@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row">
        <div class="col-12">
            <h1 class="mb-4">
                <i class="fas fa-history text-primary"></i> Lịch sử đơn hàng
            </h1>

            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle"></i> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle"></i> {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if($orders->count() > 0)
                <div class="row">
                    @foreach($orders as $order)
                        <div class="col-12 mb-4">
                            <div class="card shadow-sm">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <div>
                                        <h5 class="mb-0">
                                            <i class="fas fa-shopping-bag"></i> Đơn hàng #{{ $order->id }}
                                        </h5>
                                        <small class="text-muted">
                                            Đặt lúc: {{ $order->created_at->format('d/m/Y H:i') }}
                                        </small>
                                    </div>
                                    <div class="text-end">
                                        <span class="badge {{ $order->status === 'đã đặt cọc (MoMo)' || $order->status === 'đã đặt cọc (COD)' ? 'bg-success' : 'bg-warning' }} fs-6">
                                            {{ $order->status }}
                                        </span>
                                        <div class="mt-1">
                                            <strong class="text-primary">{{ number_format($order->total_price, 0, ',', '.') }} $</strong>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <!-- Timeline trạng thái đơn hàng -->
                                    @php
                                        $steps = $order->stage_steps;
                                        $currentIndex = $order->stage_index;
                                        $keys = array_keys($steps);
                                    @endphp
                                    <div class="mb-3">
                                        <div class="order-timeline">
                                            @foreach($steps as $key => $label)
                                                @php $index = array_search($key, $keys); @endphp
                                                <div class="timeline-step {{ $index <= $currentIndex ? 'completed' : '' }} {{ $key === 'cancelled' && $order->stage === 'cancelled' ? 'cancelled' : '' }}">
                                                    <div class="dot"></div>
                                                    <div class="label">{{ $label }}</div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                    <!-- Thông tin giao hàng -->
                                    <div class="row mb-3">
                                        <div class="col-md-4">
                                            <strong><i class="fas fa-user"></i> Người nhận:</strong>
                                            <div>{{ $order->name }}</div>
                                        </div>
                                        <div class="col-md-4">
                                            <strong><i class="fas fa-phone"></i> Số điện thoại:</strong>
                                            <div>{{ $order->phone }}</div>
                                        </div>
                                        <div class="col-md-4">
                                            <strong><i class="fas fa-map-marker-alt"></i> Địa chỉ:</strong>
                                            <div>{{ $order->address }}</div>
                                        </div>
                                    </div>

                                    <!-- Danh sách sản phẩm -->
                                    <h6 class="mb-3"><i class="fas fa-list"></i> Sản phẩm đã đặt:</h6>
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Sản phẩm</th>
                                                    <th class="text-center">Số lượng</th>
                                                    <th class="text-end">Đơn giá</th>
                                                    <th class="text-end">Thành tiền</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($order->items as $item)
                                                    <tr>
                                                        <td>
                                                            <div class="d-flex align-items-center">
                                                                <img src="{{ $item->product->image ? asset('storage/' . $item->product->image) : asset('images/default-car.svg') }}" 
                                                                     alt="{{ $item->product->name }}" 
                                                                     class="me-3"
                                                                     style="width: 50px; height: 40px; object-fit: cover; border-radius: 5px;"
                                                                     onerror="this.src='{{ asset('images/default-car.svg') }}'">
                                                                <div>
                                                                    <div class="fw-bold">{{ $item->product->name }}</div>
                                                                    <small class="text-muted">{{ $item->product->category->name }}</small>
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td class="text-center">{{ $item->quantity }}</td>
                                                        <td class="text-end">{{ number_format($item->price, 0, ',', '.') }} $</td>
                                                        <td class="text-end fw-bold">{{ number_format($item->price * $item->quantity, 0, ',', '.') }} $</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>

                                    <!-- Hành động -->
                                    <div class="d-flex justify-content-between align-items-center mt-3">
                                        <div>
                                            <a href="{{ route('user.orders.show', $order->id) }}" class="btn btn-outline-primary btn-sm">
                                                <i class="fas fa-eye"></i> Xem chi tiết
                                            </a>
                                        </div>
                                        <div>
                                            @if($order->status === 'chờ đặt cọc' || $order->status === 'thanh toán MoMo không thành công')
                                                <a href="{{ route('user.orders.momo.pay', $order->id) }}" class="btn btn-success btn-sm">
                                                    <i class="fas fa-credit-card"></i> Thanh toán MoMo
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-shopping-bag fa-4x text-muted mb-3"></i>
                    <h3 class="text-muted">Bạn chưa có đơn hàng nào</h3>
                    <p class="text-muted">Hãy mua sắm để tạo đơn hàng đầu tiên</p>
                    <a href="{{ route('categories.index') }}" class="btn btn-primary btn-lg">
                        <i class="fas fa-shopping-cart"></i> Mua sắm ngay
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>

<style>
.card {
    border: none;
    border-radius: 15px;
}

.card-header {
    border-radius: 15px 15px 0 0 !important;
    border: none;
    background-color: #f8f9fa;
}

.badge {
    border-radius: 8px;
}

.table th {
    border-top: none;
    font-weight: 600;
    color: #495057;
}

.btn {
    border-radius: 8px;
}

.alert {
    border-radius: 10px;
    border: none;
}
</style>
<style>
.order-timeline {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 12px;
    align-items: center;
}
.order-timeline .timeline-step {
    display: flex;
    align-items: center;
    gap: 8px;
    color: #6c757d;
}
.order-timeline .timeline-step .dot {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    background: #dee2e6;
}
.order-timeline .timeline-step.completed {
    color: #198754;
    font-weight: 600;
}
.order-timeline .timeline-step.completed .dot {
    background: #198754;
}
.order-timeline .timeline-step.cancelled {
    color: #dc3545;
}
.order-timeline .timeline-step.cancelled .dot {
    background: #dc3545;
}
</style>
@endsection
