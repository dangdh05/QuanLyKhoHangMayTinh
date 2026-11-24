<?php

namespace App\Services;

use App\Models\Product;
use App\Models\StockCard;
use Carbon\Carbon;

class ReportService
{
    /**
     * Báo cáo Nhập - Xuất - Tồn theo kỳ
     * Tồn cuối = Tồn đầu + Nhập - Xuất ± Điều chỉnh
     */
    public function baoCaoNhapXuatTon(string $fromDate, string $toDate, ?string $keyword = null)
    {
        $query = Product::orderBy('code', 'asc');
        if ($keyword) {
            $query->where(function ($q) use ($keyword) {
                $q->where('code', 'like', "%{$keyword}%")
                    ->orWhere('name', 'like', "%{$keyword}%")
                    ->orWhere('brand', 'like', "%{$keyword}%");
            });
        }
        $products = $query->get();
        $reportData = [];

        $start = Carbon::parse($fromDate)->startOfDay();
        $end = Carbon::parse($toDate)->endOfDay();

        foreach ($products as $product) {
            // Biến động trong kỳ từ stock_cards
            $inPeriod = StockCard::where('product_id', $product->id)
                ->whereBetween('created_at', [$start, $end])
                ->get();

            $nhapTrongKy = $inPeriod->where('transaction_type', 'inbound')->sum('change_quantity');
            $xuatTrongKy = abs($inPeriod->where('transaction_type', 'outbound')->sum('change_quantity'));
            $dieuChinhTrongKy = $inPeriod->where('transaction_type', 'adjust')->sum('change_quantity');

            // Biến động sau kỳ kết thúc đến hiện tại
            $changeAfterEnd = StockCard::where('product_id', $product->id)
                ->where('created_at', '>', $end)
                ->sum('change_quantity');

            // Tồn cuối kỳ = Tồn hiện tại - Biến động sau kỳ
            $tonCuoiKy = $product->quantity - $changeAfterEnd;

            // Biến động trong kỳ tổng hợp
            $changeInPeriod = $nhapTrongKy - $xuatTrongKy + $dieuChinhTrongKy;

            // Tồn đầu kỳ = Tồn cuối kỳ - Biến động trong kỳ
            $tonDauKy = $tonCuoiKy - $changeInPeriod;

            $reportData[] = [
                'product' => $product,
                'ton_dau' => $tonDauKy,
                'nhap' => $nhapTrongKy,
                'xuat' => $xuatTrongKy,
                'dieu_chinh' => $dieuChinhTrongKy,
                'ton_cuoi' => $tonCuoiKy,
                'gia_von' => $product->cost_price,
                'gia_tri_ton_cuoi' => $tonCuoiKy * (float) $product->cost_price,
            ];
        }

        return $reportData;
    }

    /**
     * Báo cáo hàng dưới mức tồn tối thiểu
     */
    public function baoCaoHangTonThap()
    {
        return Product::where('status', true)
            ->whereRaw('quantity <= min_quantity')
            ->orderByRaw('quantity - min_quantity ASC')
            ->get();
    }

    /**
     * Báo cáo giá trị tồn kho hiện tại
     */
    public function baoCaoGiaTriTon(?string $keyword = null)
    {
        $query = Product::where('status', true)->orderBy('name', 'asc');

        if ($keyword) {
            $query->where(function ($q) use ($keyword) {
                $q->where('code', 'like', "%{$keyword}%")
                    ->orWhere('name', 'like', "%{$keyword}%")
                    ->orWhere('brand', 'like', "%{$keyword}%");
            });
        }

        $products = $query->get();

        $totalQuantity = $products->sum('quantity');
        $totalCostValue = $products->sum(function ($p) {
            return $p->quantity * (float) $p->cost_price;
        });
        $totalSellingValue = $products->sum(function ($p) {
            return $p->quantity * (float) $p->selling_price;
        });

        return [
            'products' => $products,
            'total_quantity' => $totalQuantity,
            'total_cost_value' => $totalCostValue,
            'total_selling_value' => $totalSellingValue,
        ];
    }

    /**
     * Báo cáo hàng tồn lâu không xuất & hàng tồn ứ đọng theo thời gian mở bán
     */
    public function baoCaoHangTonLauTongHop(string $mode = 'all', int $days = 30, int $agingMonths = 12)
    {
        $thresholdDate = Carbon::now()->subDays($days);
        $agingDate = Carbon::now()->subMonths($agingMonths);

        $query = Product::where('status', true)->where('quantity', '>', 0);

        if ($mode === 'aging_release') {
            // Lọc các mặt hàng có ngày mở bán/ra mắt quá số tháng quy định (ví dụ máy tính mở bán > 12 tháng)
            $query->whereNotNull('release_date')
                ->where('release_date', '<=', $agingDate);
        } elseif ($mode === 'no_outbound') {
            // Lọc các mặt hàng không phát sinh xuất bán trong X ngày qua
            $query->whereDoesntHave('outboundDetails', function ($q) use ($thresholdDate) {
                $q->whereHas('outboundNote', function ($nq) use ($thresholdDate) {
                    $nq->where('created_date', '>=', $thresholdDate)
                        ->where('status', 'completed');
                });
            });
        } else {
            // Mode 'all': Mặt hàng không phát sinh xuất bán HOẶC mở bán đã lâu mà vẫn còn tồn
            $query->where(function ($q) use ($thresholdDate, $agingDate) {
                $q->whereDoesntHave('outboundDetails', function ($sub) use ($thresholdDate) {
                    $sub->whereHas('outboundNote', function ($nq) use ($thresholdDate) {
                        $nq->where('created_date', '>=', $thresholdDate)
                            ->where('status', 'completed');
                    });
                })
                    ->orWhere(function ($sub) use ($agingDate) {
                        $sub->whereNotNull('release_date')
                            ->where('release_date', '<=', $agingDate);
                    });
            });
        }

        return $query->with(['stockCards' => function ($q) {
            $q->orderBy('created_at', 'desc')->limit(1);
        }])
            ->orderByRaw('CASE WHEN release_date IS NOT NULL THEN release_date ELSE "9999-12-31" END ASC')
            ->get();
    }

    /**
     * Báo cáo hàng tồn lâu không xuất
     */
    public function baoCaoHangTonLau(int $days = 30)
    {
        return $this->baoCaoHangTonLauTongHop('no_outbound', $days, 12);
    }
}
