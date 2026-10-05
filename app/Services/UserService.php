<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class UserService
{
    public function kiemTraAdmin(?User $user): bool
    {
        return $user !== null && $user->isAdmin();
    }

    public function kiemTraQuyen(?User $user, array $roles = ['admin', 'manager']): bool
    {
        return $user !== null && in_array($user->role, $roles);
    }

    public function layDanhSach(array $filters = [])
    {
        $query = User::query()->orderBy('id', 'desc');

        if (! empty($filters['keyword'])) {
            $kw = $filters['keyword'];
            $query->where(function ($q) use ($kw) {
                $q->where('username', 'like', "%{$kw}%")
                    ->orWhere('full_name', 'like', "%{$kw}%")
                    ->orWhere('email', 'like', "%{$kw}%");
            });
        }

        if (! empty($filters['role'])) {
            $query->where('role', $filters['role']);
        }

        if (isset($filters['status']) && $filters['status'] !== '') {
            $query->where('status', (bool) $filters['status']);
        }

        return $query->paginate(15);
    }

    public function dangNhap(string $username, string $password): User
    {
        $user = User::where('username', $username)->first();

        if (! $user || ! Hash::check($password, $user->password)) {
            throw ValidationException::withMessages([
                'username' => 'Tên đăng nhập hoặc mật khẩu không chính xác.',
            ]);
        }

        if (! $user->isActive()) {
            throw ValidationException::withMessages([
                'username' => 'Tài khoản của bạn đã bị khóa. Vui lòng liên hệ Admin.',
            ]);
        }

        Auth::login($user);

        return $user;
    }

    public function dangXuat(): void
    {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
    }

    public function doiMatKhau(int $userId, string $oldPassword, string $newPassword, string $confirmPassword): void
    {
        $user = User::findOrFail($userId);

        if (! Hash::check($oldPassword, $user->password)) {
            throw ValidationException::withMessages([
                'old_password' => 'Mật khẩu hiện tại không đúng.',
            ]);
        }

        if ($oldPassword === $newPassword) {
            throw ValidationException::withMessages([
                'new_password' => 'Mật khẩu mới phải khác mật khẩu hiện tại.',
            ]);
        }

        if ($newPassword !== $confirmPassword) {
            throw ValidationException::withMessages([
                'new_password_confirmation' => 'Mật khẩu xác nhận không khớp.',
            ]);
        }

        if (strlen($newPassword) < 8 || ! preg_match('/[A-Za-z]/', $newPassword) || ! preg_match('/[0-9]/', $newPassword)) {
            throw ValidationException::withMessages([
                'new_password' => 'Mật khẩu mới phải có tối thiểu 8 ký tự, bao gồm cả chữ và số.',
            ]);
        }

        $user->update([
            'password' => $newPassword,
        ]);
    }

    public function luuNguoiDung(array $data, ?int $id = null): User
    {
        if ($id) {
            $user = User::findOrFail($id);
            $updateData = [
                'full_name' => $data['full_name'],
                'email' => $data['email'] ?? null,
                'role' => $data['role'],
                'status' => isset($data['status']) ? (bool) $data['status'] : $user->status,
            ];
            if (! empty($data['password'])) {
                $updateData['password'] = $data['password'];
            }
            $user->update($updateData);

            return $user;
        }

        return User::create([
            'username' => $data['username'],
            'password' => $data['password'],
            'full_name' => $data['full_name'],
            'email' => $data['email'] ?? null,
            'role' => $data['role'],
            'status' => isset($data['status']) ? (bool) $data['status'] : true,
        ]);
    }

    public function chuyenTrangThai(int $id): User
    {
        $user = User::findOrFail($id);
        $user->status = ! $user->status;
        $user->save();

        return $user;
    }
}
