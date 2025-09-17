@extends('layouts.admin')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0"><i class="fas fa-user"></i> Thông Tin Người Dùng: {{ $user->name }}</h4>
                    <div>
                        <a href="{{ route('admin.users.edit', $user->id) }}" class="btn btn-primary">
                            <i class="fas fa-edit"></i> Chỉnh Sửa
                        </a>
                        <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Quay Lại
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Thông tin cơ bản -->
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5><i class="fas fa-info-circle"></i> Thông Tin Cơ Bản</h5>
                                </div>
                                <div class="card-body">
                                    <table class="table table-borderless">
                                        <tbody>
                                            <tr>
                                                <th width="150px" class="text-muted">Mã Số:</th>
                                                <td>{{ $user->id }}</td>
                                            </tr>
                                            <tr>
                                                <th class="text-muted">Họ Tên:</th>
                                                <td><strong>{{ $user->name }}</strong></td>
                                            </tr>
                                            <tr>
                                                <th class="text-muted">Email:</th>
                                                <td>{{ $user->email }}</td>
                                            </tr>
                                            <tr>
                                                <th class="text-muted">Vai Trò:</th>
                                                <td>
                                                    @if($user->isAdmin())
                                                        <span class="badge bg-danger">Admin</span>
                                                    @else
                                                        <span class="badge bg-primary">User</span>
                                                    @endif
                                                </td>
                                            </tr>
                                            <tr>
                                                <th class="text-muted">Số Điện Thoại:</th>
                                                <td>{{ $user->phone ?? 'Chưa cập nhật' }}</td>
                                            </tr>
                                            <tr>
                                                <th class="text-muted">Địa Chỉ:</th>
                                                <td>{{ $user->address ?? 'Chưa cập nhật' }}</td>
                                            </tr>
                                            <tr>
                                                <th class="text-muted">Ngày Tạo:</th>
                                                <td>{{ $user->created_at->format('d/m/Y \lúc H:i') }}</td>
                                            </tr>
                                            <tr>
                                                <th class="text-muted">Cập Nhật Cuối:</th>
                                                <td>{{ $user->updated_at->format('d/m/Y \lúc H:i') }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Thống kê đơn hàng -->
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5><i class="fas fa-chart-bar"></i> Thống Kê Đơn Hàng</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row text-center">
                                        <div class="col-6">
                                            <div class="border rounded p-3">
                                                <h3 class="text-primary">{{ $totalOrders }}</h3>
                                                <small class="text-muted">Tổng Đơn Hàng</small>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="border rounded p-3">
                                                <h3 class="text-success">{{ number_format($totalSpent, 0, ',', '.') }} $</h3>
                                                <small class="text-muted">Tổng Chi Tiêu</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row text-center mt-3">
                                        <div class="col-6">
                                            <div class="border rounded p-3">
                                                <h3 class="text-info">{{ $completedOrders }}</h3>
                                                <small class="text-muted">Đơn Hoàn Thành</small>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="border rounded p-3">
                                                <h3 class="text-warning">{{ $totalOrders - $completedOrders }}</h3>
                                                <small class="text-muted">Đơn Đang Xử Lý</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Lịch sử đơn hàng -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5><i class="fas fa-history"></i> Lịch Sử Đơn Hàng</h5>
                                </div>
                                <div class="card-body">
                                    @if($user->orders->count() > 0)
                                        <div class="table-responsive">
                                            <table class="table table-striped table-hover">
                                                <thead class="table-dark">
                                                    <tr>
                                                        <th>Mã Đơn</th>
                                                        <th>Ngày Đặt</th>
                                                        <th>Sản Phẩm</th>
                                                        <th>Tổng Tiền</th>
                                                        <th>Trạng Thái</th>
                                                        <th>Thao Tác</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($user->orders as $order)
                                                        <tr>
                                                            <td><strong>#{{ $order->id }}</strong></td>
                                                            <td>{{ $order->created_at->format('d/m/Y H:i') }}</td>
                                                            <td>
                                                                @foreach($order->orderItems->take(2) as $item)
                                                                    <div class="d-flex align-items-center mb-1">
                                                                        <img src="{{ $item->product->image_url }}" 
                                                                             alt="{{ $item->product->name }}" 
                                                                             class="me-2" 
                                                                             style="width: 30px; height: 20px; object-fit: cover;">
                                                                        <small>{{ $item->product->name }} (x{{ $item->quantity }})</small>
                                                                    </div>
                                                                @endforeach
                                                                @if($order->orderItems->count() > 2)
                                                                    <small class="text-muted">+{{ $order->orderItems->count() - 2 }} sản phẩm khác</small>
                                                                @endif
                                                            </td>
                                                            <td>
                                                                <span class="fw-bold text-success">
                                                                    {{ number_format($order->total_price, 0, ',', '.') }} $
                                                                </span>
                                                            </td>
                                                            <td>
                                                                @php
                                                                    $statusColors = [
                                                                        'pending' => 'warning',
                                                                        'processing' => 'info',
                                                                        'completed' => 'success',
                                                                        'cancelled' => 'danger'
                                                                    ];
                                                                    $statusLabels = [
                                                                        'pending' => 'Chờ xử lý',
                                                                        'processing' => 'Đang xử lý',
                                                                        'completed' => 'Hoàn thành',
                                                                        'cancelled' => 'Đã hủy'
                                                                    ];
                                                                @endphp
                                                                <span class="badge bg-{{ $statusColors[$order->status] ?? 'secondary' }}">
                                                                    {{ $statusLabels[$order->status] ?? $order->status }}
                                                                </span>
                                                            </td>
                                                            <td>
                                                                <a href="{{ route('admin.orders.show', $order->id) }}" class="btn btn-info btn-sm" title="Xem Chi Tiết">
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
                                            <i class="fas fa-info-circle"></i> Người dùng này chưa có đơn hàng nào.
                                        </div>
                                    @endif
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
