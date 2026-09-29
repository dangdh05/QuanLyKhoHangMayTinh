<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng ký tài khoản - Quản Lý Kho Máy Tính</title>
    <!-- Google Fonts: Roboto (Chuẩn phong cách KiotViet) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        :root {
            --sky-blue: #0ea5e9;
            --sky-blue-hover: #0284c7;
            --sky-blue-subtle: #f0f9ff;
            --text-main: #0f172a;
            --text-muted: #64748b;
        }

        body {
            background-color: #f8fafc;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Roboto', -apple-system, BlinkMacSystemFont, "Segoe UI", Arial, sans-serif;
            font-size: 13.5px;
            color: var(--text-main);
            padding: 1.5rem;
            margin: 0;
        }

        .auth-card {
            width: 100%;
            max-width: 480px;
            background: #ffffff;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.05);
            padding: 2.25rem;
        }

        .brand-header {
            text-align: center;
            margin-bottom: 1.75rem;
        }

        .brand-icon {
            width: 44px;
            height: 44px;
            background: var(--sky-blue);
            border-radius: 10px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #ffffff;
            font-size: 1.25rem;
            margin-bottom: 0.75rem;
        }

        .brand-title {
            font-size: 1.15rem;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 0.25rem;
            letter-spacing: -0.01em;
        }

        .brand-subtitle {
            font-size: 0.82rem;
            color: var(--text-muted);
            margin: 0;
        }

        .form-label {
            font-size: 0.82rem;
            font-weight: 500;
            color: #334155;
            margin-bottom: 0.35rem;
        }

        .form-control {
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            padding: 0.55rem 0.85rem;
            font-size: 0.88rem;
            color: var(--text-main);
            box-shadow: none !important;
            transition: border-color 0.15s ease;
        }

        .form-control:focus {
            border-color: var(--sky-blue);
        }

        /* Nút toggle hiện mật khẩu */
        .password-field-wrapper {
            position: relative;
        }

        .password-field-wrapper .form-control {
            padding-right: 2.75rem;
        }

        .btn-toggle-password {
            position: absolute;
            right: 0;
            top: 0;
            height: 100%;
            width: 2.75rem;
            border: none;
            background: transparent;
            color: #94a3b8;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 1.05rem;
            transition: color 0.15s ease;
            z-index: 5;
        }

        .btn-toggle-password:hover {
            color: var(--sky-blue);
        }

        .btn-primary {
            background-color: var(--sky-blue) !important;
            border-color: var(--sky-blue) !important;
            color: #ffffff !important;
            border-radius: 6px;
            font-weight: 600;
            font-size: 0.9rem;
            padding: 0.6rem 1rem;
            box-shadow: none !important;
            transition: background-color 0.15s ease;
        }

        .btn-primary:hover, .btn-primary:focus {
            background-color: var(--sky-blue-hover) !important;
            border-color: var(--sky-blue-hover) !important;
        }

        .auth-footer {
            margin-top: 1.5rem;
            padding-top: 1.25rem;
            border-top: 1px solid #f1f5f9;
            text-align: center;
            font-size: 0.84rem;
            color: var(--text-muted);
        }

        .auth-footer a {
            color: var(--sky-blue);
            font-weight: 600;
            text-decoration: none;
        }

        .auth-footer a:hover {
            color: var(--sky-blue-hover);
            text-decoration: underline;
        }

        .password-hint {
            font-size: 0.74rem;
            color: #64748b;
            margin-top: 0.35rem;
        }
    </style>
</head>
<body>
    <div class="auth-card">
        <div class="brand-header">
            <div class="brand-icon">
                <i class="bi bi-person-plus"></i>
            </div>
            <h1 class="brand-title">Đăng Ký Tài Khoản</h1>
            <p class="brand-subtitle">Tạo tài khoản nhân viên kho mới trong hệ thống</p>
        </div>

        @if($errors->any())
            <div class="alert alert-danger py-2 px-3 small border-0 bg-danger bg-opacity-10 text-danger mb-3 rounded-2" role="alert">
                <ul class="mb-0 ps-3">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('register.post') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label for="full_name" class="form-label">Họ và tên <span class="text-danger">*</span></label>
                <input type="text" name="full_name" id="full_name" class="form-control" placeholder="Ví dụ: Nguyễn Văn An" value="{{ old('full_name') }}" required autofocus>
            </div>

            <div class="mb-3">
                <label for="username" class="form-label">Tên đăng nhập <span class="text-danger">*</span></label>
                <input type="text" name="username" id="username" class="form-control" placeholder="Tối thiểu 4 ký tự..." value="{{ old('username') }}" required>
            </div>

            <div class="mb-3">
                <label for="email" class="form-label">Địa chỉ Email</label>
                <input type="email" name="email" id="email" class="form-control" placeholder="user@khohang.vn (không bắt buộc)" value="{{ old('email') }}">
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">Mật khẩu <span class="text-danger">*</span></label>
                <div class="password-field-wrapper">
                    <input type="password" name="password" id="password" class="form-control" placeholder="Tối thiểu 8 ký tự, có chữ và số..." required>
                    <button type="button" class="btn-toggle-password" onclick="togglePasswordVisibility('password', this)" title="Hiện/Ẩn mật khẩu" tabindex="-1">
                        <i class="bi bi-eye"></i>
                    </button>
                </div>
                <div class="password-hint">
                    <i class="bi bi-info-circle me-1"></i>Yêu cầu: Tối thiểu 8 ký tự, có cả chữ cái và chữ số.
                </div>
            </div>

            <div class="mb-4">
                <label for="password_confirmation" class="form-label">Xác nhận mật khẩu <span class="text-danger">*</span></label>
                <div class="password-field-wrapper">
                    <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" placeholder="Nhập lại mật khẩu..." required>
                    <button type="button" class="btn-toggle-password" onclick="togglePasswordVisibility('password_confirmation', this)" title="Hiện/Ẩn mật khẩu" tabindex="-1">
                        <i class="bi bi-eye"></i>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn btn-primary w-100">
                Tạo tài khoản
            </button>
        </form>

        <div class="auth-footer">
            Đã có tài khoản? <a href="{{ route('login') }}">Đăng nhập ngay</a>
        </div>
    </div>

    <script>
        function togglePasswordVisibility(fieldId, button) {
            const input = document.getElementById(fieldId);
            const icon = button.querySelector('i');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('bi-eye');
                icon.classList.add('bi-eye-slash');
                button.title = 'Ẩn mật khẩu';
            } else {
                input.type = 'password';
                icon.classList.remove('bi-eye-slash');
                icon.classList.add('bi-eye');
                button.title = 'Hiện mật khẩu';
            }
        }
    </script>
</body>
</html>
