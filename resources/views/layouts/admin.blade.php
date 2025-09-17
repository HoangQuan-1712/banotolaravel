<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Panel') - {{ config('app.name', 'Laravel') }}</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="{{ asset('css/custom.css') }}" rel="stylesheet">

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8f9fa;
        }

        .card {
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            border: 1px solid rgba(0, 0, 0, 0.125);
            border-radius: 0.5rem;
        }

        .card-header {
            background-color: #fff;
            border-bottom: 1px solid rgba(0, 0, 0, 0.125);
            border-radius: 0.5rem 0.5rem 0 0;
        }

        .card-title {
            margin-bottom: 0;
            font-weight: 600;
        }

        /* Admin Navbar Styles */
        .admin-navbar {
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .admin-navbar .navbar-brand {
            font-weight: 700;
            font-size: 1.8rem;
            color: #fff !important;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .admin-navbar .navbar-brand:hover {
            transform: translateY(-2px);
            text-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
        }

        .admin-navbar .brand-icon {
            color: #e74c3c;
            margin-right: 10px;
            font-size: 2rem;
        }

        .admin-navbar .brand-text {
            background: linear-gradient(45deg, #fff, #e74c3c);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .admin-navbar .nav-link {
            color: rgba(255, 255, 255, 0.9) !important;
            font-weight: 500;
            padding: 8px 16px !important;
            margin: 0 3px;
            border-radius: 25px;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            white-space: nowrap;
            display: flex;
            align-items: center;
        }

        .admin-navbar .nav-link:hover {
            color: #fff !important;
            background: rgba(255, 255, 255, 0.1);
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }

        .admin-navbar .nav-link i {
            margin-right: 8px;
            font-size: 1.1rem;
        }

        /* Admin Dropdown */
        .admin-dropdown-menu {
            background: rgba(44, 62, 80, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            margin-top: 10px;
        }

        .admin-dropdown-menu .dropdown-item {
            color: rgba(255, 255, 255, 0.9);
            padding: 12px 20px;
            transition: all 0.3s ease;
        }

        .admin-dropdown-menu .dropdown-item:hover {
            background: rgba(255, 255, 255, 0.1);
            color: #fff;
            transform: translateX(5px);
        }

        .admin-dropdown-menu .dropdown-divider {
            border-color: rgba(255, 255, 255, 0.1);
        }

        .admin-dropdown-menu .dropdown-header {
            color: rgba(255, 255, 255, 0.7);
            font-weight: 600;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        /* Logout Button */
        .logout-btn {
            background: linear-gradient(45deg, #e74c3c, #c0392b);
            border: none;
            color: #fff !important;
            border-radius: 25px;
        }

        .logout-btn:hover {
            background: linear-gradient(45deg, #c0392b, #e74c3c);
            transform: translateY(-2px);
        }

        /* Alert Styles */
        .alert {
            border-radius: 0.5rem;
            border: none;
        }

        .alert-warning {
            background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);
            color: #fff;
        }

        .alert-info {
            background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
            color: #fff;
        }

        /* Button Styles */
        .btn {
            border-radius: 0.375rem;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-danger {
            background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
            border: none;
        }

        .btn-danger:hover {
            background: linear-gradient(135deg, #c0392b 0%, #e74c3c 100%);
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(231, 76, 60, 0.3);
        }

        .btn-secondary {
            background: linear-gradient(135deg, #95a5a6 0%, #7f8c8d 100%);
            border: none;
        }

        .btn-secondary:hover {
            background: linear-gradient(135deg, #7f8c8d 0%, #95a5a6 100%);
            transform: translateY(-2px);
        }

        /* Badge Styles */
        .badge {
            font-weight: 500;
            padding: 0.5em 0.75em;
            border-radius: 0.375rem;
        }

        .badge-danger {
            background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
        }

        .badge-primary {
            background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
        }

        .badge-info {
            background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
        }

        .badge-success {
            background: linear-gradient(135deg, #27ae60 0%, #229954 100%);
        }

        .badge-warning {
            background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);
        }

        /* Table Styles */
        .table {
            border-radius: 0.5rem;
            overflow: hidden;
        }

        .table th {
            background-color: #f8f9fa;
            border-bottom: 2px solid #dee2e6;
            font-weight: 600;
        }

        /* Responsive */
        @media (max-width: 991.98px) {
            .admin-navbar .nav-link {
                margin: 5px 0;
                text-align: center;
            }
            
            .admin-dropdown-menu {
                background: rgba(44, 62, 80, 0.98);
                border-radius: 10px;
                position: static !important;
                margin-top: 0;
                box-shadow: none;
                border: none;
                background: rgba(255, 255, 255, 0.05) !important;
            }
        }
    </style>
</head>

<body>
    <nav class="navbar navbar-expand-lg admin-navbar mb-4">
        <div class="container-fluid">
            <a class="navbar-brand" href="{{ route('admin.dashboard.index') }}">
                <i class="fas fa-user-shield brand-icon"></i> 
                <span class="brand-text">Admin Panel</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#adminNavbar">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="adminNavbar">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('admin.dashboard.index') }}">
                            <i class="fas fa-tachometer-alt"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-cogs"></i> Quản Lý
                        </a>
                        <ul class="dropdown-menu admin-dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('admin.categories.index') }}">
                                <i class="fas fa-tags"></i> Danh Mục
                            </a></li>
                            <li><a class="dropdown-item" href="{{ route('admin.products.index') }}">
                                <i class="fas fa-car-side"></i> Xe
                            </a></li>
                            <li><a class="dropdown-item" href="{{ route('admin.users.index') }}">
                                <i class="fas fa-users"></i> Người Dùng
                            </a></li>
                            <li><a class="dropdown-item" href="{{ route('admin.orders.index') }}">
                                <i class="fas fa-shopping-bag"></i> Đơn Hàng
                            </a></li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-chart-bar"></i> Thống Kê
                        </a>
                        <ul class="dropdown-menu admin-dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('admin.users.statistics') }}">
                                <i class="fas fa-chart-bar"></i> Người Dùng
                            </a></li>
                            <li><a class="dropdown-item" href="{{ route('admin.orders.statistics') }}">
                                <i class="fas fa-chart-line"></i> Đơn Hàng
                            </a></li>
                            <li><a class="dropdown-item" href="{{ route('admin.dashboard.reports') }}">
                                <i class="fas fa-file-alt"></i> Báo Cáo
                            </a></li>
                        </ul>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('categories.index') }}">
                            <i class="fas fa-globe"></i> Xem Trang Chủ
                        </a>
                    </li>
                    <li class="nav-item">
                        <span class="nav-link">
                            <i class="fas fa-user"></i> {{ auth()->user()->name }}
                        </span>
                    </li>
                    <li class="nav-item">
                        <form action="{{ route('logout') }}" method="POST" style="display:inline;">
                            @csrf
                            <button type="submit" class="nav-link btn btn-link logout-btn">
                                <i class="fas fa-sign-out-alt"></i> Đăng Xuất
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <main class="container-fluid">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('warning'))
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle"></i> {{ session('warning') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @yield('content')
    </main>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>

</html>
