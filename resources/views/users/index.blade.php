@extends('layouts.app')

@section('title', 'Quản Lý Tài Khoản')
@section('page_title', 'Tài Khoản Người Dùng')

@section('content')
<div class="card mb-3">
    <div class="card-body p-2">
        <form method="GET" action="{{ route('users.index') }}" class="row g-2 align-items-center">
            <div class="col-md-5">
                <input type="text" name="keyword" class="form-control" placeholder="Tìm theo username, họ tên, email..." value="{{ request('keyword') }}">
            </div>
            <div class="col-md-3">
                <select name="role" class="form-select">
                    <option value="">-- Tất cả vai trò --</option>
                    <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>Quản trị viên (Admin)</option>
                    <option value="manager" {{ request('role') === 'manager' ? 'selected' : '' }}>Quản lý kho</option>
                    <option value="staff" {{ request('role') === 'staff' ? 'selected' : '' }}>Nhân viên kho</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-outline-primary w-100"><i class="bi bi-search me-1"></i>Lọc</button>
            </div>
            <div class="col-md-2 text-end">
                <button type="button" class="btn btn-primary w-100" data-bs-toggle="modal" data-bs-target="#modalCreateUser">
                    <i class="bi bi-person-plus me-1"></i>Tạo tài khoản
                </button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>Tên đăng nhập</th>
                    <th>Họ và tên</th>
                    <th>Email</th>
                    <th class="text-center">Vai trò</th>
                    <th class="text-center">Trạng thái</th>
                    <th>Ngày tạo</th>
                    <th class="text-end">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                <tr>
                    <td>
                        <span class="font-monospace fw-medium text-dark">{{ $user->username }}</span>
                        @if($user->id === Auth::id())
                            <span class="badge bg-light text-primary border ms-1">Bạn</span>
                        @endif
                    </td>
                    <td>{{ $user->full_name }}</td>
                    <td class="text-muted small">{{ $user->email ?? '---' }}</td>
                    <td class="text-center">
                        @if($user->role === 'admin')
                            <span class="badge bg-danger bg-opacity-10 text-danger">Admin</span>
                        @elseif($user->role === 'manager')
                            <span class="badge bg-primary bg-opacity-10 text-primary">Quản lý</span>
                        @else
                            <span class="badge bg-success bg-opacity-10 text-success">Nhân viên</span>
                        @endif
                    </td>
                    <td class="text-center">
                        @if($user->status)
                            <span class="badge bg-success bg-opacity-10 text-success">Hoạt động</span>
                        @else
                            <span class="badge bg-danger bg-opacity-10 text-danger">Khóa</span>
                        @endif
                    </td>
                    <td class="small text-muted">{{ $user->created_at->format('d/m/Y') }}</td>
                    <td class="text-end">
                        <button type="button" class="btn btn-sm btn-outline-primary p-1 px-2 me-1"
                                data-bs-toggle="modal" data-bs-target="#modalEditUser{{ $user->id }}">
                            <i class="bi bi-pencil"></i>
                        </button>

                        @if($user->id !== Auth::id())
                        <form action="{{ route('users.toggle-status', $user->id) }}" method="POST" class="d-inline"
                              onsubmit="return confirm('Bạn có chắc muốn {{ $user->status ? 'khóa' : 'mở khóa' }} tài khoản [{{ $user->username }}]?');">
                            @csrf
                            <button type="submit" class="btn btn-sm {{ $user->status ? 'btn-outline-warning' : 'btn-outline-success' }} p-1 px-2"
                                    title="{{ $user->status ? 'Khóa' : 'Mở' }}">
                                <i class="bi {{ $user->status ? 'bi-lock' : 'bi-unlock' }}"></i>
                            </button>
                        </form>
                        @endif
                    </td>
                </tr>

                <div class="modal fade" id="modalEditUser{{ $user->id }}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog">
                        <form action="{{ route('users.update', $user->id) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="modal-content">
                                <div class="modal-header bg-primary">
                                    <h6 class="modal-title fw-semibold">Sửa Tài Khoản [{{ $user->username }}]</h6>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="mb-2">
                                        <label class="form-label small fw-medium">Họ và tên *</label>
                                        <input type="text" name="full_name" class="form-control" value="{{ $user->full_name }}" required>
                                    </div>
                                    <div class="mb-2">
                                        <label class="form-label small fw-medium">Email</label>
                                        <input type="email" name="email" class="form-control" value="{{ $user->email }}">
                                    </div>
                                    <div class="mb-2">
                                        <label class="form-label small fw-medium">Vai trò *</label>
                                        <select name="role" class="form-select" required>
                                            <option value="admin" {{ $user->role === 'admin' ? 'selected' : '' }}>Quản trị viên (Admin)</option>
                                            <option value="manager" {{ $user->role === 'manager' ? 'selected' : '' }}>Quản lý kho</option>
                                            <option value="staff" {{ $user->role === 'staff' ? 'selected' : '' }}>Nhân viên kho</option>
                                        </select>
                                    </div>
                                    <div class="mb-2">
                                        <label class="form-label small fw-medium">Mật khẩu mới (bỏ trống nếu không đổi)</label>
                                        <input type="password" name="password" class="form-control" placeholder="Tối thiểu 6 ký tự">
                                    </div>
                                </div>
                                <div class="modal-footer p-2">
                                    <button type="button" class="btn btn-sm btn-light border" data-bs-dismiss="modal">Đóng</button>
                                    <button type="submit" class="btn btn-sm btn-primary">Lưu thay đổi</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                @empty
                <tr>
                    <td colspan="7" class="text-center py-4 text-muted small">Không tìm thấy tài khoản nào.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if(method_exists($users, 'hasPages') && $users->hasPages())
    <div class="card-footer d-flex justify-content-between align-items-center">
        <small class="text-muted">Tổng: {{ $users->total() }} tài khoản</small>
        <div>
            {{ $users->withQueryString()->links() }}
        </div>
    </div>
    @endif
</div>

<div class="modal fade" id="modalCreateUser" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('users.store') }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header bg-primary">
                    <h6 class="modal-title fw-semibold">Tạo Tài Khoản Mới</h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-2">
                        <label class="form-label small fw-medium">Tên đăng nhập *</label>
                        <input type="text" name="username" class="form-control" placeholder="VD: thukho2" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small fw-medium">Mật khẩu ban đầu *</label>
                        <input type="password" name="password" class="form-control" placeholder="Tối thiểu 6 ký tự" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small fw-medium">Họ và tên *</label>
                        <input type="text" name="full_name" class="form-control" placeholder="VD: Nguyễn Văn Bình" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small fw-medium">Email</label>
                        <input type="email" name="email" class="form-control" placeholder="VD: binh@khohang.vn">
                    </div>
                    <div class="mb-2">
                        <label class="form-label small fw-medium">Vai trò *</label>
                        <select name="role" class="form-select" required>
                            <option value="staff">Nhân viên kho (Lập phiếu nhập/xuất, xem tồn)</option>
                            <option value="manager">Quản lý kho (Kiểm kê, hủy phiếu, quản lý danh mục)</option>
                            <option value="admin">Quản trị viên (Toàn quyền hệ thống)</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer p-2">
                    <button type="button" class="btn btn-sm btn-light border" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-sm btn-primary">Lưu tài khoản</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
