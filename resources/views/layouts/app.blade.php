<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Quản Lý Kho')</title>
    <!-- Google Fonts: Roboto (Chuẩn phong cách KiotViet) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        :root {
            --sidebar-width: 250px;
            --sky-blue: #0ea5e9;
            --sky-blue-hover: #0284c7;
            --sky-blue-subtle: #f0f9ff;
            --border-color: #f1f5f9;
            --border-hover: #e2e8f0;
            --text-main: #0f172a;
            --text-muted: #64748b;
            --bg-body: #ffffff;
            --bg-card: #ffffff;
            --bg-table-head: #fafbfc;
        }

        * {
            box-sizing: border-box;
        }

        body {
            background-color: var(--bg-body);
            font-family: 'Roboto', -apple-system, BlinkMacSystemFont, "Segoe UI", Arial, sans-serif;
            font-size: 13.5px;
            line-height: 1.45;
            color: var(--text-main);
            margin: 0;
            padding: 0;
            -webkit-font-smoothing: antialiased;
        }

        /* Sidebar tối giản, nền trắng và có thể cuộn trượt mượt mà */
        .sidebar {
            width: var(--sidebar-width);
            height: 100vh;
            max-height: 100vh;
            background: #ffffff;
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            z-index: 1000;
            overflow-y: auto;
            overflow-x: hidden;
            border-right: 1px solid #eef2f6;
            display: flex;
            flex-direction: column;
        }
        .sidebar::-webkit-scrollbar {
            width: 5px;
        }
        .sidebar::-webkit-scrollbar-track {
            background: #f8fafc;
        }
        .sidebar::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }
        .sidebar::-webkit-scrollbar-thumb:hover {
            background: #0ea5e9;
        }
        .sidebar-brand {
            flex-shrink: 0;
            padding: 1.15rem 1.25rem 0.9rem;
            display: flex;
            align-items: center;
            gap: 0.6rem;
            text-decoration: none;
            color: var(--text-main);
            border-bottom: 1px solid #f1f5f9;
            background: #ffffff;
            position: sticky;
            top: 0;
            z-index: 10;
        }
        .sidebar-brand-icon {
            width: 32px;
            height: 32px;
            background: var(--sky-blue);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ffffff;
            font-size: 1rem;
        }
        .sidebar-brand-text {
            font-weight: 700;
            font-size: 0.95rem;
            letter-spacing: -0.01em;
            color: #0f172a;
        }

        .sidebar-menu {
            flex: 1;
            padding: 0.75rem 0.6rem 4rem 0.6rem;
        }
        .menu-header {
            padding: 0.85rem 0.75rem 0.35rem;
            font-size: 0.68rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #94a3b8;
            font-weight: 600;
        }
        .nav-item-link {
            color: #475569;
            padding: 0.55rem 0.75rem;
            display: flex;
            align-items: center;
            gap: 0.65rem;
            font-size: 0.86rem;
            font-weight: 500;
            border-radius: 6px;
            text-decoration: none;
            margin-bottom: 2px;
            transition: background 0.15s ease, color 0.15s ease;
        }
        .nav-item-link i {
            font-size: 1rem;
            color: #94a3b8;
            transition: color 0.15s ease;
        }
        .nav-item-link:hover {
            color: var(--sky-blue);
            background: #f8fafc;
        }
        .nav-item-link:hover i {
            color: var(--sky-blue);
        }
        .nav-item-link.active {
            color: #0284c7;
            background: #f0f9ff;
            font-weight: 600;
        }
        .nav-item-link.active i {
            color: var(--sky-blue);
        }

        /* Khung nội dung chính */
        .main-wrapper {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            background: #ffffff;
        }
        .topbar {
            background: #ffffff;
            border-bottom: 1px solid #eef2f6;
            padding: 0.85rem 1.5rem;
            position: sticky;
            top: 0;
            z-index: 990;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .page-heading {
            font-size: 1.05rem;
            font-weight: 700;
            color: #0f172a;
            margin: 0;
        }
        .main-container {
            padding: 1.5rem;
            flex: 1;
            background: #ffffff;
        }

        /* Nút màu xanh da trời tối giản */
        .btn-primary {
            background-color: var(--sky-blue) !important;
            border-color: var(--sky-blue) !important;
            color: #ffffff !important;
            border-radius: 6px;
            font-weight: 500;
            font-size: 0.86rem;
            padding: 0.45rem 0.9rem;
            box-shadow: none !important;
            transition: background 0.15s ease;
        }
        .btn-primary:hover, .btn-primary:focus {
            background-color: var(--sky-blue-hover) !important;
            border-color: var(--sky-blue-hover) !important;
        }

        .btn-outline-primary {
            color: #0284c7 !important;
            border-color: var(--sky-blue) !important;
            background: #ffffff !important;
            border-radius: 6px;
            font-weight: 500;
            font-size: 0.86rem;
            padding: 0.45rem 0.9rem;
            box-shadow: none !important;
            transition: all 0.15s ease;
        }
        .btn-outline-primary:hover {
            background-color: var(--sky-blue) !important;
            border-color: var(--sky-blue) !important;
            color: #ffffff !important;
        }

        /* Cards tối giản phẳng - viền mảnh */
        .card {
            border: 1px solid #eef2f6 !important;
            border-radius: 8px !important;
            box-shadow: none !important;
            background: #ffffff;
            margin-bottom: 1.25rem;
        }
        .card-header {
            background: #ffffff;
            border-bottom: 1px solid #f1f5f9;
            padding: 0.85rem 1.15rem;
            font-weight: 600;
            font-size: 0.92rem;
            color: #1e293b;
        }
        .card-body {
            padding: 1.15rem;
        }
        .card-footer {
            background: #ffffff;
            border-top: 1px solid #f1f5f9;
            padding: 0.75rem 1.15rem;
        }

        /* Form elements tinh tế */
        .form-control, .form-select {
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            font-size: 0.86rem;
            padding: 0.45rem 0.75rem;
            color: #334155;
            box-shadow: none !important;
        }
        .form-control:focus, .form-select:focus {
            border-color: var(--sky-blue);
            outline: none;
        }

        /* Bảng tối giản, sạch sẽ */
        .table {
            margin-bottom: 0;
            font-size: 0.86rem;
            color: #334155;
        }
        .table > :not(caption) > * > * {
            padding: 0.75rem 0.9rem;
            border-bottom: 1px solid #f8fafc;
            vertical-align: middle;
        }
        .table thead th {
            background: #fcfcfd !important;
            color: #64748b;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.03em;
            border-bottom: 1px solid #eef2f6;
            white-space: nowrap;
        }
        .table tbody tr:hover {
            background-color: #fbfdff !important;
        }

        /* Badges tối giản */
        .badge {
            font-weight: 500;
            font-size: 0.75rem;
            padding: 0.3em 0.6em;
            border-radius: 4px;
        }

        /* Modal header tối giản */
        .modal-header.bg-primary {
            background-color: var(--sky-blue) !important;
            color: #ffffff !important;
            border-bottom: 0;
            padding: 1rem 1.25rem;
        }
        .modal-content {
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.08);
        }

        /* Phân trang phẳng tối giản */
        .pagination {
            gap: 2px;
            margin: 0;
        }
        .pagination .page-link {
            border: 1px solid #e2e8f0;
            color: #64748b;
            border-radius: 4px !important;
            padding: 0.35rem 0.65rem;
            font-size: 0.8rem;
        }
        .pagination .page-item.active .page-link {
            background: var(--sky-blue) !important;
            border-color: var(--sky-blue) !important;
            color: #ffffff !important;
        }

        /* Text colors */
        .text-primary {
            color: #0284c7 !important;
        }
        a {
            color: #0284c7;
            text-decoration: none;
        }
        a:hover {
            color: #0369a1;
        }

        /* Định dạng giá trị tiền tệ chuyên nghiệp, đồng bộ font chữ đẹp và luôn trên 1 dòng */
        .font-monospace, .money-amount, .text-money, .price-val {
            font-family: 'Roboto', -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif !important;
            font-variant-numeric: tabular-nums lining-nums;
            -webkit-font-feature-settings: "tnum" 1, "lnum" 1;
            font-feature-settings: "tnum" 1, "lnum" 1;
            white-space: nowrap !important;
            letter-spacing: -0.01em;
        }

        /* Đảm bảo cột tiền trong bảng luôn trên 1 dòng, không bao giờ ngắt xuống hàng */
        td.text-end, th.text-end, .text-nowrap, .money-amount, .currency-cell {
            white-space: nowrap !important;
        }

        .money-val {
            font-family: 'Roboto', -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif !important;
            font-variant-numeric: tabular-nums lining-nums;
            white-space: nowrap !important;
            font-weight: 500;
        }
        .money-val.bold {
            font-weight: 600;
        }
    </style>
    @stack('styles')
