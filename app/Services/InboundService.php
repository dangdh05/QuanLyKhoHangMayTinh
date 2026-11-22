<?php

namespace App\Services;

use App\Models\InboundDetail;
use App\Models\InboundNote;
use App\Models\Product;
use App\Models\Supplier;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InboundService
{
    protected ProductService $productService;

    protected StockCardService $stockCardService;

    public function __construct(ProductService $productService, StockCardService $stockCardService)
    {
        $this->productService = $productService;
        $this->stockCardService = $stockCardService;
    }

    public function layDanhSach(array $filters = [])
    {
        $query = InboundNote::with(['supplier', 'user', 'details.product'])
            ->orderBy('id', 'desc');

        if (! empty($filters['keyword'])) {
            $kw = $filters['keyword'];
            $query->where(function ($q) use ($kw) {
                $q->where('code', 'like', "%{$kw}%")
                    ->orWhereHas('supplier', function ($sq) use ($kw) {
                        $sq->where('name', 'like', "%{$kw}%");
                    });
            });
        }

        if (! empty($filters['supplier_id'])) {
            $query->where('supplier_id', $filters['supplier_id']);
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['from_date'])) {
            $query->whereDate('created_date', '>=', $filters['from_date']);
        }

        if (! empty($filters['to_date'])) {
            $query->whereDate('created_date', '<=', $filters['to_date']);
        }

        return $query->paginate(15);
    }

    /**
     * Tính giá vốn bình quân gia quyền theo QT6
     */
    public function tinhGiaVonBinhQuan(float $oldQty, float $oldCost, float $inQty, float $inPrice): float
    {
        $totalQty = $oldQty + $inQty;
        if ($totalQty <= 0) {
            return $inPrice;
        }

        // Giá vốn mới = (Số lượng tồn cũ × Giá vốn cũ + Số lượng nhập × Đơn giá nhập) / (Số lượng tồn cũ + Số lượng nhập)
        $newCost = (($oldQty * $oldCost) + ($inQty * $inPrice)) / $totalQty;

        return round($newCost, 2);
    }

    /**
     * Tính tổng tiền chi tiết phiếu nhập
     */
    public function tinhTongTien(array $items): float
    {
        $total = 0;
        foreach ($items as $item) {
            $total += (int) $item['quantity'] * (float) $item['import_price'];
        }

        return round($total, 2);
    }

    /**
     * Tạo phiếu nhập kho theo QT1, QT3, QT4, QT6
     */
    public function taoPhieuNhap(int $supplierId, int $userId, array $items, ?string $note = null, ?string $createdDate = null): InboundNote
    {
        // 1. Kiểm tra QT1: NCC hợp lệ, số lượng > 0, đơn giá > 0
        $supplier = Supplier::findOrFail($supplierId);
        if (! $supplier->status) {
            throw ValidationException::withMessages([
                'supplier_id' => 'Nhà cung cấp đã ngừng hoạt động.',
            ]);
        }

        if (empty($items)) {
            throw ValidationException::withMessages([
                'items' => 'Phiếu nhập phải có ít nhất một mặt hàng.',
            ]);
        }

        foreach ($items as $idx => $item) {
            if (empty($item['product_id'])) {
                throw ValidationException::withMessages(["items.{$idx}.product_id" => 'Vui lòng chọn mặt hàng.']);
            }
            if (! isset($item['quantity']) || (int) $item['quantity'] <= 0) {
                throw ValidationException::withMessages(["items.{$idx}.quantity" => 'Số lượng nhập phải lớn hơn 0.']);
            }
            if (! isset($item['import_price']) || (float) $item['import_price'] <= 0) {
                throw ValidationException::withMessages(["items.{$idx}.import_price" => 'Đơn giá nhập phải lớn hơn 0.']);
            }
        }

        // 2. Bắt đầu Database Transaction (QT3)
        return DB::transaction(function () use ($supplierId, $userId, $items, $note, $createdDate) {
            $code = 'PN'.date('Ymd').'-'.strtoupper(substr(uniqid(), -4));
            $totalAmount = $this->tinhTongTien($items);

            $inboundNote = InboundNote::create([
                'code' => $code,
                'supplier_id' => $supplierId,
                'user_id' => $userId,
                'created_date' => $createdDate ? date('Y-m-d H:i:s', strtotime($createdDate)) : now(),
                'total_amount' => $totalAmount,
                'note' => $note,
                'status' => 'completed',
            ]);

            foreach ($items as $item) {
                $qty = (int) $item['quantity'];
                $importPrice = (float) $item['import_price'];
                $productId = (int) $item['product_id'];

                // Lưu chi tiết phiếu
                InboundDetail::create([
                    'inbound_note_id' => $inboundNote->id,
                    'product_id' => $productId,
                    'quantity' => $qty,
                    'import_price' => $importPrice,
                ]);

                // Khóa bản ghi product để tính toán chính xác
                $product = Product::lockForUpdate()->findOrFail($productId);
                $oldQty = $product->quantity;
                $oldCost = (float) $product->cost_price;

                // Tính giá vốn bình quân mới (QT6)
                $newCostPrice = $this->tinhGiaVonBinhQuan($oldQty, $oldCost, $qty, $importPrice);
                $newBalance = $oldQty + $qty;

                // Cập nhật tồn và giá vốn Product
                $product->quantity = $newBalance;
                $product->cost_price = $newCostPrice;
                $product->save();

                // Ghi thẻ kho (QT4)
                $this->stockCardService->ghiTheKho(
                    productId: $productId,
                    transactionType: 'inbound',
                    refCode: $inboundNote->code,
                    changeQuantity: $qty,
                    balanceQuantity: $newBalance,
                    note: 'Nhập kho từ phiếu '.$inboundNote->code
                );
            }

            return $inboundNote;
        });
    }

    /**
     * Hủy phiếu nhập kho (QT5: Hoàn tác tồn, kiểm tra không để âm kho)
     */
    public function huyPhieuNhap(int $id, int $userId): InboundNote
    {
        return DB::transaction(function () use ($id) {
            $note = InboundNote::with('details')->lockForUpdate()->findOrFail($id);

            if ($note->status === 'cancelled') {
                throw new Exception('Phiếu nhập này đã được hủy trước đó.');
            }

            // Kiểm tra trước: việc trừ lại tồn có làm bất kỳ mặt hàng nào bị âm không (QT5)
            foreach ($note->details as $detail) {
                $product = Product::lockForUpdate()->findOrFail($detail->product_id);
                if ($product->quantity < $detail->quantity) {
                    throw new Exception("Không thể hủy phiếu! Mặt hàng [{$product->code}] {$product->name} hiện chỉ còn tồn {$product->quantity}, không đủ {$detail->quantity} để hoàn tác.");
                }
            }

            // Tiến hành hoàn tác tồn kho
            foreach ($note->details as $detail) {
                $product = Product::lockForUpdate()->findOrFail($detail->product_id);
                $newBalance = $product->quantity - $detail->quantity;
                $product->quantity = $newBalance;
                $product->save();

                // Ghi thẻ kho hoàn tác
                $this->stockCardService->ghiTheKho(
                    productId: $product->id,
                    transactionType: 'cancel_inbound',
                    refCode: 'HUY-'.$note->code,
                    changeQuantity: -$detail->quantity,
                    balanceQuantity: $newBalance,
                    note: 'Hoàn tác hủy phiếu nhập '.$note->code
                );
            }

            $note->status = 'cancelled';
            $note->save();

            return $note;
        });
    }
}
