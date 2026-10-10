<?php

namespace App\Http\Controllers;

use App\Services\ProductService;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
{
    protected ProductService $productService;

    protected UserService $userService;

    public function __construct(ProductService $productService, UserService $userService)
    {
        $this->productService = $productService;
        $this->userService = $userService;
    }

    public function index(Request $request)
    {
        $products = $this->productService->layDanhSach($request->all());

        return view('products.index', compact('products'));
    }

    public function store(Request $request)
    {
        if (! $this->userService->kiemTraQuyen(Auth::user())) {
            abort(403, 'Bạn không có quyền thực hiện thao tác này.');
        }

        $validated = $request->validate([
            'code' => 'required|string|unique:products,code',
            'name' => 'required|string|max:255',
            'brand' => 'nullable|string|max:100',
            'cpu' => 'nullable|string|max:100',
            'ram' => 'nullable|string|max:100',
            'storage' => 'nullable|string|max:100',
            'gpu' => 'nullable|string|max:100',
            'release_date' => 'nullable|date',
            'cost_price' => 'nullable|numeric|min:0',
            'selling_price' => 'nullable|numeric|min:0',
            'min_quantity' => 'nullable|integer|min:0',
        ]);

        $this->productService->luuSanPham($validated);

        return redirect()->route('products.index')->with('success', 'Thêm máy tính / mặt hàng mới thành công!');
    }

    public function update(Request $request, $id)
    {
        if (! $this->userService->kiemTraQuyen(Auth::user())) {
            abort(403, 'Bạn không có quyền thực hiện thao tác này.');
        }

        $validated = $request->validate([
            'code' => 'required|string|unique:products,code,'.$id,
            'name' => 'required|string|max:255',
            'brand' => 'nullable|string|max:100',
            'cpu' => 'nullable|string|max:100',
            'ram' => 'nullable|string|max:100',
            'storage' => 'nullable|string|max:100',
            'gpu' => 'nullable|string|max:100',
            'release_date' => 'nullable|date',
            'cost_price' => 'nullable|numeric|min:0',
            'selling_price' => 'nullable|numeric|min:0',
            'min_quantity' => 'nullable|integer|min:0',
            'status' => 'nullable|boolean',
        ]);

        $this->productService->luuSanPham($validated, (int) $id);

        return redirect()->route('products.index')->with('success', 'Cập nhật thông tin máy tính thành công!');
    }

    public function destroy($id)
    {
        if (! $this->userService->kiemTraQuyen(Auth::user())) {
            abort(403, 'Bạn không có quyền thực hiện thao tác này.');
        }

        $wasDeleted = $this->productService->xoaSanPham((int) $id);

        if ($wasDeleted) {
            return redirect()->route('products.index')->with('success', 'Đã xóa vĩnh viễn mặt hàng khỏi hệ thống.');
        }

        return redirect()->route('products.index')->with('warning', 'Mặt hàng đã phát sinh giao dịch xuất/nhập nên đã được chuyển sang trạng thái "Ngừng hoạt động".');
    }

    /**
     * Màn hình xem tồn kho và cảnh báo định mức + cảnh báo tồn ứ đọng lâu năm (StockView)
     */
    public function stock(Request $request)
    {
        $products = $this->productService->layDanhSachTon($request->all(), 15);
        $lowStockProducts = $this->productService->layCacSanPhamTonThap();
        $agingProducts = $this->productService->layCacSanPhamTonUdong(12);
        $warningAgingProducts = $this->productService->layCacSanPhamTonUdong(6);

        return view('products.stock', compact('products', 'lowStockProducts', 'agingProducts', 'warningAgingProducts'));
    }
}
