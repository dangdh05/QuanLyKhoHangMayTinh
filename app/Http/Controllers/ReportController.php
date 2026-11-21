<?php

namespace App\Http\Controllers;

use App\Services\ReportService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    protected ReportService $reportService;

    public function __construct(ReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    public function inOutStock(Request $request)
    {
        $fromDate = $request->get('from_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $toDate = $request->get('to_date', Carbon::now()->endOfMonth()->format('Y-m-d'));
        $keyword = $request->get('keyword');

        $reportData = $this->reportService->baoCaoNhapXuatTon($fromDate, $toDate, $keyword);

        return view('reports.in-out-stock', compact('reportData', 'fromDate', 'toDate', 'keyword'));
    }

    public function lowStock()
    {
        $lowStockProducts = $this->reportService->baoCaoHangTonThap();

        return view('reports.low-stock', compact('lowStockProducts'));
    }

    public function inventoryValue(Request $request)
    {
        $keyword = $request->get('keyword');
        $data = $this->reportService->baoCaoGiaTriTon($keyword);

        return view('reports.inventory-value', array_merge($data, ['keyword' => $keyword]));
    }

    public function slowMoving(Request $request)
    {
        $mode = $request->get('mode', 'all'); // 'all', 'aging_release', 'no_outbound'
        $days = (int) $request->get('days', 30);
        $agingMonths = $request->has('aging_months') ? (int) $request->get('aging_months') : 12;

        $products = $this->reportService->baoCaoHangTonLauTongHop($mode, $days, $agingMonths);

        return view('reports.slow-moving', compact('products', 'mode', 'days', 'agingMonths'));
    }
}

