{{-- resources/views/products/partials/reviews-block.blade.php --}}
@php
    /** @var \App\Models\Product $product */
    $reviews = $product
        ->reviews()
        ->with(['user', 'order', 'replies.user'])
        ->where('status', 'approved')
        ->whereNull('parent_review_id') // Chỉ lấy review gốc, không lấy phản hồi
        ->latest()
        ->paginate(5);
    $avg = round($product->reviews()->whereNull('parent_review_id')->avg('rating'), 1);
    $totalReviews = $product->reviews()->whereNull('parent_review_id')->count();
    
    // Thống kê đánh giá theo từng mức sao
    $ratingStats = [];
    for ($i = 5; $i >= 1; $i--) {
        $count = $product->reviews()->where('rating', $i)->whereNull('parent_review_id')->count();
        $percentage = $totalReviews > 0 ? round(($count / $totalReviews) * 100) : 0;
        $ratingStats[$i] = ['count' => $count, 'percentage' => $percentage];
    }
    
    // Lấy danh sách đơn hàng hợp lệ để đánh giá (đã hoàn tất và chưa đánh giá sản phẩm này)
    $eligibleOrders = collect();
    if (auth()->check()) {
        try {
            $eligibleOrders = auth()->user()->orders()
                ->where(function($query) {
                    $query->whereIn('status', ['đã giao hàng', 'hoàn tất', 'completed'])
                          ->orWhere('status', 'like', '%đã thanh toán%')
                          ->orWhere('status', 'like', '%đã giao%')
                          ->orWhere('status', 'like', '%hoàn thành%');
                })
                ->whereHas('items', function($q) use ($product) {
                    $q->where('product_id', $product->id);
                })
                ->whereDoesntHave('reviews', function($q) use ($product) {
                    $q->where('product_id', $product->id);
                })
                ->with('items.product')
                ->latest()
                ->get();
        } catch (\Exception $e) {
            // Fallback nếu có lỗi với relationship
            $eligibleOrders = collect();
        }
    }
@endphp