</head>
<body>
    <!-- Sidebar Nền Trắng Tối Giản -->
    <aside class="sidebar">
        <a href="{{ route('dashboard') }}" class="sidebar-brand">
            <div class="sidebar-brand-icon">
                <i class="bi bi-box-seam"></i>
            </div>
            <span class="sidebar-brand-text">Quản Lý Kho</span>
        </a>

        <div class="sidebar-menu">
            <a href="{{ route('dashboard') }}" class="nav-item-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <i class="bi bi-grid"></i>
                <span>Tổng quan</span>
            </a>

            <div class="menu-header">Mặt Hàng</div>
            <a href="{{ route('products.index') }}" class="nav-item-link {{ request()->routeIs('products.index') ? 'active' : '' }}">
                <i class="bi bi-laptop"></i>
                <span>Danh mục máy</span>
            </a>
            <a href="{{ route('products.stock') }}" class="nav-item-link {{ request()->routeIs('products.stock') ? 'active' : '' }}">
                <i class="bi bi-boxes"></i>
                <span>Tồn kho hiện tại</span>
            </a>
            <a href="{{ route('stock-cards.index') }}" class="nav-item-link {{ request()->routeIs('stock-cards.*') ? 'active' : '' }}">
                <i class="bi bi-clock-history"></i>
                <span>Thẻ kho</span>
            </a>
            @if(Auth::check() && Auth::user()->isManager())
            <a href="{{ route('stock-check.index') }}" class="nav-item-link {{ request()->routeIs('stock-check.*') ? 'active' : '' }}">
                <i class="bi bi-check2-square"></i>
                <span>Kiểm kê kho</span>
            </a>
            @endif

            <div class="menu-header">Nhập / Xuất</div>
            <a href="{{ route('inbounds.index') }}" class="nav-item-link {{ request()->routeIs('inbounds.*') ? 'active' : '' }}">
                <i class="bi bi-box-arrow-in-down"></i>
                <span>Phiếu nhập kho</span>
            </a>
            <a href="{{ route('outbounds.index') }}" class="nav-item-link {{ request()->routeIs('outbounds.*') ? 'active' : '' }}">
                <i class="bi bi-box-arrow-up"></i>
                <span>Phiếu xuất kho</span>
            </a>

            <div class="menu-header">Đối Tác</div>
            <a href="{{ route('suppliers.index') }}" class="nav-item-link {{ request()->routeIs('suppliers.*') ? 'active' : '' }}">
                <i class="bi bi-truck"></i>
                <span>Nhà cung cấp</span>
            </a>
            <a href="{{ route('customers.index') }}" class="nav-item-link {{ request()->routeIs('customers.*') ? 'active' : '' }}">
                <i class="bi bi-people"></i>
                <span>Khách hàng</span>
            </a>

            <div class="menu-header">Báo Cáo</div>
            <a href="{{ route('reports.slow-moving') }}" class="nav-item-link {{ request()->routeIs('reports.slow-moving') ? 'active' : '' }}">
                <i class="bi bi-hourglass-split"></i>
                <span>Hàng tồn lâu ngày</span>
            </a>
            <a href="{{ route('reports.low-stock') }}" class="nav-item-link {{ request()->routeIs('reports.low-stock') ? 'active' : '' }}">
                <i class="bi bi-exclamation-circle"></i>
                <span>Hàng sắp hết (QT7)</span>
            </a>
            <a href="{{ route('reports.in-out-stock') }}" class="nav-item-link {{ request()->routeIs('reports.in-out-stock') ? 'active' : '' }}">
                <i class="bi bi-bar-chart"></i>
                <span>Nhập - Xuất - Tồn</span>
            </a>
            <a href="{{ route('reports.inventory-value') }}" class="nav-item-link {{ request()->routeIs('reports.inventory-value') ? 'active' : '' }}">
                <i class="bi bi-cash-stack"></i>
                <span>Giá trị tồn kho</span>
            </a>

            @if(Auth::check() && Auth::user()->isAdmin())
            <div class="menu-header">Hệ Thống</div>
            <a href="{{ route('users.index') }}" class="nav-item-link {{ request()->routeIs('users.*') ? 'active' : '' }}">
                <i class="bi bi-person-gear"></i>
                <span>Tài khoản người dùng</span>
            </a>
            @endif
        </div>
    </aside>

    <!-- Main Wrapper -->
    <div class="main-wrapper">
        <header class="topbar">
            <h1 class="page-heading">@yield('page_title', 'Hệ Thống Quản Lý Kho')</h1>

            <div class="d-flex align-items-center gap-2">
                @auth
                <div class="dropdown">
                    <button class="btn btn-sm btn-light border dropdown-toggle d-flex align-items-center gap-2" type="button" data-bs-toggle="dropdown">
                        <i class="bi bi-person text-secondary"></i>
                        <span>{{ Auth::user()->full_name }}</span>
                        <span class="badge bg-light text-secondary border ms-1" style="font-size: 0.7rem;">
                            {{ Auth::user()->role === 'admin' ? 'Admin' : (Auth::user()->role === 'manager' ? 'Quản lý' : 'Nhân viên') }}
                        </span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end border shadow-sm">
                        @if(Auth::user()->isAdmin())
                        <li>
                            <a class="dropdown-item py-1" href="{{ route('users.index') }}">
                                <i class="bi bi-person-gear me-2 text-muted"></i>Tài khoản người dùng (Hệ thống)
                            </a>
                        </li>
                        @endif
                        <li>
                            <a class="dropdown-item py-1" href="{{ route('password.change') }}">
                                <i class="bi bi-key me-2 text-muted"></i>Đổi mật khẩu
                            </a>
                        </li>
                        <li><hr class="dropdown-divider my-1"></li>
                        <li>
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button type="submit" class="dropdown-item py-1 text-danger">
                                    <i class="bi bi-box-arrow-right me-2"></i>Đăng xuất
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
                @endauth
            </div>
        </header>

        <main class="main-container">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show py-2 px-3 small border-0 bg-success bg-opacity-10 text-success mb-3" role="alert">
                    <i class="bi bi-check2 me-1"></i>{{ session('success') }}
                    <button type="button" class="btn-close btn-close-sm" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('warning'))
                <div class="alert alert-warning alert-dismissible fade show py-2 px-3 small border-0 bg-warning bg-opacity-10 text-dark mb-3" role="alert">
                    <i class="bi bi-exclamation-triangle me-1"></i>{{ session('warning') }}
                    <button type="button" class="btn-close btn-close-sm" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show py-2 px-3 small border-0 bg-danger bg-opacity-10 text-danger mb-3" role="alert">
                    <i class="bi bi-x-circle me-1"></i>{{ session('error') }}
                    <button type="button" class="btn-close btn-close-sm" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger py-2 px-3 small border-0 bg-danger bg-opacity-10 text-danger mb-3" role="alert">
                    <ul class="mb-0 ps-3">
                        @foreach($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @yield('content')
        </main>
    </div>

    <!-- Bootstrap 5 Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
