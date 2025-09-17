@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h3 class="mb-0"><i class="fas fa-shopping-bag"></i> Quản Lý Đơn Hàng</h3>
                        <small class="text-muted">Quản lý và xử lý tất cả đơn hàng trong hệ thống</small>
                    </div>
                    <div>
                        <a href="{{ route('admin.orders.statistics') }}" class="btn btn-info">
                            <i class="fas fa-chart-bar"></i> Thống Kê
                        </a>
                        <a href="{{ route('admin.orders.search') }}" class="btn btn-secondary">
                            <i class="fas fa-search"></i> Tìm Kiếm
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle"></i> {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <!-- Thống kê nhanh -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body text-center">
                                    <h3>{{ $totalOrders }}</h3>
                                    <small>Tổng Đơn Hàng</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-success text-white">
                                <div class="card-body text-center">
                                    <h3>{{ $completedOrders }}</h3>
                                    <small>Đơn Hoàn Thành</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-warning text-white">
                                <div class="card-body text-center">
                                    <h3>{{ $pendingOrders }}</h3>
                                    <small>Đơn Chờ Xử Lý</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-info text-white">
                                <div class="card-body text-center">
                                    <h3>{{ number_format($totalRevenue, 0, ',', '.') }} $</h3>
                                    <small>Doanh Thu</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Bộ lọc trạng thái -->
                    <div class="row mb-3">
                        <div class="col-12">
                            <div class="btn-group" role="group">
                                <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-primary {{ request()->routeIs('admin.orders.index') ? 'active' : '' }}">
                                    Tất Cả
                                </a>
                                <a href="{{ route('admin.orders.by-status', 'pending') }}" class="btn btn-outline-warning {{ request()->is('*/status/pending') ? 'active' : '' }}">
                                    Chờ Xử Lý
                                </a>
                                <a href="{{ route('admin.orders.by-status', 'processing') }}" class="btn btn-outline-info {{ request()->is('*/status/processing') ? 'active' : '' }}">
                                    Đang Xử Lý
                                </a>
                                <a href="{{ route('admin.orders.by-status', 'completed') }}" class="btn btn-outline-success {{ request()->is('*/status/completed') ? 'active' : '' }}">
                                    Hoàn Thành
                                </a>
                                <a href="{{ route('admin.orders.by-status', 'cancelled') }}" class="btn btn-outline-danger {{ request()->is('*/status/cancelled') ? 'active' : '' }}">
                                    Đã Hủy
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>Mã Đơn</th>
                                    <th>Khách Hàng</th>
                                    <th>Sản Phẩm</th>
                                    <th>Tổng Tiền</th>
                                    <th>Trạng Thái</th>
                                    <th>Ngày Đặt</th>
                                    <th width="150px">Thao Tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($orders as $order)
                                    <tr>
                                        <td>
                                            <strong>#{{ $order->id }}</strong>
                                        </td>
                                        <td>
                                            <div>
                                                <strong>{{ $order->user->name }}</strong><br>
                                                <small class="text-muted">{{ $order->user->email }}</small>
                                                @if($order->user->phone)
                                                    <br><small class="text-muted">📱 {{ $order->user->phone }}</small>
                                                @endif
                                            </div>
                                        </td>
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
                                        <td>{{ $order->created_at->format('d/m/Y H:i') }}</td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a class="btn btn-info btn-sm" href="{{ route('admin.orders.show', $order->id) }}" title="Xem Chi Tiết">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a class="btn btn-warning btn-sm" href="{{ route('admin.orders.edit', $order->id) }}" title="Chỉnh Sửa">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <button type="button" class="btn btn-primary btn-sm" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#statusModal{{ $order->id }}" 
                                                        title="Cập Nhật Trạng Thái">
                                                    <i class="fas fa-sync-alt"></i>
                                                </button>
                                                <form action="{{ route('admin.orders.destroy', $order->id) }}" method="POST" style="display: inline;">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger btn-sm" 
                                                            onclick="return confirm('Bạn có chắc chắn muốn xóa đơn hàng này? Hành động này không thể hoàn tác!')" title="Xóa">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>

                                    <!-- Modal cập nhật trạng thái -->
                                    <div class="modal fade" id="statusModal{{ $order->id }}" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Cập Nhật Trạng Thái Đơn Hàng #{{ $order->id }}</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <form action="{{ route('admin.orders.update-status', $order->id) }}" method="POST">
                                                    @csrf
                                                    @method('PATCH')
                                                    <div class="modal-body">
                                                        <div class="mb-3">
                                                            <label for="status" class="form-label">Trạng Thái</label>
                                                            <select class="form-select" name="status" required>
                                                                <option value="pending" {{ $order->status == 'pending' ? 'selected' : '' }}>Chờ xử lý</option>
                                                                <option value="processing" {{ $order->status == 'processing' ? 'selected' : '' }}>Đang xử lý</option>
                                                                <option value="completed" {{ $order->status == 'completed' ? 'selected' : '' }}>Hoàn thành</option>
                                                                <option value="cancelled" {{ $order->status == 'cancelled' ? 'selected' : '' }}>Đã hủy</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                                                        <button type="submit" class="btn btn-primary">Cập Nhật</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center">Không tìm thấy đơn hàng nào.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-center mt-4">
                        {{ $orders->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
