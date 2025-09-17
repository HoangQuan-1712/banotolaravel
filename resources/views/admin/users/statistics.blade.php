@extends('layouts.admin')

@section('title', 'Thống kê người dùng')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title"><i class="fas fa-chart-bar"></i> Thống kê người dùng</h3>
                    <div>
                        <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">
                            <i class="fas fa-users"></i> Danh sách người dùng
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Thống kê tổng quan -->
                    <div class="row mb-4">
                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-info">
                                <div class="inner">
                                    <h3>{{ $stats['total_users'] }}</h3>
                                    <p>Tổng người dùng</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-users"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-success">
                                <div class="inner">
                                    <h3>{{ $stats['active_users'] }}</h3>
                                    <p>Người dùng hoạt động</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-user-check"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-warning">
                                <div class="inner">
                                    <h3>{{ $stats['users_with_orders'] }}</h3>
                                    <p>Có đơn hàng</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-shopping-cart"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-danger">
                                <div class="inner">
                                    <h3>{{ $stats['deleted_users'] }}</h3>
                                    <p>Đã ẩn/xóa</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-user-times"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Thống kê chi tiết -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5>Phân loại theo vai trò</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row text-center">
                                        <div class="col-6">
                                            <div class="description-block border-right">
                                                <span class="description-percentage text-success">
                                                    <i class="fas fa-user-shield"></i>
                                                </span>
                                                <h5 class="description-header">{{ $stats['admin_users'] }}</h5>
                                                <span class="description-text">ADMIN</span>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="description-block">
                                                <span class="description-percentage text-primary">
                                                    <i class="fas fa-user"></i>
                                                </span>
                                                <h5 class="description-header">{{ $stats['regular_users'] }}</h5>
                                                <span class="description-text">USER</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5>Tình trạng mua hàng</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row text-center">
                                        <div class="col-6">
                                            <div class="description-block border-right">
                                                <span class="description-percentage text-success">
                                                    <i class="fas fa-shopping-bag"></i>
                                                </span>
                                                <h5 class="description-header">{{ $stats['users_with_orders'] }}</h5>
                                                <span class="description-text">CÓ ĐƠN HÀNG</span>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="description-block">
                                                <span class="description-percentage text-warning">
                                                    <i class="fas fa-user-clock"></i>
                                                </span>
                                                <h5 class="description-header">{{ $stats['users_without_orders'] }}</h5>
                                                <span class="description-text">CHƯA MUA</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Top khách hàng -->
                    <div class="row">
                        <div class="col-md-8">
                            <div class="card">
                                <div class="card-header">
                                    <h5><i class="fas fa-trophy"></i> Top 10 Khách Hàng Chi Tiêu Cao Nhất</h5>
                                </div>
                                <div class="card-body">
                                    @if(collect($stats['top_customers'] ?? [])->count() > 0)
                                        <div class="table-responsive">
                                            <table class="table table-striped table-hover">
                                                <thead class="table-dark">
                                                    <tr>
                                                        <th>#</th>
                                                        <th>Khách Hàng</th>
                                                        <th>Số Đơn Hàng</th>
                                                        <th>Tổng Chi Tiêu</th>
                                                        <th>Thao Tác</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach(($stats['top_customers'] ?? []) as $index => $customer)
                                                        <tr>
                                                            <td>
                                                                @if($index < 3)
                                                                    <span class="badge bg-warning fs-6">#{{ $index + 1 }}</span>
                                                                @else
                                                                    <span class="text-muted">{{ $index + 1 }}</span>
                                                                @endif
                                                            </td>
                                                            <td>
                                                                <div>
                                                                    <strong>{{ $customer->name }}</strong><br>
                                                                    <small class="text-muted">{{ $customer->email }}</small>
                                                                </div>
                                                            </td>
                                                            <td>
                                                                <span class="badge bg-primary">{{ $customer->orders_count }} đơn</span>
                                                            </td>
                                                            <td>
                                                                <span class="fw-bold text-success">
                                                                    {{ number_format($customer->orders_sum_total_price, 0, ',', '.') }} $
                                                                </span>
                                                            </td>
                                                            <td>
                                                                <a href="{{ route('admin.users.show', $customer->id) }}" class="btn btn-info btn-sm" title="Xem Chi Tiết">
                                                                    <i class="fas fa-eye"></i>
                                                                </a>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @else
                                        <div class="alert alert-info text-center">
                                            <i class="fas fa-info-circle"></i> Chưa có dữ liệu khách hàng.
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-header">
                                    <h5><i class="fas fa-percentage"></i> Tỷ Lệ Người Dùng</h5>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <div class="d-flex justify-content-between mb-1">
                                            <span>Khách hàng đã mua hàng</span>
                                            <span>{{ ($stats['total_users'] ?? 0) > 0 ? round((($stats['users_with_orders'] ?? 0) / $stats['total_users']) * 100, 1) : 0 }}%</span>
                                        </div>
                                        <div class="progress">
                                            <div class="progress-bar bg-success" style="width: {{ ($stats['total_users'] ?? 0) > 0 ? (($stats['users_with_orders'] ?? 0) / $stats['total_users']) * 100 : 0 }}%"></div>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <div class="d-flex justify-content-between mb-1">
                                            <span>Người dùng mới tháng này</span>
                                            <span>{{ ($stats['total_users'] ?? 0) > 0 ? round((($stats['new_users_this_month'] ?? 0) / $stats['total_users']) * 100, 1) : 0 }}%</span>
                                        </div>
                                        <div class="progress">
                                            <div class="progress-bar bg-info" style="width: {{ ($stats['total_users'] ?? 0) > 0 ? (($stats['new_users_this_month'] ?? 0) / $stats['total_users']) * 100 : 0 }}%"></div>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <div class="d-flex justify-content-between mb-1">
                                            <span>Người dùng hoạt động</span>
                                            <span>{{ ($stats['total_users'] ?? 0) > 0 ? round((($stats['active_users'] ?? 0) / $stats['total_users']) * 100, 1) : 0 }}%</span>
                                        </div>
                                        <div class="progress">
                                            <div class="progress-bar bg-warning" style="width: {{ ($stats['total_users'] ?? 0) > 0 ? (($stats['active_users'] ?? 0) / $stats['total_users']) * 100 : 0 }}%"></div>
                                        </div>
                                    </div>

                                    <div class="alert alert-info mt-3">
                                        <i class="fas fa-lightbulb"></i>
                                        <strong>Gợi ý:</strong>
                                        <ul class="mb-0 mt-2 small">
                                            <li>Tập trung vào {{ max(0, ($stats['total_users'] ?? 0) - ($stats['users_with_orders'] ?? 0)) }} người dùng chưa mua hàng</li>
                                            <li>Khuyến khích {{ max(0, ($stats['users_with_orders'] ?? 0) - ($stats['active_users'] ?? 0)) }} khách hàng quay lại</li>
                                            <li>Chăm sóc {{ $stats['active_users'] ?? 0 }} khách hàng trung thành</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
