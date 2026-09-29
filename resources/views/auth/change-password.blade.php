@extends('layouts.app')

@section('title', 'Đổi Mật Khẩu')
@section('page_title', 'Thay Đổi Mật Khẩu Tài Khoản')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
        <div class="card p-4">
            <h5 class="fw-bold mb-3">Đổi Mật Khẩu</h5>

            <form action="{{ route('password.update') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label class="form-label small fw-medium">Mật khẩu hiện tại *</label>
                    <div class="position-relative">
                        <input type="password" name="current_password" id="current_password" class="form-control pe-5" required placeholder="Nhập mật khẩu đang dùng">
                        <button type="button" class="btn btn-link position-absolute end-0 top-50 translate-middle-y text-muted text-decoration-none pe-3" onclick="togglePassword('current_password', this)" tabindex="-1">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-medium">Mật khẩu mới *</label>
                    <div class="position-relative">
                        <input type="password" name="new_password" id="new_password" class="form-control pe-5" required placeholder="Tối thiểu 8 ký tự, có chữ và số">
                        <button type="button" class="btn btn-link position-absolute end-0 top-50 translate-middle-y text-muted text-decoration-none pe-3" onclick="togglePassword('new_password', this)" tabindex="-1">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                    <div class="form-text small text-muted">Tối thiểu 8 ký tự, gồm cả chữ cái và chữ số.</div>
                </div>

                <div class="mb-4">
                    <label class="form-label small fw-medium">Xác nhận mật khẩu mới *</label>
                    <div class="position-relative">
                        <input type="password" name="new_password_confirmation" id="new_password_confirmation" class="form-control pe-5" required placeholder="Nhập lại mật khẩu mới">
                        <button type="button" class="btn btn-link position-absolute end-0 top-50 translate-middle-y text-muted text-decoration-none pe-3" onclick="togglePassword('new_password_confirmation', this)" tabindex="-1">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('dashboard') }}" class="btn btn-sm btn-light border">Hủy bỏ</a>
                    <button type="submit" class="btn btn-sm btn-primary">
                        <i class="bi bi-check2 me-1"></i>Lưu mật khẩu mới
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function togglePassword(fieldId, btn) {
        const input = document.getElementById(fieldId);
        const icon = btn.querySelector('i');
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.remove('bi-eye');
            icon.classList.add('bi-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.remove('bi-eye-slash');
            icon.classList.add('bi-eye');
        }
    }
</script>
@endpush
@endsection
