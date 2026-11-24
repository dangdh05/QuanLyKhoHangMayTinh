<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\OutboundDetail;
use App\Models\OutboundNote;
use App\Models\Product;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OutboundService
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
        $query = OutboundNote::with(['customer', 'user', 'details.product'])
            ->orderBy('id', 'desc');

        if (! empty($filters['keyword'])) {
            $kw = $filters['keyword'];
            $query->where(function ($q) use ($kw) {
                $q->where('code', 'like', "%{$kw}%")
                    ->orWhereHas('customer', function ($cq) use ($kw) {
                        $cq->where('name', 'like', "%{$kw}%");
                    });
            });
        }

        if (! empty($filters['customer_id'])) {
            $query->where('customer_id', $filters['customer_id']);
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

    public function kiemTraDuHangTon(int $productId, int $exportQty): bool
    {
        $product = Product::find($productId);
        if (! $product) {
            return false;
        }

        return $product->quantity >= $exportQty;
    }

    public function tinhTongTien(array $items): float
    {
        $total = 0;
        foreach ($items as $item) {
            $total += (int) $item['quantity'] * (float) $item['export_price'];
        }

        return round($total, 2);
    }

    /**
     * Tạo phiếu xuất kho theo QT2, QT3, QT4, QT6
     */
    public function taoPhieuXuat(int $customerId, int $userId, array $items, ?string $note = null, ?string $createdDate = null): array
    {
        // 1. Kiểm tra QT2: Khách hàng hợp lệ, số lượng > 0
        $customer = Customer::findOrFail($customerId);
        if (! $customer->status) {
            throw ValidationException::withMessages([
                'customer_id' => 'Khách hàng này hiện đã bị ngưng hoạt động.',
            ]);
        }

        if (empty($items)) {
            throw ValidationException::withMessages([
                'items' => 'Phiếu xuất phải có ít nhất một mặt hàng.',
            ]);
        }

        foreach ($items as $idx => $item) {
            if (empty($item['product_id'])) {
                throw ValidationException::withMessages(["items.{$idx}.product_id" => 'Vui lòng chọn mặt hàng.']);
            }
            if (! isset($item['quantity']) || (int) $item['quantity'] <= 0) {
                throw ValidationException::withMessages(["items.{$idx}.quantity" => 'Số lượng xuất phải lớn hơn 0.']);
            }
            if (! isset($item['export_price']) || (float) $item['export_price'] < 0) {
                throw ValidationException::withMessages(["items.{$idx}.export_price" => 'Đơn giá xuất không hợp lệ.']);
            }
        }

        // 2. Bắt đầu Database Transaction (QT3)
        $lowStockWarnings = [];

        $outboundNote = DB::transaction(function () use ($customerId, $userId, $items, $note, $createdDate, &$lowStockWarnings) {
            $code = 'PX'.date('Ymd').'-'.strtoupper(substr(uniqid(), -4));
            $totalAmount = $this->tinhTongTien($items);

            $outboundNote = OutboundNote::create([
                'code' => $code,
                'customer_id' => $customerId,
                'user_id' => $userId,
                'created_date' => $createdDate ? date('Y-m-d H:i:s', strtotime($createdDate)) : now(),
                'total_amount' => $totalAmount,
                'note' => $note,
                'status' => 'completed',
            ]);

            foreach ($items as $item) {
                $qty = (int) $item['quantity'];
                $exportPrice = (float) $item['export_price'];
                $productId = (int) $item['product_id'];

                // Khóa kiểm tra tồn kho (QT2)
                $product = Product::lockForUpdate()->findOrFail($productId);
                if ($product->quantity < $qty) {
                    throw new Exception("Mặt hàng [{$product->code}] {$product->name} tồn hiện tại ({$product->quantity}) không đủ để xuất {$qty}.");
                }

                // Lưu chi tiết
                OutboundDetail::create([
                    'outbound_note_id' => $outboundNote->id,
                    'product_id' => $productId,
                    'quantity' => $qty,
                    'export_price' => $exportPrice,
                ]);

                // Trừ tồn kho (giá vốn không đổi theo QT6)
                $newBalance = $product->quantity - $qty;
                $product->quantity = $newBalance;
                $product->save();

                // Ghi thẻ kho (QT4)
                $this->stockCardService->ghiTheKho(
                    productId: $productId,
                    transactionType: 'outbound',
                    refCode: $outboundNote->code,
                    changeQuantity: -$qty,
                    balanceQuantity: $newBalance,
                    note: 'Xuất kho theo phiếu '.$outboundNote->code
                );

                // Kiểm tra cảnh báo tồn thấp (QT7)
                if ($newBalance <= $product->min_quantity) {
                    $lowStockWarnings[] = [
                        'code' => $product->code,
                        'name' => $product->name,
                        'balance' => $newBalance,
                        'min' => $product->min_quantity,
                    ];
                }
            }

            return $outboundNote;
        });

        return [
            'note' => $outboundNote,
            'low_stock_warnings' => $lowStockWarnings,
        ];
    }

    /**
     * Hủy phiếu xuất kho (QT5: Hoàn tác cộng lại tồn kho)
     */
    public function huyPhieuXuat(int $id, int $userId): OutboundNote
    {
        return DB::transaction(function () use ($id) {
            $note = OutboundNote::with('details')->lockForUpdate()->findOrFail($id);

            if ($note->status === 'cancelled') {
                throw new Exception('Phiếu xuất này đã được hủy trước đó.');
            }

            // Hoàn tác tồn kho: cộng trả lại
            foreach ($note->details as $detail) {
                $product = Product::lockForUpdate()->findOrFail($detail->product_id);
                $newBalance = $product->quantity + $detail->quantity;
                $product->quantity = $newBalance;
                $product->save();

                // Ghi thẻ kho hoàn tác
                $this->stockCardService->ghiTheKho(
                    productId: $product->id,
                    transactionType: 'cancel_outbound',
                    refCode: 'HUY-'.$note->code,
                    changeQuantity: $detail->quantity,
                    balanceQuantity: $newBalance,
                    note: 'Hoàn tác hủy phiếu xuất '.$note->code
                );
            }

            $note->status = 'cancelled';
            $note->save();

            return $note;
        });
    }
}
