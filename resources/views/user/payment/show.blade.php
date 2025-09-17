@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card shadow-lg border-0">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">
                        <i class="fas fa-receipt me-2"></i>
                        Chi tiết đơn hàng #{{ $order->id }}
                    </h4>
                </div>
                <div class="card-body">
                    <!-- Thông tin đơn hàng -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h5 class="text-primary mb-3">Thông tin đơn hàng</h5>
                            <div class="mb-2">
                                <strong>Mã đơn hàng:</strong> #{{ $order->id }}
                            </div>

                    <!-- Timeline trạng thái đơn hàng (dễ nhìn cho khách) -->
                    @php
                        $steps = $order->stage_steps;
                        $currentIndex = $order->stage_index;
                        $keys = array_keys($steps);
                    @endphp
                    <div class="mb-4">
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
                            <div class="mb-2">
                                <strong>Ngày đặt:</strong> {{ $order->created_at->format('d/m/Y H:i') }}
                            </div>
                            <div class="mb-2">
                                <strong>Trạng thái:</strong> 
                                <span class="badge bg-{{ $order->stage === 'completed' ? 'success' : ($order->stage === 'cancelled' ? 'danger' : ($order->stage === 'deposited' ? 'primary' : 'warning')) }}">
                                    {{ $order->status }}
                                </span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h5 class="text-primary mb-3">Thông tin khách hàng</h5>
                            <div class="mb-2">
                                <strong>Họ tên:</strong> {{ $order->name }}
                            </div>
                            <div class="mb-2">
                                <strong>Địa chỉ:</strong> {{ $order->address }}
                            </div>
                            <div class="mb-2">
                                <strong>Số điện thoại:</strong> {{ $order->phone }}
                            </div>
                        </div>
                    </div>

                    <!-- Tổng quan thanh toán -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <div class="row text-center">
                                        <div class="col-md-4">
                                            <h6 class="text-muted">Tổng tiền</h6>
                                            <h4 class="text-primary">{{ number_format($order->total_price, 0, ',', '.') }} $</h4>
                                        </div>
                                        <div class="col-md-4">
                                            <h6 class="text-muted">Tiền cọc (30%)</h6>
                                            <h4 class="text-success">{{ number_format($order->deposit_amount, 0, ',', '.') }} $</h4>
                                        </div>
                                        <div class="col-md-4">
                                            <h6 class="text-muted">Còn lại</h6>
                                            <h4 class="text-info">{{ number_format($order->total_price - $order->deposit_amount, 0, ',', '.') }} $</h4>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Danh sách sản phẩm -->
                    <div class="mb-4">
                        <h5 class="text-primary mb-3">Sản phẩm đã đặt</h5>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Sản phẩm</th>
                                        <th>Giá</th>
                                        <th>Số lượng</th>
                                        <th>Thành tiền</th>
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
                                                     style="width: 50px; height: 50px; object-fit: cover;"
                                                     onerror="this.src='{{ asset('images/default-car.svg') }}'">
                                                <div>
                                                    <strong>{{ $item->product->name }}</strong>
                                                    <br>
                                                    <small class="text-muted">{{ $item->product->category->name }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>{{ number_format($item->price, 0, ',', '.') }} $</td>
                                        <td>{{ $item->quantity }}</td>
                                        <td>{{ number_format($item->price * $item->quantity, 0, ',', '.') }} $</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Nút thanh toán lại nếu cần -->
                    @if($order->status === 'chờ đặt cọc' || $order->status === 'thanh toán MoMo không thành công')
                    <div class="text-center">
                        <a href="{{ route('user.orders.momo.pay', $order) }}" class="btn btn-primary btn-lg">
                            <i class="fas fa-credit-card me-2"></i>
                            Thanh toán MoMo
                        </a>
                    </div>
                    @endif

                    <!-- Nút quay lại -->
                    <div class="text-center mt-4">
                        <a href="{{ route('user.orders.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-2"></i>
                            Quay lại danh sách đơn hàng
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
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
