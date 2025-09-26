@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row">
        <div class="col-12">
            <h1 class="mb-4">
                <i class="fas fa-shopping-cart text-primary"></i> Giỏ hàng của bạn
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

            @if (count($cart) > 0)
                <div class="card shadow-sm">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th width="100">Ảnh</th>
                                        <th>Sản phẩm</th>
                                        <th width="150">Số lượng</th>
                                        <th width="120">Đơn giá</th>
                                        <th width="120">Thành tiền</th>
                                        <th width="100">Hành động</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($cart as $id => $details)
                                        <tr class="cart-item">
                                            <td>
                                                <img src="{{ $details['image'] ? asset('storage/' . $details['image']) : asset('images/default-car.svg') }}" 
                                                     alt="{{ $details['name'] }}" 
                                                     class="img-thumbnail product-image" 
                                                     style="width: 80px; height: 60px; object-fit: cover;"
                                                     onerror="this.src='{{ asset('images/default-car.svg') }}'">
                                            </td>
                                            <td>
                                                <h6 class="mb-1">{{ $details['name'] }}</h6>
                                                <small class="text-muted">
                                                    @if (!empty($details['category']))
                                                    <span class="badge bg-secondary">{{ $details['category'] }}</span>
                                                @endif
                                                </small>
                                            </td>
                                            <td>
                                                <form action="{{ route('cart.update', $id) }}" method="POST" class="d-flex align-items-center">
                                                    @csrf
                                                    @method('PATCH')
                                                    <input type="number" 
                                                           name="quantity" 
                                                           value="{{ $details['quantity'] }}" 
                                                           min="1" 
                                                           max="{{ $details['max_quantity'] ?? 999 }}" 
                                                           class="form-control form-control-sm quantity-input" 
                                                           onchange="this.form.submit()">
                                                </form>
                                                <small class="text-muted d-block mt-1">
                                                    Tối đa: {{ $details['max_quantity'] ?? 'N/A' }}
                                                </small>
                                            </td>
                                            <td>
                                                <span class="fw-bold text-primary">
                                                    {{ number_format($details['price'], 0, ',', '.') }} $
                                                </span>
                                            </td>
                                            <td>
                                                <span class="fw-bold text-success">
                                                    {{ number_format($details['price'] * $details['quantity'], 0, ',', '.') }} $
                                                </span>
                                            </td>
                                            <td>
                                                <form action="{{ route('cart.remove', $id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-outline-danger btn-sm" 
                                                            onclick="return confirmRemove()">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                                
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Tổng tiền và nút đặt cọc -->
                        <div class="row mt-4">
                            <div class="col-md-6">
                                <div class="card cart-summary">
                                    <div class="card-body">
                                        <h5 class="card-title">
                                            <i class="fas fa-calculator"></i> Tổng quan đơn hàng
                                        </h5>
                                        <div class="d-flex justify-content-between mb-2">
                                            <span>Tổng tiền hàng:</span>
                                            <span class="fw-bold">{{ number_format($total, 0, ',', '.') }} $</span>
                                        </div>
                                        <div class="d-flex justify-content-between mb-2">
                                            <span>Tiền cọc (30%):</span>
                                            <span class="fw-bold text-primary">{{ number_format($total * 0.3, 0, ',', '.') }} $</span>
                                        </div>
                                        <hr>
                                        <div class="d-flex justify-content-between">
                                            <span class="fw-bold">Số tiền cần thanh toán:</span>
                                            <span class="fw-bold text-success fs-5">{{ number_format($total * 0.3, 0, ',', '.') }} $</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 d-flex align-items-center justify-content-end">
                                <div class="text-end">
                                    <a href="{{ route('user.payment.index') }}" class="btn btn-success btn-lg me-2">
                                        <i class="fas fa-credit-card"></i> Đặt cọc ngay
                                    </a>
                                    <a href="{{ route('categories.index') }}" class="btn btn-outline-primary btn-lg">
                                        <i class="fas fa-shopping-bag"></i> Tiếp tục mua sắm
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- Thông tin về đặt cọc -->
                        <div class="card deposit-info mt-3">
                            <div class="card-body">
                                <h6><i class="fas fa-info-circle"></i> Thông tin về đặt cọc:</h6>
                                <ul class="mb-0">
                                    <li>Bạn cần đặt cọc 30% giá trị đơn hàng để giữ xe</li>
                                    <li>Số tiền còn lại sẽ được thanh toán khi nhận xe</li>
                                    <li>Thời gian giữ xe: 7 ngày kể từ ngày đặt cọc</li>
                                    <li>Liên hệ: 0123-456-789 để được tư vấn thêm</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-shopping-cart fa-4x text-muted mb-3"></i>
                    <h3 class="text-muted">Giỏ hàng của bạn trống</h3>
                    <p class="text-muted">Hãy thêm sản phẩm vào giỏ hàng để bắt đầu mua sắm</p>
                    <a href="{{ route('categories.index') }}" class="btn btn-primary btn-lg">
                        <i class="fas fa-shopping-bag"></i> Tiếp tục mua sắm
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

.table th {
    border-top: none;
    font-weight: 600;
    color: #495057;
}

.table td {
    vertical-align: middle;
}

.btn {
    border-radius: 8px;
}

.alert {
    border-radius: 10px;
    border: none;
}

.img-thumbnail {
    border-radius: 8px;
}

.badge {
    border-radius: 6px;
}
</style>

<script>
// Auto-submit form when quantity changes and show spinner on actual form submit only
document.addEventListener('DOMContentLoaded', function() {
    const quantityInputs = document.querySelectorAll('input[name="quantity"]');

    quantityInputs.forEach(input => {
        input.addEventListener('change', function() {
            setTimeout(() => {
                if (this.form) this.form.requestSubmit();
            }, 100);
        });
    });

    // Attach submit handlers to forms to show loading state per form
    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', function(e) {
            // Find the submit button within this form
            const btn = this.querySelector('button[type="submit"]');
            if (btn) {
                btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang xử lý...';
                btn.disabled = true;
            }
        });
    });
});

// Confirm before removing item
function confirmRemove() {
    return confirm('Bạn có chắc muốn xóa sản phẩm này khỏi giỏ hàng?');
}

// Confirm before checkout
function confirmCheckout() {
    const total = {{ $total ?? 0 }};
    const deposit = total * 0.3;
    return confirm(`Xác nhận đặt cọc?\n\nSố tiền cọc: ${new Intl.NumberFormat('vi-VN').format(deposit)} $\n\nBạn sẽ được liên hệ trong vòng 24h để xác nhận đơn hàng.`);
}
</script>
@endsection
