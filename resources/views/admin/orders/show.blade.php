@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h3 class="mb-0"><i class="fas fa-eye"></i> Chi Tiết Đơn Hàng #{{ $order->id }}</h3>
                        <small class="text-muted">Xem thông tin chi tiết đơn hàng</small>
                    </div>
                    <div>
                        <a href="{{ route('admin.orders.edit', $order->id) }}" class="btn btn-warning">
                            <i class="fas fa-edit"></i> Chỉnh Sửa
                        </a>
                        <a href="{{ route('admin.orders.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Quay Lại
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

                    <!-- Thông tin đơn hàng -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5><i class="fas fa-info-circle"></i> Thông Tin Đơn Hàng</h5>
                                </div>
                                <div class="card-body">
                                    <table class="table table-borderless">
                                        <tr>
                                            <td><strong>Mã Đơn Hàng:</strong></td>
                                            <td>#{{ $order->id }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Ngày Đặt:</strong></td>
                                            <td>{{ $order->created_at->format('d/m/Y H:i') }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Cập Nhật Cuối:</strong></td>
                                            <td>{{ $order->updated_at->format('d/m/Y H:i') }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Tổng Tiền:</strong></td>
                                            <td>
                                                <span class="h5 text-success">
                                                    ${{ number_format($order->total_price, 0, ',', '.') }}
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>Trạng Thái:</strong></td>
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
                                                <span class="badge bg-{{ $statusColors[$order->status] ?? 'secondary' }} fs-6">
                                                    {{ $statusLabels[$order->status] ?? $order->status }}
                                                </span>
                                            </td>
                                        </tr>
                                    </table>
                                    
                                    <!-- Form cập nhật trạng thái -->
                                    <div class="mt-3">
                                        <h6>Cập Nhật Trạng Thái:</h6>
                                        <form action="{{ route('admin.orders.update-status', $order->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('PATCH')
                                            <div class="input-group">
                                                <select class="form-select" name="status" required>
                                                    <option value="pending" {{ $order->status == 'pending' ? 'selected' : '' }}>Chờ xử lý</option>
                                                    <option value="processing" {{ $order->status == 'processing' ? 'selected' : '' }}>Đang xử lý</option>
                                                    <option value="completed" {{ $order->status == 'completed' ? 'selected' : '' }}>Hoàn thành</option>
                                                    <option value="cancelled" {{ $order->status == 'cancelled' ? 'selected' : '' }}>Đã hủy</option>
                                                </select>
                                                <button type="submit" class="btn btn-primary">
                                                    <i class="fas fa-sync-alt"></i> Cập Nhật
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5><i class="fas fa-user"></i> Thông Tin Khách Hàng</h5>
                                </div>
                                <div class="card-body">
                                    <table class="table table-borderless">
                                        <tr>
                                            <td><strong>Tên:</strong></td>
                                            <td>{{ $order->user->name }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Email:</strong></td>
                                            <td>{{ $order->user->email }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Số Điện Thoại:</strong></td>
                                            <td>{{ $order->user->phone ?? 'Chưa cập nhật' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Ngày Đăng Ký:</strong></td>
                                            <td>{{ $order->user->created_at->format('d/m/Y') }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Tổng Đơn Hàng:</strong></td>
                                            <td>
                                                <span class="badge bg-info">
                                                    {{ $order->user->orders->count() }} đơn hàng
                                                </span>
                                            </td>
                                        </tr>
                                    </table>
                                    
                                    <div class="mt-3">
                                        <a href="{{ route('admin.users.show', $order->user->id) }}" class="btn btn-outline-primary btn-sm">
                                            <i class="fas fa-user"></i> Xem Hồ Sơ Khách Hàng
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Chi tiết sản phẩm -->
                    <div class="card">
                        <div class="card-header">
                            <h5><i class="fas fa-box"></i> Chi Tiết Sản Phẩm ({{ $order->orderItems->count() }} sản phẩm)</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>Sản Phẩm</th>
                                            <th>Hình Ảnh</th>
                                            <th>Danh Mục</th>
                                            <th class="text-center">Số Lượng</th>
                                            <th class="text-end">Giá</th>
                                            <th class="text-end">Thành Tiền</th>
                                            <th>Thao Tác</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($order->orderItems as $item)
                                            <tr>
                                                <td>
                                                    <div>
                                                        <h6 class="mb-1">{{ $item->product->name }}</h6>
                                                        <small class="text-muted">ID: {{ $item->product->id }}</small>
                                                    </div>
                                                </td>
                                                <td>
                                                    <img src="{{ $item->product->image_url }}" 
                                                         alt="{{ $item->product->name }}" 
                                                         class="img-thumbnail" 
                                                         style="width: 60px; height: 45px; object-fit: cover;">
                                                </td>
                                                <td>
                                                    <span class="badge bg-secondary">
                                                        {{ $item->product->category->name }}
                                                    </span>
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge bg-primary fs-6">{{ $item->quantity }}</span>
                                                </td>
                                                <td class="text-end">
                                                    ${{ number_format($item->price, 0, ',', '.') }}
                                                </td>
                                                <td class="text-end">
                                                    <strong class="text-success">
                                                        ${{ number_format($item->quantity * $item->price, 0, ',', '.') }}
                                                    </strong>
                                                </td>
                                                <td>
                                                    <a href="{{ route('products.show', $item->product->id) }}" 
                                                       class="btn btn-sm btn-outline-info" 
                                                       target="_blank">
                                                        <i class="fas fa-external-link-alt"></i> Xem
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr class="table-active">
                                            <th colspan="4">Tổng Cộng:</th>
                                            <th class="text-end">{{ $order->orderItems->sum('quantity') }} sản phẩm</th>
                                            <th class="text-end">
                                                <h5 class="text-success mb-0">
                                                    ${{ number_format($order->total_price, 0, ',', '.') }}
                                                </h5>
                                            </th>
                                            <th></th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Thao tác -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <a href="{{ route('admin.orders.index') }}" class="btn btn-secondary">
                                        <i class="fas fa-arrow-left"></i> Quay Lại Danh Sách
                                    </a>
                                </div>
                                <div>
                                    <a href="{{ route('admin.orders.edit', $order->id) }}" class="btn btn-warning me-2">
                                        <i class="fas fa-edit"></i> Chỉnh Sửa Đơn Hàng
                                    </a>
                                    <form action="{{ route('admin.orders.destroy', $order->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger" 
                                                onclick="return confirm('Bạn có chắc chắn muốn xóa đơn hàng này? Hành động này không thể hoàn tác!')">
                                            <i class="fas fa-trash"></i> Xóa Đơn Hàng
                                        </button>
                                    </form>
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

@push('styles')
<style>
.card {
    border: none;
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    margin-bottom: 1.5rem;
}

.card-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
    font-weight: 500;
}

.table-borderless td {
    border: none;
    padding: 0.5rem 0.75rem;
}

.badge {
    font-size: 0.875rem;
}

.img-thumbnail {
    border: 1px solid #dee2e6;
}

.btn-group .btn {
    margin-right: 0.25rem;
}

.alert {
    border-radius: 0.375rem;
}
</style>
@endpush
