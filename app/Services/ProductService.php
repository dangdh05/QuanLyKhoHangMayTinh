<?php

namespace App\Services;

use App\Models\Product;
use App\Models\StockCard;
use Illuminate\Validation\ValidationException;

class ProductService
{
    public function layDanhSach(array $filters = [])
    {
        $query = Product::query()->orderBy('id', 'desc');

        if (! empty($filters['keyword'])) {
            $kw = $filters['keyword'];
            $query->where(function ($q) use ($kw) {
                $q->where('code', 'like', "%{$kw}%")
                    ->orWhere('name', 'like', "%{$kw}%")
                    ->orWhere('brand', 'like', "%{$kw}%")
                    ->orWhere('cpu', 'like', "%{$kw}%")
                    ->orWhere('ram', 'like', "%{$kw}%");
            });
        }

        if (! empty($filters['brand'])) {
            $query->where('brand', $filters['brand']);
        }

        if (isset($filters['low_stock']) && $filters['low_stock']) {
            $query->whereRaw('quantity <= min_quantity');
        }

        if (! empty($filters['aging'])) {
            if ($filters['aging'] === '12') {
                $query->whereNotNull('release_date')
                    ->where('release_date', '<=', now()->subMonths(12))
                    ->where('quantity', '>', 0);
            } elseif ($filters['aging'] === '6') {
                $query->whereNotNull('release_date')
                    ->where('release_date', '<=', now()->subMonths(6))
                    ->where('quantity', '>', 0);
            }
        }

        if (isset($filters['status']) && $filters['status'] !== '') {
            $query->where('status', (bool) $filters['status']);
        }

        return $query->paginate(15);
    }

    public function layTatCaSanPhamTon()
    {
        return Product::where('status', true)->orderBy('name', 'asc')->get();
    }

    public function layDanhSachTon(array $filters = [], int $perPage = 15)
    {
        $query = Product::where('status', true)->orderBy('name', 'asc');

        if (! empty($filters['keyword'])) {
            $kw = $filters['keyword'];
            $query->where(function ($q) use ($kw) {
                $q->where('code', 'like', "%{$kw}%")
                    ->orWhere('name', 'like', "%{$kw}%")
                    ->orWhere('brand', 'like', "%{$kw}%");
            });
        }

        if (! empty($filters['filter_type'])) {
            if ($filters['filter_type'] === 'low_stock') {
                $query->whereRaw('quantity <= min_quantity');
            } elseif ($filters['filter_type'] === 'aging') {
                $query->whereNotNull('release_date')
                    ->where('release_date', '<=', now()->subMonths(12))
                    ->where('quantity', '>', 0);
            } elseif ($filters['filter_type'] === 'warning_aging') {
                $query->whereNotNull('release_date')
                    ->where('release_date', '<=', now()->subMonths(6))
                    ->where('quantity', '>', 0);
            }
        }

        return $query->paginate($perPage);
    }

    public function kiemTraTonThap(Product $product): bool
    {
        return $product->quantity <= $product->min_quantity;
    }

    public function layCacSanPhamTonThap()
    {
        return Product::where('status', true)
            ->whereRaw('quantity <= min_quantity')
            ->get();
    }

    /**
     * Lấy các sản phẩm tồn kho ứ đọng theo số tháng mở bán (Mặc định >= 12 tháng)
     */
    public function layCacSanPhamTonUdong(int $months = 12)
    {
        return Product::where('status', true)
            ->whereNotNull('release_date')
            ->where('release_date', '<=', now()->subMonths($months))
            ->where('quantity', '>', 0)
            ->orderBy('release_date', 'asc')
            ->get();
    }

    public function luuSanPham(array $data, ?int $id = null): Product
    {
        // Kiểm tra trùng mã máy (code)
        $codeCheck = Product::where('code', $data['code']);
        if ($id) {
            $codeCheck->where('id', '!=', $id);
        }
        if ($codeCheck->exists()) {
            throw ValidationException::withMessages([
                'code' => 'Mã máy tính đã tồn tại trên hệ thống.',
            ]);
        }

        if ($id) {
            $product = Product::findOrFail($id);
            $product->update($data);

            return $product;
        }

        return Product::create($data);
    }

    public function xoaSanPham(int $id): bool
    {
        $product = Product::findOrFail($id);

        // Kiểm tra đã có phát sinh giao dịch trong thẻ kho chưa
        $hasTransactions = StockCard::where('product_id', $id)->exists();

        if ($hasTransactions) {
            // Đã phát sinh giao dịch -> Ngừng hoạt động (status = false)
            $product->status = false;
            $product->save();

            return false; // đã chuyển trạng thái ngừng hoạt động
        }

        // Chưa có giao dịch nào -> Xóa vĩnh viễn
        $product->delete();

        return true; // đã xóa hẳn
    }

    public function capNhatSoLuongTon(int $productId, int $qtyChange, ?float $newCostPrice = null): Product
    {
        $product = Product::findOrFail($productId);
        $product->quantity += $qtyChange;

        if ($newCostPrice !== null) {
            $product->cost_price = $newCostPrice;
        }

        $product->save();

        return $product;
    }
}
