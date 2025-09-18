@extends('layouts.app')

@section('content')
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4>Đơn hàng #{{ $order->id }}</h4>
            <a href="{{ route('user.orders.index') }}" class="btn btn-light">← Quay lại</a>
        </div>

        <div class="mb-3">
            <span class="me-3">Ngày đặt: <strong>{{ $order->created_at->format('d/m/Y H:i') }}</strong></span>
            <span class="me-3">Trạng thái:
                <span class="badge bg-{{ $order->status === 'completed' ? 'success' : 'secondary' }}">
                    {{ ucfirst($order->status) }}
                </span>
            </span>
            @if (isset($order->total_amount))
                <span>Tổng tiền: <strong>{{ number_format($order->total_amount, 0, ',', '.') }}₫</strong></span>
            @endif
        </div>

        @if ($order->items->isEmpty())
            <div class="alert alert-info">Đơn hàng này chưa có sản phẩm nào.</div>
        @else
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Sản phẩm</th>
                            <th>Số lượng</th>
                            <th>Giá</th>
                            <th class="text-end">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($order->items as $item)
                            @php
                                $product = $item->product; // do đã eager load
                                $isCompleted = $order->status === 'completed'; // đổi theo trạng thái của bạn
                                $hasReviewed = isset($reviewedPairs[$item->product_id]); // dùng helper tránh N+1
                            @endphp
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        @if (!empty($product?->thumbnail_url))
                                            <img src="{{ $product->thumbnail_url }}" alt=""
                                                style="width:60px;height:60px;object-fit:cover" class="rounded border">
                                        @endif
                                        <div>
                                            <div class="fw-semibold">{{ $product?->name ?? 'Sản phẩm #' . $item->product_id }}
                                            </div>
                                            <div class="text-muted small">Mã: {{ $product?->sku ?? $item->product_id }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $item->quantity }}</td>
                                <td>{{ number_format($item->price ?? 0, 0, ',', '.') }}₫</td>
                                <td class="text-end">
                                    @if ($isCompleted)
                                        @if (!$hasReviewed)
                                            {{-- Link tới trang sản phẩm + truyền order_id để prefill form review --}}
                                            <a href="{{ route('products.show', $item->product_id) }}?order_id={{ $order->id }}"
                                                class="btn btn-sm btn-outline-primary" title="Đánh giá sản phẩm này">
                                                Đánh giá
                                            </a>
                                        @else
                                            <span class="badge bg-success">Đã đánh giá</span>
                                        @endif
                                    @else
                                        <span class="badge bg-secondary">Chưa hoàn tất</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
@endsection
