@extends('layouts.app')

@section('content')
    <div class="container py-4">
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="mb-0">
                        <i class="fas fa-shopping-bag me-2"></i>
                        Lịch sử đơn hàng
                    </h4>
                    <a href="{{ route('products.index') }}" class="btn btn-primary">
                        <i class="fas fa-shopping-cart me-2"></i>
                        Tiếp tục mua sắm
                    </a>
                </div>

                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show">
                        <i class="fas fa-check-circle me-2"></i>
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if ($orders->isEmpty())
                    <div class="text-center py-5">
                        <i class="fas fa-shopping-bag fa-4x text-muted mb-3"></i>
                        <h5 class="text-muted">Bạn chưa có đơn hàng nào</h5>
                        <p class="text-muted">Hãy khám phá các sản phẩm tuyệt vời của chúng tôi!</p>
                        <a href="{{ route('products.index') }}" class="btn btn-primary">
                            <i class="fas fa-shopping-cart me-2"></i>
                            Bắt đầu mua sắm
                        </a>
                    </div>
                @else
                    @foreach ($orders as $order)
                        <div class="card mb-4 order-card">
                            <div class="card-header bg-light">
                                <div class="row align-items-center">
                                    <div class="col-md-6">
                                        <h6 class="mb-0">
                                            <i class="fas fa-receipt me-2"></i>
                                            Đơn hàng #{{ $order->id }}
                                        </h6>
                                        <small class="text-muted">
                                            <i class="fas fa-calendar me-1"></i>
                                            {{ $order->created_at->format('d/m/Y H:i') }}
                                        </small>
                                    </div>
                                    <div class="col-md-6 text-md-end">
                                        <span class="badge bg-{{ $order->status_color }} fs-6 px-3 py-2">
                                            {{ $order->status_label }}
                                        </span>
                                        <div class="mt-1">
                                            <strong class="text-primary">
                                                {{ number_format($order->total_price, 0, ',', '.') }}đ
                                            </strong>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                @if($order->items && $order->items->count() > 0)
                                    @foreach($order->items as $item)
                                        <div class="row align-items-center mb-3 pb-3 border-bottom">
                                            <div class="col-md-2">
                                                @if($item->product)
                                                    <img src="{{ $item->product->image_url }}" 
                                                         class="img-thumbnail" 
                                                         style="width: 80px; height: 80px; object-fit: cover;"
                                                         alt="{{ $item->product->name }}">
                                                @else
                                                    <div class="bg-light d-flex align-items-center justify-content-center" 
                                                         style="width: 80px; height: 80px;">
                                                        <i class="fas fa-image text-muted"></i>
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="col-md-6">
                                                @if($item->product)
                                                    <h6 class="mb-1">
                                                        <a href="{{ route('products.show', $item->product->id) }}" 
                                                           class="text-decoration-none">
                                                            {{ $item->product->name }}
                                                        </a>
                                                    </h6>
                                                    <small class="text-muted">
                                                        Số lượng: {{ $item->quantity }} | 
                                                        Giá: {{ number_format($item->price, 0, ',', '.') }}đ
                                                    </small>
                                                @else
                                                    <h6 class="mb-1 text-muted">Sản phẩm đã bị xóa</h6>
                                                    <small class="text-muted">
                                                        Số lượng: {{ $item->quantity }} | 
                                                        Giá: {{ number_format($item->price, 0, ',', '.') }}đ
                                                    </small>
                                                @endif
                                            </div>
                                            <div class="col-md-4 text-md-end">
                                                @if($item->product && in_array($order->status, ['đã giao hàng', 'hoàn tất', 'completed']) && !$order->reviews->where('product_id', $item->product->id)->count())
                                                    <button class="btn btn-warning btn-sm" 
                                                            onclick="openReviewModal({{ $order->id }}, {{ $item->product->id }}, '{{ $item->product->name }}')">
                                                        <i class="fas fa-star me-1"></i>
                                                        Đánh giá
                                                    </button>
                                                @elseif($item->product && $order->reviews->where('product_id', $item->product->id)->count())
                                                    <span class="badge bg-success">
                                                        <i class="fas fa-check me-1"></i>
                                                        Đã đánh giá
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                @endif
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <small class="text-muted">
                                            <i class="fas fa-user me-1"></i>
                                            {{ $order->name }}<br>
                                            <i class="fas fa-map-marker-alt me-1"></i>
                                            {{ $order->address }}<br>
                                            <i class="fas fa-phone me-1"></i>
                                            {{ $order->phone }}
                                        </small>
                                    </div>
                                    <div class="col-md-6 text-md-end">
                                        <a href="{{ route('user.orders.show', $order->id) }}" 
                                           class="btn btn-outline-primary btn-sm">
                                            <i class="fas fa-eye me-1"></i>
                                            Xem chi tiết
                                        </a>

                                        @php
                                            // Define statuses where user can take action
                                            $cancelableStatuses = [
                                                'chờ đặt cọc',
                                                'thanh toán MoMo không thành công',
                                                'chờ thanh toán',
                                                \App\Models\Order::STATUS_AWAITING_DEPOSIT ?? 'chờ đặt cọc'
                                            ];
                                        @endphp

                                        @if(in_array($order->status, $cancelableStatuses))
                                            <a href="{{ route('user.orders.momo.pay', $order) }}" class="btn btn-success btn-sm ms-2">
                                                <i class="fas fa-credit-card me-1"></i>
                                                Tiếp tục đặt cọc
                                            </a>
                                            <form action="{{ route('user.orders.cancel', $order) }}" method="POST" class="d-inline ms-2" onsubmit="return confirm('Bạn có chắc chắn muốn hủy đơn hàng này?');">
                                                @csrf
                                                <button type="submit" class="btn btn-danger btn-sm">
                                                    <i class="fas fa-times me-1"></i>
                                                    Hủy đơn
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach

                    <!-- Pagination -->
                    @if($orders->hasPages())
                        <div class="d-flex justify-content-center">
                            {{ $orders->links() }}
                        </div>
                    @endif
                @endif
            </div>
        </div>
    </div>

    <!-- Review Modal -->
    <div class="modal fade" id="reviewModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-star me-2"></i>
                        Đánh giá sản phẩm
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="reviewForm" method="POST">
                    @csrf
                    <div class="modal-body">
                        <input type="hidden" name="product_id" id="review_product_id">
                        <input type="hidden" name="order_id" id="review_order_id">
                        
                        <div class="mb-3">
                            <label class="form-label">Sản phẩm</label>
                            <input type="text" class="form-control" id="review_product_name" readonly>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Đánh giá của bạn</label>
                            <div class="star-rating mb-2">
                                @for ($i = 1; $i <= 5; $i++)
                                    <span class="star" data-rating="{{ $i }}">★</span>
                                @endfor
                            </div>
                            <input type="hidden" name="rating" id="rating" value="5" required>
                            <div class="rating-text text-muted small"></div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Tiêu đề đánh giá</label>
                            <input type="text" name="title" class="form-control" required 
                                   placeholder="Tóm tắt đánh giá của bạn...">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Nội dung đánh giá</label>
                            <textarea name="comment" class="form-control" rows="4" required 
                                      placeholder="Chia sẻ trải nghiệm của bạn về sản phẩm này..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-paper-plane me-2"></i>
                            Gửi đánh giá
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <style>
        .order-card {
            border: none;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: transform 0.2s ease;
        }
        
        .order-card:hover {
            transform: translateY(-2px);
        }
        
        .star-rating {
            font-size: 1.5rem;
            color: #ddd;
            cursor: pointer;
        }
        
        .star-rating .star {
            transition: color 0.2s ease;
            margin-right: 5px;
        }
        
        .star-rating .star:hover,
        .star-rating .star.active {
            color: #ffc107;
        }
        
        .star-rating .star:hover ~ .star {
            color: #ddd;
        }
    </style>

    <script>
        // Star rating functionality
        document.addEventListener('DOMContentLoaded', function() {
            const stars = document.querySelectorAll('.star-rating .star');
            const ratingInput = document.getElementById('rating');
            const ratingText = document.querySelector('.rating-text');
            
            const ratingTexts = {
                1: 'Rất không hài lòng',
                2: 'Không hài lòng', 
                3: 'Bình thường',
                4: 'Hài lòng',
                5: 'Rất hài lòng'
            };

            // Initialize with default rating
            updateStars(5);
            
            stars.forEach(star => {
                star.addEventListener('click', function() {
                    const rating = parseInt(this.dataset.rating);
                    updateStars(rating);
                    ratingInput.value = rating;
                    ratingText.textContent = ratingTexts[rating];
                });
                
                star.addEventListener('mouseover', function() {
                    const rating = parseInt(this.dataset.rating);
                    highlightStars(rating);
                });
            });
            
            document.querySelector('.star-rating').addEventListener('mouseleave', function() {
                updateStars(parseInt(ratingInput.value));
            });
            
            function updateStars(rating) {
                stars.forEach((star, index) => {
                    if (index < rating) {
                        star.classList.add('active');
                    } else {
                        star.classList.remove('active');
                    }
                });
                ratingText.textContent = ratingTexts[rating];
            }
            
            function highlightStars(rating) {
                stars.forEach((star, index) => {
                    if (index < rating) {
                        star.style.color = '#ffc107';
                    } else {
                        star.style.color = '#ddd';
                    }
                });
            }
        });

        function openReviewModal(orderId, productId, productName) {
            document.getElementById('review_order_id').value = orderId;
            document.getElementById('review_product_id').value = productId;
            document.getElementById('review_product_name').value = productName;
            document.getElementById('reviewForm').action = '/reviews/store';
            
            const modal = new bootstrap.Modal(document.getElementById('reviewModal'));
            modal.show();
        }
    </script>
@endsection
