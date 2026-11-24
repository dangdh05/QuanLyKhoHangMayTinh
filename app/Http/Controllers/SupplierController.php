<?php

namespace App\Http\Controllers;

use App\Services\SupplierService;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SupplierController extends Controller
{
    protected SupplierService $supplierService;

    protected UserService $userService;

    public function __construct(SupplierService $supplierService, UserService $userService)
    {
        $this->supplierService = $supplierService;
        $this->userService = $userService;
    }

    public function index(Request $request)
    {
        $suppliers = $this->supplierService->layDanhSach($request->all());

        return view('suppliers.index', compact('suppliers'));
    }

    public function store(Request $request)
    {
        if (! $this->userService->kiemTraQuyen(Auth::user())) {
            abort(403, 'Bạn không có quyền thực hiện thao tác này.');
        }

        $validated = $request->validate([
            'code' => 'required|string|unique:suppliers,code',
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:100',
            'address' => 'nullable|string|max:255',
        ]);

        $this->supplierService->luuNhaCungCap($validated);

        return redirect()->route('suppliers.index')->with('success', 'Thêm nhà cung cấp mới thành công!');
    }

    public function update(Request $request, $id)
    {
        if (! $this->userService->kiemTraQuyen(Auth::user())) {
            abort(403, 'Bạn không có quyền thực hiện thao tác này.');
        }

        $validated = $request->validate([
            'code' => 'required|string|unique:suppliers,code,'.$id,
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:100',
            'address' => 'nullable|string|max:255',
            'status' => 'nullable|boolean',
        ]);

        $this->supplierService->luuNhaCungCap($validated, (int) $id);

        return redirect()->route('suppliers.index')->with('success', 'Cập nhật thông tin nhà cung cấp thành công!');
    }

    public function destroy($id)
    {
        if (! $this->userService->kiemTraQuyen(Auth::user())) {
            abort(403, 'Bạn không có quyền thực hiện thao tác này.');
        }

        $wasDeleted = $this->supplierService->xoaNhaCungCap((int) $id);

        if ($wasDeleted) {
            return redirect()->route('suppliers.index')->with('success', 'Đã xóa vĩnh viễn nhà cung cấp khỏi hệ thống.');
        }

        return redirect()->route('suppliers.index')->with('warning', 'Nhà cung cấp đã có phiếu nhập kho nên đã chuyển sang trạng thái "Ngừng hoạt động".');
    }
}
