<?php

namespace App\Http\Controllers;

use App\Models\InboundNote;
use App\Models\OutboundNote;
use App\Models\Product;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();

        // 1. Thống kê sản phẩm
        $totalProducts = Product::where('status', true)->count();
        $totalQuantity = Product::where('status', true)->sum('quantity');

        $totalInventoryValue = Product::where('status', true)->get()->sum(function ($p) {
            return $p->quantity * (float) $p->cost_price;
        });

        // 2. Cảnh báo hàng tồn thấp (QT7)
        $lowStockCount = Product::where('status', true)->whereRaw('quantity <= min_quantity')->count();
        $lowStockProducts = Product::where('status', true)
            ->whereRaw('quantity <= min_quantity')
            ->orderBy('quantity', 'asc')
            ->take(5)
            ->get();

        // 2b. Cảnh báo hàng tồn ứ đọng / quá hạn mở bán (mở bán > 12 tháng)
        $agingStockCount = Product::where('status', true)
            ->whereNotNull('release_date')
            ->where('release_date', '<=', now()->subMonths(12))
            ->where('quantity', '>', 0)
            ->count();
        $agingStockProducts = Product::where('status', true)
            ->whereNotNull('release_date')
            ->where('release_date', '<=', now()->subMonths(12))
            ->where('quantity', '>', 0)
            ->orderBy('release_date', 'asc')
            ->take(5)
            ->get();
        $totalAgingCapital = Product::where('status', true)
            ->whereNotNull('release_date')
            ->where('release_date', '<=', now()->subMonths(12))
            ->where('quantity', '>', 0)
            ->get()
            ->sum(fn ($p) => $p->quantity * (float) $p->cost_price);

        // 3. Phiếu nhập trong tháng
        $monthlyInboundsCount = InboundNote::where('status', 'completed')
            ->whereBetween('created_date', [$startOfMonth, $endOfMonth])
            ->count();
        $monthlyInboundsAmount = InboundNote::where('status', 'completed')
            ->whereBetween('created_date', [$startOfMonth, $endOfMonth])
            ->sum('total_amount');

        // 4. Phiếu xuất trong tháng
        $monthlyOutboundsCount = OutboundNote::where('status', 'completed')
            ->whereBetween('created_date', [$startOfMonth, $endOfMonth])
            ->count();
        $monthlyOutboundsAmount = OutboundNote::where('status', 'completed')
            ->whereBetween('created_date', [$startOfMonth, $endOfMonth])
            ->sum('total_amount');

        // 5. Hoạt động gần đây
        $recentInbounds = InboundNote::with('supplier')
            ->orderBy('created_date', 'desc')
            ->take(5)
            ->get();

        $recentOutbounds = OutboundNote::with('customer')
            ->orderBy('created_date', 'desc')
            ->take(5)
            ->get();

        return view('dashboard', compact(
            'totalProducts',
            'totalQuantity',
            'totalInventoryValue',
            'lowStockCount',
            'lowStockProducts',
            'agingStockCount',
            'agingStockProducts',
            'totalAgingCapital',
            'monthlyInboundsCount',
            'monthlyInboundsAmount',
            'monthlyOutboundsCount',
            'monthlyOutboundsAmount',
            'recentInbounds',
            'recentOutbounds'
        ));
    }
}
