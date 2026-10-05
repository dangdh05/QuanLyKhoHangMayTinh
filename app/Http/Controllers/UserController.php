<?php

namespace App\Http\Controllers;

use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    protected UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    public function showRegister()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.register');
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'username' => 'required|string|min:4|max:50|unique:users,username',
            'full_name' => 'required|string|max:100',
            'email' => 'nullable|email|max:100|unique:users,email',
            'password' => 'required|string|min:8|regex:/[A-Za-z]/|regex:/[0-9]/',
            'password_confirmation' => 'required|same:password',
        ], [
            'username.required' => 'Vui lòng nhập tên đăng nhập.',
            'username.min' => 'Tên đăng nhập phải có ít nhất 4 ký tự.',
            'username.unique' => 'Tên đăng nhập này đã được sử dụng.',
            'full_name.required' => 'Vui lòng nhập họ và tên.',
            'email.email' => 'Email không đúng định dạng.',
            'email.unique' => 'Email này đã được sử dụng.',
            'password.required' => 'Vui lòng nhập mật khẩu.',
            'password.min' => 'Mật khẩu phải có tối thiểu 8 ký tự.',
            'password.regex' => 'Mật khẩu phải bao gồm cả chữ cái và số.',
            'password_confirmation.required' => 'Vui lòng xác nhận mật khẩu.',
            'password_confirmation.same' => 'Mật khẩu xác nhận không khớp.',
        ]);

        $user = \App\Models\User::create([
            'username' => $validated['username'],
            'full_name' => $validated['full_name'],
            'email' => $validated['email'] ?? null,
            'password' => $validated['password'],
            'role' => 'staff',
            'status' => true,
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('dashboard')->with('success', 'Đăng ký tài khoản thành công! Chào mừng ' . $user->full_name);
    }

    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $this->userService->dangNhap($request->username, $request->password);
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'))
            ->with('success', 'Đăng nhập thành công! Chào mừng '.Auth::user()->full_name);
    }

    public function logout()
    {
        $this->userService->dangXuat();

        return redirect()->route('login')->with('success', 'Bạn đã đăng xuất khỏi hệ thống.');
    }

    public function showChangePassword()
    {
        return view('auth.change-password');
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:8|regex:/[A-Za-z]/|regex:/[0-9]/',
            'new_password_confirmation' => 'required|same:new_password',
        ], [
            'new_password.regex' => 'Mật khẩu mới phải có cả chữ cái và số.',
            'new_password_confirmation.same' => 'Mật khẩu xác nhận không khớp.',
        ]);

        $this->userService->doiMatKhau(
            Auth::id(),
            $request->current_password,
            $request->new_password,
            $request->new_password_confirmation
        );

        return back()->with('success', 'Đổi mật khẩu thành công!');
    }

    // Quản lý người dùng (Chỉ duy nhất Admin)
    public function index(Request $request)
    {
        if (! $this->userService->kiemTraAdmin(Auth::user())) {
            abort(403, 'Bạn không có quyền truy cập chức năng này.');
        }

        $users = $this->userService->layDanhSach($request->all());

        return view('users.index', compact('users'));
    }

    public function store(Request $request)
    {
        if (! $this->userService->kiemTraAdmin(Auth::user())) {
            abort(403, 'Bạn không có quyền truy cập chức năng này.');
        }

        $request->validate([
            'username' => 'required|string|unique:users,username',
            'password' => 'required|string|min:6',
            'full_name' => 'required|string',
            'email' => 'nullable|email',
            'role' => 'required|in:admin,manager,staff',
        ]);

        $this->userService->luuNguoiDung($request->all());

        return redirect()->route('users.index')->with('success', 'Thêm người dùng mới thành công!');
    }

    public function update(Request $request, $id)
    {
        if (! $this->userService->kiemTraAdmin(Auth::user())) {
            abort(403, 'Bạn không có quyền truy cập chức năng này.');
        }

        $request->validate([
            'full_name' => 'required|string',
            'email' => 'nullable|email',
            'role' => 'required|in:admin,manager,staff',
            'password' => 'nullable|string|min:6',
        ]);

        $this->userService->luuNguoiDung($request->all(), $id);

        return redirect()->route('users.index')->with('success', 'Cập nhật thông tin người dùng thành công!');
    }

    public function toggleStatus($id)
    {
        if (! $this->userService->kiemTraAdmin(Auth::user())) {
            abort(403, 'Bạn không có quyền truy cập chức năng này.');
        }

        $user = $this->userService->chuyenTrangThai($id);
        $statusStr = $user->status ? 'Mở khóa' : 'Khóa';

        return back()->with('success', "Đã {$statusStr} tài khoản [{$user->username}] thành công.");
    }
}
