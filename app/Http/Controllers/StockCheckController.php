<?php

namespace App\Http\Controllers;

use App\Services\ProductService;
use App\Services\StockCheckService;
use App\Services\UserService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StockCheckController extends Controller
{
    protected StockCheckService $stockCheckService;

    protected ProductService $productService;

    protected UserService $userService;

    public function __construct(
        StockCheckService $stockCheckService,
        ProductService $productService,
        UserService $userService
    ) {
        $this->stockCheckService = $stockCheckService;
        $this->productService = $productService;
        $this->userService = $userService;
    }

    public function index()
    {
        if (! $this->userService->kiemTraQuyen(Auth::user())) {
            abort(403, 'Chỉ Quản lý hoặc Admin mới có quyền thực hiện kiểm kê kho.');
        }

        $products = $this->productService->layTatCaSanPhamTon();

        return view('stock-check.index', compact('products'));
    }

    public function adjust(Request $request)
    {
        if (! $this->userService->kiemTraQuyen(Auth::user())) {
            abort(403, 'Chỉ Quản lý hoặc Admin mới có quyền thực hiện kiểm kê kho.');
        }

        $request->validate([
            'adjustments' => 'required|array',
            'adjustments.*.product_id' => 'required|exists:products,id',
            'adjustments.*.actual_quantity' => 'required|integer|min:0',
            'adjustments.*.reason' => 'nullable|string',
        ]);

        try {
            $result = $this->stockCheckService->dieuChinhTon($request->adjustments, Auth::id());

            return redirect()->route('stock-check.index')->with('success', $result['message']);
        } catch (Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }
}
