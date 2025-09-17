<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Laravel') }} - Auto Dealership</title>

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
        }

        .card-header {
            background-color: #fff;
            border-bottom: 1px solid rgba(0, 0, 0, 0.125);
        }

        .btn-group .btn {
            margin-right: 0.25rem;
        }

        .btn-group .btn:last-child {
            margin-right: 0;
        }

        /* Custom Navbar Styles */
        .navbar-custom {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .navbar-brand {
            font-weight: 700;
            font-size: 1.8rem;
            color: #fff !important;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .navbar-brand:hover {
            transform: translateY(-2px);
            text-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
        }

        .brand-icon {
            color: #ffd700;
            margin-right: 10px;
            font-size: 2rem;
            animation: car-bounce 2s ease-in-out infinite;
        }

        .brand-text {
            background: linear-gradient(45deg, #fff, #ffd700);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        @keyframes car-bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-3px); }
        }

        .navbar-nav {
            align-items: center;
        }
        
        .navbar-nav .nav-link {
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

        .navbar-nav .nav-link::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s;
        }

        .navbar-nav .nav-link:hover::before {
            left: 100%;
        }

        .navbar-nav .nav-link:hover {
            color: #fff !important;
            background: rgba(255, 255, 255, 0.1);
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }

        .navbar-nav .nav-link i {
            margin-right: 8px;
            font-size: 1.1rem;
        }

        /* Auth Links */
        .auth-link {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .auth-link.register {
            background: linear-gradient(45deg, #ff6b6b, #ee5a24);
            border: none;
        }

        .auth-link.register:hover {
            background: linear-gradient(45deg, #ee5a24, #ff6b6b);
            transform: translateY(-2px) scale(1.05);
        }

        /* Cart Link */
        .cart-link {
            background: linear-gradient(45deg, #ffd700, #ffed4e);
            color: #333 !important;
        }

        .cart-link:hover {
            background: linear-gradient(45deg, #ffed4e, #ffd700);
            color: #333 !important;
        }

        /* Admin Dropdown */
        .admin-dropdown {
            background: linear-gradient(45deg, #2c3e50, #34495e);
            border: 1px solid rgba(255, 255, 255, 0.1);
            position: relative;
        }
        
        /* Admin header spacing */
        .admin-item {
            margin: 0 8px;
        }

        .admin-dropdown-menu {
            background: rgba(44, 62, 80, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            margin-top: 10px;
            z-index: 9999 !important;
            position: absolute !important;
            top: 100% !important;
            left: 0 !important;
            min-width: 250px;
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

        /* Logout Button */
        .logout-btn {
            background: linear-gradient(45deg, #e74c3c, #c0392b);
            border: none;
            color: #fff !important;
        }

        .logout-btn:hover {
            background: linear-gradient(45deg, #c0392b, #e74c3c);
            transform: translateY(-2px);
        }

        /* Custom Toggler */
        .custom-toggler {
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 8px;
            padding: 8px 12px;
        }

        .custom-toggler:focus {
            box-shadow: 0 0 0 0.2rem rgba(255, 255, 255, 0.25);
        }

        /* Responsive */
        @media (max-width: 991.98px) {
            .navbar-nav .nav-link {
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
            
            .admin-dropdown-menu .dropdown-item {
                color: rgba(255, 255, 255, 0.9) !important;
                padding: 10px 20px;
                border-radius: 10px;
                margin: 2px 0;
            }
            
            .admin-dropdown-menu .dropdown-item:hover {
                background: rgba(255, 255, 255, 0.1) !important;
                transform: none;
            }
        }

        .price {
            font-weight: 600;
            color: #28a745;
        }

        .quantity {
            font-weight: 500;
            color: #6c757d;
        }

        /* Search Dropdown */
        .search-dropdown-menu {
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(15px);
            border: 1px solid rgba(102, 126, 234, 0.2);
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(102, 126, 234, 0.15);
            margin-top: 15px;
            min-width: 350px;
            padding: 10px;
        }

        .search-input {
            border-radius: 15px;
            border: 2px solid #e9ecef;
            transition: all 0.3s ease;
            padding: 12px 15px;
            font-size: 14px;
        }

        .search-input:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.3rem rgba(102, 126, 234, 0.15);
            transform: translateY(-1px);
        }

        .search-dropdown-menu .btn-primary {
            border-radius: 15px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            padding: 12px 20px;
            transition: all 0.3s ease;
        }

        .search-dropdown-menu .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.3);
        }

        /* Search Results Page Styling */
        .search-results-card {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border: none;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .search-results-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 20px 20px 0 0;
        }

        .search-form-enhanced {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .search-form-enhanced .input-group {
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.2);
        }

        .search-form-enhanced .form-control {
            border: none;
            padding: 15px 20px;
            font-size: 16px;
            background: white;
        }

        .search-form-enhanced .btn {
            border: none;
            padding: 15px 25px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            transition: all 0.3s ease;
        }

        .search-form-enhanced .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.3);
        }

        .product-card-enhanced {
            border: none;
            border-radius: 20px;
            overflow: hidden;
            transition: all 0.3s ease;
            background: white;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
        }

        .product-card-enhanced:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
        }

        .product-card-enhanced .card-img-top {
            transition: all 0.3s ease;
        }

        .product-card-enhanced:hover .card-img-top {
            transform: scale(1.05);
        }

        .search-info-alert {
            background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
            border: none;
            border-radius: 15px;
            color: white;
            padding: 15px 20px;
        }
    </style>
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-custom mb-4">
        <div class="container">
            <a class="navbar-brand" href="{{ route('categories.index') }}">
                <i class="fas fa-car brand-icon"></i> 
                <span class="brand-text">AutoDealer</span>
            </a>
            <button class="navbar-toggler custom-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                        @guest
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('categories.index') }}">
                                    <i class="fas fa-tags"></i> <span class="d-none d-lg-inline">Danh Mục</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link auth-link login" href="{{ route('login') }}">
                                    <i class="fas fa-sign-in-alt"></i> <span class="d-none d-lg-inline">Đăng Nhập</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link auth-link register" href="{{ route('register') }}">
                                    <i class="fas fa-user-plus"></i> <span class="d-none d-lg-inline">Đăng Ký</span>
                                </a>
                            </li>
                        @else
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('categories.index') }}">
                                    <i class="fas fa-tags"></i> <span class="d-none d-lg-inline">Danh Mục</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('products.index') }}">
                                    <i class="fas fa-car-side"></i> <span class="d-none d-lg-inline">Tất Cả Xe</span>
                                </a>
                            </li>
                            
                            <!-- Tìm kiếm -->
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                    <i class="fas fa-search"></i> <span class="d-none d-lg-inline">Tìm Kiếm</span>
                                </a>
                                <div class="dropdown-menu search-dropdown-menu">
                                    <form method="GET" action="{{ route('products.index') }}">
                                        <div class="input-group">
                                            <input type="text" name="search" class="form-control search-input" 
                                                   placeholder="Tìm kiếm xe..." autocomplete="off">
                                            <button class="btn btn-primary" type="submit">
                                                <i class="fas fa-search"></i>
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </li>
                        
                        <!-- Chỉ Admin thấy dropdown Admin -->
                        @if (auth()->user()->isAdmin())
                            <li class="nav-item dropdown admin-item">
                                <a class="nav-link dropdown-toggle admin-dropdown" href="#" role="button" data-bs-toggle="dropdown">
                                    <i class="fas fa-user-shield"></i> <span class="d-none d-xl-inline">Admin</span>
                                </a>
                                <ul class="dropdown-menu admin-dropdown-menu">
                                    <li><a class="dropdown-item" href="{{ route('admin.dashboard.index') }}">
                                        <i class="fas fa-tachometer-alt"></i> Dashboard
                                    </a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><h6 class="dropdown-header">Quản Lý</h6></li>
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
                                    <li><hr class="dropdown-divider"></li>
                                    <li><h6 class="dropdown-header">Thống Kê</h6></li>
                                    <li><a class="dropdown-item" href="{{ route('admin.users.statistics') }}">
                                        <i class="fas fa-chart-bar"></i> Người Dùng
                                    </a></li>
                                    <li><a class="dropdown-item" href="{{ route('admin.orders.statistics') }}">
                                        <i class="fas fa-chart-line"></i> Đơn Hàng
                                    </a></li>
                                    <li><a class="dropdown-item" href="{{ route('admin.dashboard.reports') }}">
                                        <i class="fas fa-file-alt"></i> Báo Cáo
                                    </a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><h6 class="dropdown-header">Khách Hàng</h6></li>
                                    <li><a class="dropdown-item" href="{{ route('user.orders.index') }}">
                                        <i class="fas fa-history"></i> Đơn Hàng Của Tôi
                                    </a></li>
                                    <li><a class="dropdown-item" href="{{ route('cart.index') }}">
                                        <i class="fas fa-shopping-cart"></i> Giỏ Hàng
                                        @if(session('cart'))
                                            <span class="badge bg-primary ms-2">{{ count(session('cart')) }}</span>
                                        @endif
                                    </a></li>
                                    <li><a class="dropdown-item" href="{{ route('wishlist.index') }}">
                                        <i class="fas fa-heart"></i> Yêu Thích
                                    </a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><h6 class="dropdown-header">Hệ Thống</h6></li>
                                    <li><a class="dropdown-item" href="{{ route('admin.system.overview') }}">
                                        <i class="fas fa-server"></i> Tổng Quan
                                    </a></li>
                                    <li><a class="dropdown-item" href="{{ route('admin.dashboard.settings') }}">
                                        <i class="fas fa-cogs"></i> Cài Đặt
                                    </a></li>
                                </ul>
                            </li>
                        @endif
                        
                        <!-- User items bên ngoài nhưng responsive -->
                        @if (!auth()->user()->isAdmin())
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('wishlist.index') }}">
                                    <i class="fas fa-heart"></i> <span class="d-none d-xl-inline">Yêu Thích</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('user.orders.index') }}">
                                    <i class="fas fa-history"></i> <span class="d-none d-xl-inline">Đơn Hàng</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link cart-link" href="{{ route('cart.index') }}">
                                    <i class="fas fa-shopping-cart"></i> <span class="d-none d-xl-inline">Giỏ hàng</span>
                                    @if(session('cart'))
                                        <span class="badge bg-warning text-dark ms-1">{{ count(session('cart')) }}</span>
                                    @else
                                        <span class="badge bg-secondary ms-1">0</span>
                                    @endif
                                </a>
                            </li>
                        @endif
                        <!-- Logout cho cả Customer và Admin -->
                        <li class="nav-item">
                            <form action="{{ route('logout') }}" method="POST" style="display:inline;">
                                @csrf
                                <button type="submit" class="nav-link btn btn-link logout-btn">
                                    <i class="fas fa-sign-out-alt"></i> <span class="d-none d-lg-inline">Đăng Xuất</span>
                                </button>
                            </form>
                        </li>
                    @endauth
                </ul>
            </div>
        </div>
    </nav>

    <main>
        @yield('content')
    </main>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>

</html>
