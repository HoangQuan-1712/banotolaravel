@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="mb-0"><i class="fas fa-tachometer-alt"></i> Dashboard Tổng Quan</h3>
                    <small class="text-muted">Quản lý toàn bộ hệ thống từ một nơi</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Thống kê tổng quan -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Tổng Người Dùng</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalUsers }}</div>
                            <small class="text-success">+{{ $newUsersThisMonth }} tháng này</small>
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
                                Tổng Đơn Hàng</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalOrders }}</div>
                            <small class="text-success">+{{ $newOrdersThisMonth }} tháng này</small>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-shopping-bag fa-2x text-gray-300"></i>
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
                                Tổng Sản Phẩm</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalProducts }}</div>
                            <small class="text-warning">{{ $lowStockProducts->count() }} sắp hết hàng</small>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-car fa-2x text-gray-300"></i>
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
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($totalRevenue, 0, ',', '.') }} $</div>
                            <small class="text-success">+{{ number_format($revenueThisMonth, 0, ',', '.') }} $ tháng này</small>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Thống kê đơn hàng -->
    <div class="row mb-4">
        <div class="col-xl-8 col-lg-7">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Thống Kê Đơn Hàng Theo Trạng Thái</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 text-center">
                            <div class="border rounded p-3">
                                <h4 class="text-warning">{{ $pendingOrders }}</h4>
                                <small>Chờ Xử Lý</small>
                            </div>
                        </div>
                        <div class="col-md-3 text-center">
                            <div class="border rounded p-3">
                                <h4 class="text-info">{{ $processingOrders }}</h4>
                                <small>Đang Xử Lý</small>
                            </div>
                        </div>
                        <div class="col-md-3 text-center">
                            <div class="border rounded p-3">
                                <h4 class="text-success">{{ $completedOrders }}</h4>
                                <small>Hoàn Thành</small>
                            </div>
                        </div>
                        <div class="col-md-3 text-center">
                            <div class="border rounded p-3">
                                <h4 class="text-danger">{{ $cancelledOrders }}</h4>
                                <small>Đã Hủy</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-lg-5">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Sản Phẩm Sắp Hết Hàng</h6>
                </div>
                <div class="card-body">
                    @if($lowStockProducts->count() > 0)
                        @foreach($lowStockProducts as $product)
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div>
                                    <strong>{{ $product->name }}</strong><br>
                                    <small class="text-muted">{{ $product->category->name }}</small>
                                </div>
                                <span class="badge bg-warning">{{ $product->quantity }} còn lại</span>
                            </div>
                        @endforeach
                        <a href="{{ route('admin.products.index') }}" class="btn btn-sm btn-primary mt-2">
                            Xem Tất Cả Sản Phẩm
                        </a>
                    @else
                        <p class="text-success">Tất cả sản phẩm đều có đủ hàng!</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Top sản phẩm và khách hàng -->
    <div class="row mb-4">
        <div class="col-xl-6 col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Top 5 Sản Phẩm Bán Chạy</h6>
                </div>
                <div class="card-body">
                    @if($topProducts->count() > 0)
                        @foreach($topProducts as $index => $product)
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div class="d-flex align-items-center">
                                    <span class="badge bg-primary me-2">#{{ $index + 1 }}</span>
                                    <div>
                                        <strong>{{ $product->name }}</strong><br>
                                        <small class="text-muted">Đã bán: {{ $product->total_sold }}</small>
                                    </div>
                                </div>
                                <span class="text-success fw-bold">{{ number_format($product->total_revenue, 0, ',', '.') }} $</span>
                            </div>
                        @endforeach
                    @else
                        <p class="text-muted">Chưa có dữ liệu bán hàng.</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-xl-6 col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Top 5 Khách Hàng Chi Tiêu</h6>
                </div>
                <div class="card-body">
                    @if($topCustomers->count() > 0)
                        @foreach($topCustomers as $index => $customer)
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div class="d-flex align-items-center">
                                    <span class="badge bg-success me-2">#{{ $index + 1 }}</span>
                                    <div>
                                        <strong>{{ $customer->name }}</strong><br>
                                        <small class="text-muted">{{ $customer->orders_count }} đơn hàng</small>
                                    </div>
                                </div>
                                <span class="text-success fw-bold">{{ number_format($customer->total_spent, 0, ',', '.') }} $</span>
                            </div>
                        @endforeach
                    @else
                        <p class="text-muted">Chưa có dữ liệu khách hàng.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Đơn hàng gần đây và user mới -->
    <div class="row mb-4">
        <div class="col-xl-6 col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Đơn Hàng Gần Đây</h6>
                </div>
                <div class="card-body">
                    @if($recentOrders->count() > 0)
                        @foreach($recentOrders as $order)
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div>
                                    <strong>#{{ $order->id }}</strong><br>
                                    <small class="text-muted">{{ optional($order->user)->name ?? 'Khách hàng (N/A)' }}</small>
                                </div>
                                <div class="text-end">
                                    <span class="fw-bold">{{ number_format($order->total_price, 0, ',', '.') }} $</span><br>
                                    <small class="text-muted">{{ $order->created_at->format('d/m/Y H:i') }}</small>
                                </div>
                            </div>
                        @endforeach
                        <a href="{{ route('admin.orders.index') }}" class="btn btn-sm btn-primary mt-2">
                            Xem Tất Cả Đơn Hàng
                        </a>
                    @else
                        <p class="text-muted">Chưa có đơn hàng nào.</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-xl-6 col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Người Dùng Mới Đăng Ký</h6>
                </div>
                <div class="card-body">
                    @if($recentUsers->count() > 0)
                        @foreach($recentUsers as $user)
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div>
                                    <strong>{{ $user->name }}</strong><br>
                                    <small class="text-muted">{{ $user->email }}</small>
                                </div>
                                <div class="text-end">
                                    <span class="badge bg-primary">{{ $user->role }}</span><br>
                                    <small class="text-muted">{{ $user->created_at->format('d/m/Y') }}</small>
                                </div>
                            </div>
                        @endforeach
                        <a href="{{ route('admin.users.index') }}" class="btn btn-sm btn-primary mt-2">
                            Xem Tất Cả Người Dùng
                        </a>
                    @else
                        <p class="text-muted">Chưa có người dùng nào.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Thao Tác Nhanh</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-2">
                            <a href="{{ route('admin.products.create') }}" class="btn btn-success w-100">
                                <i class="fas fa-plus"></i> Thêm Sản Phẩm
                            </a>
                        </div>
                        <div class="col-md-3 mb-2">
                            <a href="{{ route('admin.categories.create') }}" class="btn btn-info w-100">
                                <i class="fas fa-tags"></i> Thêm Danh Mục
                            </a>
                        </div>
                        <div class="col-md-3 mb-2">
                            <a href="{{ route('admin.orders.index') }}" class="btn btn-warning w-100">
                                <i class="fas fa-shopping-bag"></i> Xử Lý Đơn Hàng
                            </a>
                        </div>
                        <div class="col-md-3 mb-2">
                            <a href="{{ route('admin.system.overview') }}" class="btn btn-secondary w-100">
                                <i class="fas fa-server"></i> Quản Lý Hệ Thống
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
