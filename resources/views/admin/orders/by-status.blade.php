@extends('layouts.app')

@section('title', 'Đơn Hàng Theo Trạng Thái - Admin')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">
                    <i class="fas fa-filter"></i> 
                    Đơn Hàng: {{ $statusLabels[$status] ?? 'Tất Cả' }}
                </h1>
                <div>
                    <a href="{{ route('admin.orders.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Quay Lại
                    </a>
                    <a href="{{ route('admin.orders.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Tạo Đơn Hàng
                    </a>
                </div>
            </div>

            <!-- Thống kê nhanh -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card bg-primary text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h6 class="card-title">Tổng Đơn Hàng</h6>
                                    <h3 class="mb-0">{{ $orders->total() }}</h3>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-shopping-bag fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-success text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h6 class="card-title">Đã Hoàn Thành</h6>
                                    <h3 class="mb-0">{{ $orders->where('status', 'completed')->count() }}</h3>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-check-circle fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-warning text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h6 class="card-title">Đang Xử Lý</h6>
                                    <h3 class="mb-0">{{ $orders->where('status', 'processing')->count() }}</h3>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-clock fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-danger text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h6 class="card-title">Đã Hủy</h6>
                                    <h3 class="mb-0">{{ $orders->where('status', 'cancelled')->count() }}</h3>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-times-circle fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bộ lọc -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" action="{{ route('admin.orders.by-status', $status) }}" class="row g-3">
                        <div class="col-md-3">
                            <label for="search" class="form-label">Tìm Kiếm</label>
                            <input type="text" class="form-control" id="search" name="search" 
                                   value="{{ request('search') }}" placeholder="Mã đơn hàng, tên khách hàng...">
                        </div>
                        <div class="col-md-2">
                            <label for="date_from" class="form-label">Từ Ngày</label>
                            <input type="date" class="form-control" id="date_from" name="date_from" 
                                   value="{{ request('date_from') }}">
                        </div>
                        <div class="col-md-2">
                            <label for="date_to" class="form-label">Đến Ngày</label>
                            <input type="date" class="form-control" id="date_to" name="date_to" 
                                   value="{{ request('date_to') }}">
                        </div>
                        <div class="col-md-2">
                            <label for="min_amount" class="form-label">Giá Tối Thiểu</label>
                            <input type="number" class="form-control" id="min_amount" name="min_amount" 
                                   value="{{ request('min_amount') }}" placeholder="0">
                        </div>
                        <div class="col-md-2">
                            <label for="max_amount" class="form-label">Giá Tối Đa</label>
                            <input type="number" class="form-control" id="max_amount" name="max_amount" 
                                   value="{{ request('max_amount') }}" placeholder="999999999">
                        </div>
                        <div class="col-md-1">
                            <label class="form-label">&nbsp;</label>
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Danh sách đơn hàng -->
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Danh Sách Đơn Hàng</h5>
                        <div class="btn-group" role="group">
                            <a href="{{ route('admin.orders.by-status', 'pending') }}" 
                               class="btn btn-outline-warning {{ $status == 'pending' ? 'active' : '' }}">
                                Chờ Xử Lý
                            </a>
                            <a href="{{ route('admin.orders.by-status', 'processing') }}" 
                               class="btn btn-outline-info {{ $status == 'processing' ? 'active' : '' }}">
                                Đang Xử Lý
                            </a>
                            <a href="{{ route('admin.orders.by-status', 'completed') }}" 
                               class="btn btn-outline-success {{ $status == 'completed' ? 'active' : '' }}">
                                Hoàn Thành
                            </a>
                            <a href="{{ route('admin.orders.by-status', 'cancelled') }}" 
                               class="btn btn-outline-danger {{ $status == 'cancelled' ? 'active' : '' }}">
                                Đã Hủy
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    @if($orders->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Mã Đơn</th>
                                        <th>Khách Hàng</th>
                                        <th>Sản Phẩm</th>
                                        <th>Tổng Tiền</th>
                                        <th>Trạng Thái</th>
                                        <th>Ngày Tạo</th>
                                        <th>Thao Tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($orders as $order)
                                    <tr>
                                        <td>
                                            <strong>#{{ $order->id }}</strong>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-sm me-2">
                                                    <i class="fas fa-user-circle fa-2x text-primary"></i>
                                                </div>
                                                <div>
                                                    <div class="fw-bold">{{ optional($order->user)->name ?? 'Khách hàng (N/A)' }}</div>
                                                    <small class="text-muted">{{ optional($order->user)->email ?? 'N/A' }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                @if($order->orderItems->count() > 0)
                                                    <div class="me-2">
                                                        <img src="{{ asset('storage/' . $order->orderItems->first()->product->image) }}" 
                                                             alt="{{ $order->orderItems->first()->product->name }}"
                                                             class="img-thumbnail" style="width: 40px; height: 40px;">
                                                    </div>
                                                    <div>
                                                        <div class="fw-bold">{{ $order->orderItems->first()->product->name }}</div>
                                                        <small class="text-muted">
                                                            {{ $order->orderItems->count() }} sản phẩm
                                                        </small>
                                                    </div>
                                                @else
                                                    <span class="text-muted">Không có sản phẩm</span>
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-success fs-6">
                                                {{ number_format($order->total_amount) }} $
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge {{ $order->status_color }} fs-6">
                                                {{ $order->status_label }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="text-muted">
                                                <div>{{ $order->created_at->format('d/m/Y') }}</div>
                                                <small>{{ $order->created_at->format('H:i') }}</small>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('admin.orders.show', $order) }}" 
                                                   class="btn btn-sm btn-outline-primary" title="Xem Chi Tiết">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('admin.orders.edit', $order) }}" 
                                                   class="btn btn-sm btn-outline-warning" title="Chỉnh Sửa">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <button type="button" class="btn btn-sm btn-outline-info" 
                                                        title="Cập Nhật Trạng Thái"
                                                        onclick="updateStatus({{ $order->id }})">
                                                    <i class="fas fa-sync-alt"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-danger" 
                                                        title="Xóa"
                                                        onclick="deleteOrder({{ $order->id }})">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Phân trang -->
                        <div class="d-flex justify-content-center mt-4">
                            {{ $orders->appends(request()->query())->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">Không Có Đơn Hàng Nào</h5>
                            <p class="text-muted">Không tìm thấy đơn hàng nào với trạng thái này.</p>
                            <a href="{{ route('admin.orders.index') }}" class="btn btn-primary">
                                <i class="fas fa-arrow-left"></i> Quay Lại Danh Sách
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal cập nhật trạng thái -->
<div class="modal fade" id="updateStatusModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Cập Nhật Trạng Thái Đơn Hàng</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="updateStatusForm" method="POST">
                @csrf
                @method('PATCH')
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="status" class="form-label">Trạng Thái</label>
                        <select class="form-select" id="status" name="status" required>
                            <option value="pending">Chờ Xử Lý</option>
                            <option value="processing">Đang Xử Lý</option>
                            <option value="completed">Hoàn Thành</option>
                            <option value="cancelled">Đã Hủy</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="notes" class="form-label">Ghi Chú</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3" 
                                  placeholder="Ghi chú về việc thay đổi trạng thái..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary">Cập Nhật</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal xác nhận xóa -->
<div class="modal fade" id="deleteOrderModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Xác Nhận Xóa</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Bạn có chắc chắn muốn xóa đơn hàng này? Hành động này không thể hoàn tác.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                <form id="deleteOrderForm" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Xóa</button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
function updateStatus(orderId) {
    const form = document.getElementById('updateStatusForm');
    form.action = `/admin/orders/${orderId}/status`;
    
    const modal = new bootstrap.Modal(document.getElementById('updateStatusModal'));
    modal.show();
}

function deleteOrder(orderId) {
    const form = document.getElementById('deleteOrderForm');
    form.action = `/admin/orders/${orderId}`;
    
    const modal = new bootstrap.Modal(document.getElementById('deleteOrderModal'));
    modal.show();
}

// Tự động submit form khi chọn ngày
document.getElementById('date_from').addEventListener('change', function() {
    this.form.submit();
});

document.getElementById('date_to').addEventListener('change', function() {
    this.form.submit();
});

// Tự động submit form khi nhập giá
let searchTimeout;
document.getElementById('min_amount').addEventListener('input', function() {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        this.form.submit();
    }, 1000);
});

document.getElementById('max_amount').addEventListener('input', function() {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        this.form.submit();
    }, 1000);
});
</script>
@endpush

@push('styles')
<style>
.avatar-sm {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.btn-group .btn {
    margin-right: 2px;
}

.btn-group .btn:last-child {
    margin-right: 0;
}

.table th {
    white-space: nowrap;
}

.badge {
    font-size: 0.875rem !important;
}

.img-thumbnail {
    object-fit: cover;
}
</style>
@endpush
