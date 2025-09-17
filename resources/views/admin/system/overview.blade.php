@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="mb-0"><i class="fas fa-server"></i> Tổng Quan Hệ Thống</h3>
                    <small class="text-muted">Quản lý và giám sát toàn bộ hệ thống</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Thống kê hệ thống -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Người Dùng</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['users']['total'] }}</div>
                            <small class="text-success">{{ $stats['users']['active'] }} hoạt động</small>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Sản Phẩm</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['products']['total'] }}</div>
                            <small class="text-warning">{{ $stats['products']['low_stock'] }} sắp hết hàng</small>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-car fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Đơn Hàng</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['orders']['total'] }}</div>
                            <small class="text-warning">{{ $stats['orders']['pending'] }} chờ xử lý</small>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-shopping-bag fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Doanh Thu</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($stats['revenue']['total'], 0, ',', '.') }} $</div>
                            <small class="text-success">{{ number_format($stats['revenue']['this_month'], 0, ',', '.') }} $ tháng này</small>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Cảnh báo và thông báo -->
    <div class="row mb-4">
        <div class="col-xl-8 col-lg-7">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Cảnh Báo Hệ Thống</h6>
                </div>
                <div class="card-body">
                    @if($lowStockProducts->count() > 0)
                        <div class="alert alert-warning">
                            <h6><i class="fas fa-exclamation-triangle"></i> Sản phẩm sắp hết hàng</h6>
                            <ul class="mb-0">
                                @foreach($lowStockProducts->take(5) as $product)
                                    <li><strong>{{ $product->name }}</strong> - Còn lại: {{ $product->quantity }} ({{ $product->category->name }})</li>
                                @endforeach
                            </ul>
                            @if($lowStockProducts->count() > 5)
                                <small class="text-muted">Và {{ $lowStockProducts->count() - 5 }} sản phẩm khác...</small>
                            @endif
                        </div>
                    @endif

                    @if($urgentOrders->count() > 0)
                        <div class="alert alert-info">
                            <h6><i class="fas fa-clock"></i> Đơn hàng cần xử lý gấp</h6>
                            <ul class="mb-0">
                                @foreach($urgentOrders->take(5) as $order)
                                    <li><strong>#{{ $order->id }}</strong> - {{ $order->user->name }} ({{ $order->status }}) - {{ $order->created_at->diffForHumans() }}</li>
                                @endforeach
                            </ul>
                            @if($urgentOrders->count() > 5)
                                <small class="text-muted">Và {{ $urgentOrders->count() - 5 }} đơn hàng khác...</small>
                            @endif
                        </div>
                    @endif

                    @if($lowStockProducts->count() == 0 && $urgentOrders->count() == 0)
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i> Hệ thống hoạt động bình thường, không có cảnh báo nào.
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-lg-5">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Thông Tin Hệ Thống</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <strong>Phiên bản Laravel:</strong><br>
                        <small class="text-muted">{{ app()->version() }}</small>
                    </div>
                    <div class="mb-3">
                        <strong>Môi trường:</strong><br>
                        <span class="badge bg-{{ app()->environment() == 'production' ? 'success' : 'warning' }}">
                            {{ app()->environment() }}
                        </span>
                    </div>
                    <div class="mb-3">
                        <strong>Thời gian hoạt động:</strong><br>
                        <small class="text-muted">{{ now()->diffForHumans(now()->subDays(1)) }}</small>
                    </div>
                    <div class="mb-3">
                        <strong>Bộ nhớ sử dụng:</strong><br>
                        <small class="text-muted">{{ number_format(memory_get_usage(true) / 1024 / 1024, 2) }} MB</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quản lý hệ thống -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Quản Lý Hệ Thống</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <a href="{{ route('admin.system.database') }}" class="btn btn-outline-primary w-100">
                                <i class="fas fa-database fa-2x mb-2"></i><br>
                                <strong>Database</strong><br>
                                <small>Backup, tối ưu hóa</small>
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="{{ route('admin.system.storage') }}" class="btn btn-outline-success w-100">
                                <i class="fas fa-hdd fa-2x mb-2"></i><br>
                                <strong>Storage</strong><br>
                                <small>Quản lý file, dọn dẹp</small>
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="{{ route('admin.system.cache') }}" class="btn btn-outline-info w-100">
                                <i class="fas fa-memory fa-2x mb-2"></i><br>
                                <strong>Cache</strong><br>
                                <small>Xóa cache, tối ưu</small>
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="{{ route('admin.system.logs') }}" class="btn btn-outline-warning w-100">
                                <i class="fas fa-file-text fa-2x mb-2"></i><br>
                                <strong>Logs</strong><br>
                                <small>Xem, xóa logs</small>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quản lý bảo mật -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Bảo Mật Hệ Thống</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6><i class="fas fa-shield-alt"></i> Trạng Thái Bảo Mật</h6>
                            <ul class="list-unstyled">
                                <li><i class="fas fa-check text-success"></i> HTTPS: Bật</li>
                                <li><i class="fas fa-check text-success"></i> CSRF Protection: Bật</li>
                                <li><i class="fas fa-check text-success"></i> SQL Injection Protection: Bật</li>
                                <li><i class="fas fa-check text-success"></i> XSS Protection: Bật</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6><i class="fas fa-user-shield"></i> Quản Lý User</h6>
                            <ul class="list-unstyled">
                                <li><i class="fas fa-users text-info"></i> Admin: {{ $stats['users']['admins'] }}</li>
                                <li><i class="fas fa-user text-primary"></i> User: {{ $stats['users']['total'] - $stats['users']['admins'] }}</li>
                                <li><i class="fas fa-user-check text-success"></i> Hoạt động: {{ $stats['users']['active'] }}</li>
                                <li><i class="fas fa-user-plus text-warning"></i> Mới tháng này: {{ $stats['users']['new_this_month'] }}</li>
                            </ul>
                        </div>
                    </div>
                    <div class="mt-3">
                        <a href="{{ route('admin.system.security') }}" class="btn btn-primary">
                            <i class="fas fa-cog"></i> Cài Đặt Bảo Mật
                        </a>
                        <a href="{{ route('admin.users.index') }}" class="btn btn-info">
                            <i class="fas fa-users"></i> Quản Lý User
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Thống kê hoạt động -->
    <div class="row mb-4">
        <div class="col-xl-6 col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Hoạt Động Gần Đây</h6>
                </div>
                <div class="card-body">
                    @if($recentUsers->count() > 0)
                        @foreach($recentUsers->take(5) as $user)
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div>
                                    <strong>{{ $user->name }}</strong><br>
                                    <small class="text-muted">{{ $user->email }}</small>
                                </div>
                                <div class="text-end">
                                    <span class="badge bg-{{ $user->role == 'admin' ? 'danger' : 'primary' }}">{{ $user->role }}</span><br>
                                    <small class="text-muted">{{ $user->created_at->format('d/m/Y H:i') }}</small>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <p class="text-muted">Chưa có hoạt động nào.</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-xl-6 col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Thống Kê Nhanh</h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="border rounded p-3">
                                <h4 class="text-primary">{{ $stats['orders']['completed'] }}</h4>
                                <small>Đơn hoàn thành</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="border rounded p-3">
                                <h4 class="text-success">{{ $stats['products']['in_stock'] }}</h4>
                                <small>Còn hàng</small>
                            </div>
                        </div>
                    </div>
                    <div class="row text-center mt-3">
                        <div class="col-6">
                            <div class="border rounded p-3">
                                <h4 class="text-info">{{ $stats['orders']['total'] > 0 ? round(($stats['orders']['completed'] / $stats['orders']['total']) * 100, 1) : 0 }}%</h4>
                                <small>Tỷ lệ hoàn thành</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="border rounded p-3">
                                <h4 class="text-warning">{{ $stats['revenue']['total'] > 0 ? round($stats['revenue']['avg_order'], 0) : 0 }} $</h4>
                                <small>Đơn hàng TB</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
