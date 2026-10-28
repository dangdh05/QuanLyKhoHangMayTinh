<?php

namespace App\Http\Controllers;

use App\Services\CustomerService;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CustomerController extends Controller
{
    protected CustomerService $customerService;

    protected UserService $userService;

    public function __construct(CustomerService $customerService, UserService $userService)
    {
        $this->customerService = $customerService;
        $this->userService = $userService;
    }

    public function index(Request $request)
    {
        $customers = $this->customerService->layDanhSach($request->all());

        return view('customers.index', compact('customers'));
    }

    public function store(Request $request)
    {
        if (! $this->userService->kiemTraQuyen(Auth::user())) {
            abort(403, 'Bạn không có quyền thực hiện thao tác này.');
        }

        $validated = $request->validate([
            'code' => 'required|string|unique:customers,code',
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
        ]);

        $this->customerService->luuKhachHang($validated);

        return redirect()->route('customers.index')->with('success', 'Thêm khách hàng mới thành công!');
    }

    public function update(Request $request, $id)
    {
        if (! $this->userService->kiemTraQuyen(Auth::user())) {
            abort(403, 'Bạn không có quyền thực hiện thao tác này.');
        }

        $validated = $request->validate([
            'code' => 'required|string|unique:customers,code,'.$id,
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'status' => 'nullable|boolean',
        ]);

        $this->customerService->luuKhachHang($validated, (int) $id);

        return redirect()->route('customers.index')->with('success', 'Cập nhật thông tin khách hàng thành công!');
    }

    public function destroy($id)
    {
        if (! $this->userService->kiemTraQuyen(Auth::user())) {
            abort(403, 'Bạn không có quyền thực hiện thao tác này.');
        }

        $wasDeleted = $this->customerService->xoaKhachHang((int) $id);

        if ($wasDeleted) {
            return redirect()->route('customers.index')->with('success', 'Đã xóa vĩnh viễn khách hàng khỏi hệ thống.');
        }

        return redirect()->route('customers.index')->with('warning', 'Khách hàng đã có phiếu xuất kho nên đã chuyển sang trạng thái "Ngừng hoạt động".');
    }
}