<div class="card mt-4">
    <div class="card-body">
        <h5 class="card-title mb-4">
            <i class="fas fa-star text-warning me-2"></i>
            Đánh giá sản phẩm ({{ $totalReviews }})
        </h5>
        
        <!-- Tổng quan đánh giá -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="text-center">
                    <div class="display-4 fw-bold text-warning">{{ $avg ?: '0' }}</div>
                    <div class="mb-2">
                        @for ($i = 1; $i <= 5; $i++)
                            <span class="fs-5 {{ $i <= round($avg) ? 'text-warning' : 'text-muted' }}">★</span>
                        @endfor
                    </div>
                    <div class="text-muted">{{ $totalReviews }} đánh giá</div>
                </div>
            </div>
            <div class="col-md-8">
                @foreach($ratingStats as $star => $stat)
                    <div class="d-flex align-items-center mb-2">
                        <div class="me-2" style="width: 60px;">
                            {{ $star }} <span class="text-warning">★</span>
                        </div>
                        <div class="progress flex-grow-1 me-2" style="height: 8px;">
                            <div class="progress-bar bg-warning" style="width: {{ $stat['percentage'] }}%"></div>
                        </div>
                        <div class="text-muted" style="width: 50px;">{{ $stat['count'] }}</div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Thông tin đánh giá -->
        @auth
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>
                <strong>Muốn đánh giá sản phẩm này?</strong>
                <br>
                <small>Vui lòng vào <a href="{{ route('user.orders.index') }}" class="alert-link">Lịch sử đơn hàng</a> để đánh giá các sản phẩm đã mua.</small>
            </div>
        @else
            <div class="alert alert-warning">
                <i class="fas fa-sign-in-alt me-2"></i>
                <strong>Vui lòng <a href="{{ route('login') }}" class="alert-link">đăng nhập</a> để xem và viết đánh giá.</strong>
            </div>
        @endauth

        <!-- Filter và Sort -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="mb-0">
                <i class="fas fa-comments me-2"></i>
                Đánh giá từ khách hàng
            </h6>
            <div class="d-flex gap-2">
                <select class="form-select form-select-sm" style="width: auto;" id="ratingFilter">
                    <option value="">Tất cả đánh giá</option>
                    <option value="5">5 sao</option>
                    <option value="4">4 sao</option>
                    <option value="3">3 sao</option>
                    <option value="2">2 sao</option>
                    <option value="1">1 sao</option>
                </select>
                <select class="form-select form-select-sm" style="width: auto;" id="sortReviews">
                    <option value="newest">Mới nhất</option>
                    <option value="oldest">Cũ nhất</option>
                    <option value="highest">Điểm cao nhất</option>
                    <option value="lowest">Điểm thấp nhất</option>
                </select>
            </div>
        </div>

        {{-- Danh sách đánh giá --}}
        <div id="reviewsList">
            @forelse($reviews as $review)
                <div class="review-item border rounded p-3 mb-3" data-rating="{{ $review->rating }}">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div class="d-flex align-items-center">
                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" 
                                 style="width: 40px; height: 40px; font-size: 18px;">
                                {{ strtoupper(substr($review->user->name ?? 'U', 0, 1)) }}
                            </div>
                            <div>
                                <div class="fw-bold">{{ $review->user->name ?? 'Người dùng' }}</div>
                                <div class="text-muted small">
                                    <i class="fas fa-calendar-alt me-1"></i>
                                    {{ $review->created_at->format('d/m/Y H:i') }}
                                    @if($review->order)
                                        • Đơn hàng #{{ $review->order->id }}
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="text-end">
                            <div class="mb-1">
                                @for ($i = 1; $i <= 5; $i++)
                                    <span class="fs-6 {{ $i <= $review->rating ? 'text-warning' : 'text-muted' }}">★</span>
                                @endfor
                            </div>
                            <div class="badge bg-warning text-dark">{{ $review->rating }}/5</div>
                        </div>
                    </div>

                    <div class="review-content">
                        @if($review->title)
                            <h6 class="fw-bold mb-2">{{ $review->title }}</h6>
                        @endif
                        <p class="mb-2">{{ $review->content }}</p>
                        
                        @if($review->is_verified_purchase)
                            <div class="mb-2">
                                <span class="badge bg-success">
                                    <i class="fas fa-check-circle me-1"></i>
                                    Đã xác minh mua hàng
                                </span>
                            </div>
                        @endif
                    </div>

                    {{-- Phản hồi từ admin --}}
                    @if($review->replies && $review->replies->count() > 0)
                        @foreach($review->replies as $reply)
                            <div class="mt-3 p-3 bg-primary bg-opacity-10 border-start border-primary border-3 rounded">
                                <div class="d-flex align-items-center mb-2">
                                    <i class="fas fa-store text-primary me-2"></i>
                                    <strong class="text-primary">Phản hồi từ cửa hàng</strong>
                                    <small class="text-muted ms-auto">{{ $reply->created_at->format('d/m/Y H:i') }}</small>
                                </div>
                                <div class="mb-2">{{ $reply->comment }}</div>
                                <div class="text-muted small">
                                    <i class="fas fa-user me-1"></i>{{ $reply->user->name }}
                                </div>
                            </div>
                        @endforeach
                    @endif

                    {{-- Nút sửa nội dung (chỉ chủ review & còn hạn) --}}
                    @auth
                        @if (auth()->id() === $review->user_id && $review->isWithinEditWindow())
                            <div class="mt-3">
                                <a class="btn btn-sm btn-outline-secondary" href="{{ route('reviews.edit', $review) }}">
                                    <i class="fas fa-edit me-1"></i>Chỉnh sửa đánh giá
                                </a>
                                <small class="text-muted ms-2">
                                    (Còn {{ 7 - $review->created_at->diffInDays(now()) }} ngày)
                                </small>
                            </div>
                        @endif
                    @endauth
                </div>
            @empty
                <div class="text-center py-5">
                    <i class="fas fa-comments fa-3x text-muted mb-3"></i>
                    <h6 class="text-muted">Chưa có đánh giá nào cho sản phẩm này</h6>
                    <p class="text-muted">Hãy là người đầu tiên đánh giá sản phẩm này!</p>
                </div>
            @endforelse
        </div>

        <!-- Pagination -->
        @if($reviews->hasPages())
            <div class="d-flex justify-content-center mt-4">
                {{ $reviews->links() }}
            </div>
        @endif
    </div>
</div>

<!-- CSS và JavaScript cho star rating -->
<style>
.star-rating {
    font-size: 2rem;
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

.review-image:hover {
    transform: scale(1.05);
    transition: transform 0.2s ease;
}

.review-item {
    transition: box-shadow 0.2s ease;
}

.review-item:hover {
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Star rating functionality
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
    
    
    // Filter and sort functionality
    const ratingFilter = document.getElementById('ratingFilter');
    const sortReviews = document.getElementById('sortReviews');
    
    if (ratingFilter) {
        ratingFilter.addEventListener('change', filterReviews);
    }
    
    if (sortReviews) {
        sortReviews.addEventListener('change', sortReviewsList);
    }
    
    function filterReviews() {
        const selectedRating = ratingFilter.value;
        const reviewItems = document.querySelectorAll('.review-item');
        
        reviewItems.forEach(item => {
            if (!selectedRating || item.dataset.rating === selectedRating) {
                item.style.display = 'block';
            } else {
                item.style.display = 'none';
            }
        });
    }
    
    function sortReviewsList() {
        const sortValue = sortReviews.value;
        const reviewsList = document.getElementById('reviewsList');
        const reviewItems = Array.from(reviewsList.querySelectorAll('.review-item'));
        
        reviewItems.sort((a, b) => {
            const aRating = parseInt(a.dataset.rating);
            const bRating = parseInt(b.dataset.rating);
            
            switch(sortValue) {
                case 'highest':
                    return bRating - aRating;
                case 'lowest':
                    return aRating - bRating;
                case 'oldest':
                    // This would need actual date data
                    return 0;
                case 'newest':
                default:
                    return 0;
            }
        });
        
        reviewItems.forEach(item => reviewsList.appendChild(item));
    }
});
</script>
