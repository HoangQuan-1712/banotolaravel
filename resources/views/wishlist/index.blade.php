@extends('layouts.app')

@section('title', 'Danh Sách Yêu Thích')

@section('content')
<div class="container py-4">
    <div class="row">
        <div class="col-12">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h2 mb-0">
                        <i class="fas fa-heart text-danger"></i> 
                        Danh Sách Yêu Thích
                    </h1>
                    <p class="text-muted mb-0">Quản lý sản phẩm bạn yêu thích</p>
                </div>
                <div class="d-flex gap-2">
                    @if($wishlist->count() > 0)
                        <form action="{{ route('wishlist.clear') }}" method="POST" 
                              onsubmit="return confirm('Bạn có chắc muốn xóa tất cả sản phẩm khỏi danh sách yêu thích?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger">
                                <i class="fas fa-trash"></i> Xóa Tất Cả
                            </button>
                        </form>
                    @endif
                    <a href="{{ route('categories.index') }}" class="btn btn-primary">
                        <i class="fas fa-shopping-bag"></i> Tiếp Tục Mua Sắm
                    </a>
                </div>
            </div>

            <!-- Statistics -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card bg-primary text-white">
                        <div class="card-body text-center">
                            <i class="fas fa-heart fa-2x mb-2"></i>
                            <h4 class="mb-0">{{ $wishlist->count() }}</h4>
                            <small>Sản phẩm yêu thích</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-success text-white">
                        <div class="card-body text-center">
                            <i class="fas fa-shopping-cart fa-2x mb-2"></i>
                            <h4 class="mb-0">{{ $wishlist->where('product.quantity', '>', 0)->count() }}</h4>
                            <small>Còn hàng</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-warning text-white">
                        <div class="card-body text-center">
                            <i class="fas fa-exclamation-triangle fa-2x mb-2"></i>
                            <h4 class="mb-0">{{ $wishlist->where('product.quantity', '<=', 5)->where('product.quantity', '>', 0)->count() }}</h4>
                            <small>Sắp hết hàng</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-danger text-white">
                        <div class="card-body text-center">
                            <i class="fas fa-times-circle fa-2x mb-2"></i>
                            <h4 class="mb-0">{{ $wishlist->where('product.quantity', '=', 0)->count() }}</h4>
                            <small>Hết hàng</small>
                        </div>
                    </div>
                </div>
            </div>

            @if($wishlist->count() > 0)
                <!-- Wishlist Items -->
                <div class="row">
                    @foreach($wishlist as $item)
                        <div class="col-lg-4 col-md-6 mb-4">
                            <div class="card h-100 wishlist-card">
                                <div class="position-relative">
                                    <img src="{{ $item->product->image_url }}" 
                                         class="card-img-top" 
                                         alt="{{ $item->product->name }}"
                                         style="height: 200px; object-fit: cover;">
                                    
                                    <!-- Stock Status Badge -->
                                    @if($item->product->quantity == 0)
                                        <div class="position-absolute top-0 start-0 m-2">
                                            <span class="badge bg-danger">Hết hàng</span>
                                        </div>
                                    @elseif($item->product->quantity <= 5)
                                        <div class="position-absolute top-0 start-0 m-2">
                                            <span class="badge bg-warning">Sắp hết hàng</span>
                                        </div>
                                    @endif

                                    <!-- Rating Badge -->
                                    @if($item->product->reviews_count > 0)
                                        <div class="position-absolute top-0 end-0 m-2">
                                            <span class="badge bg-success">
                                                <i class="fas fa-star"></i> {{ $item->product->average_rating }}
                                            </span>
                                        </div>
                                    @endif

                                    <!-- Remove from wishlist button -->
                                    <div class="position-absolute bottom-0 end-0 m-2">
                                        <form action="{{ route('wishlist.remove', $item->product) }}" method="POST" 
                                              style="display: inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" 
                                                    onclick="return confirm('Bạn có chắc muốn xóa sản phẩm này khỏi danh sách yêu thích?')">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>

                                <div class="card-body d-flex flex-column">
                                    <div class="mb-2">
                                        <span class="badge bg-primary">{{ $item->product->category->name }}</span>
                                    </div>
                                    
                                    <h5 class="card-title">{{ $item->product->name }}</h5>
                                    
                                    <div class="mb-2">
                                        <span class="h5 text-success mb-0">${{ number_format($item->product->price, 0, ',', '.') }}</span>
                                    </div>

                                    <div class="mb-3">
                                        <small class="text-muted">
                                            <i class="fas fa-box"></i> 
                                            Còn lại: {{ $item->product->quantity }} sản phẩm
                                        </small>
                                    </div>

                                    <div class="mt-auto">
                                        <div class="d-grid gap-2">
                                            @if($item->product->quantity > 0)
                                                <form action="{{ route('wishlist.move-to-cart', $item->product) }}" method="POST">
                                                    @csrf
                                                    <button type="submit" class="btn btn-success w-100">
                                                        <i class="fas fa-shopping-cart"></i> Chuyển Vào Giỏ Hàng
                                                    </button>
                                                </form>
                                            @else
                                                <button class="btn btn-secondary w-100" disabled>
                                                    <i class="fas fa-times"></i> Hết Hàng
                                                </button>
                                            @endif
                                            
                                            <a href="{{ route('products.show', $item->product) }}" class="btn btn-outline-primary">
                                                <i class="fas fa-eye"></i> Xem Chi Tiết
                                            </a>
                                        </div>
                                    </div>
                                </div>

                                <div class="card-footer text-muted">
                                    <small>
                                        <i class="fas fa-clock"></i> 
                                        Đã thêm: {{ $item->added_at->diffForHumans() }}
                                    </small>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Quick Actions -->
                <div class="card mt-4">
                    <div class="card-body">
                        <h5 class="card-title">
                            <i class="fas fa-lightning-bolt"></i> Thao Tác Nhanh
                        </h5>
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Chuyển tất cả vào giỏ hàng:</h6>
                                <p class="text-muted">Chuyển tất cả sản phẩm còn hàng vào giỏ hàng để mua sắm.</p>
                                <button class="btn btn-success" onclick="moveAllToCart()">
                                    <i class="fas fa-shopping-cart"></i> Chuyển Tất Cả Vào Giỏ Hàng
                                </button>
                            </div>
                            <div class="col-md-6">
                                <h6>Xóa sản phẩm hết hàng:</h6>
                                <p class="text-muted">Xóa các sản phẩm không còn hàng khỏi danh sách yêu thích.</p>
                                <button class="btn btn-warning" onclick="removeOutOfStock()">
                                    <i class="fas fa-trash"></i> Xóa Sản Phẩm Hết Hàng
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

            @else
                <!-- Empty Wishlist -->
                <div class="text-center py-5">
                    <i class="fas fa-heart-broken fa-4x text-muted mb-4"></i>
                    <h3 class="text-muted">Danh sách yêu thích trống</h3>
                    <p class="text-muted mb-4">
                        Bạn chưa có sản phẩm nào trong danh sách yêu thích.<br>
                        Hãy duyệt qua các danh mục và thêm sản phẩm bạn yêu thích!
                    </p>
                    <div class="d-flex justify-content-center gap-3">
                        <a href="{{ route('categories.index') }}" class="btn btn-primary btn-lg">
                            <i class="fas fa-shopping-bag"></i> Khám Phá Sản Phẩm
                        </a>
                        <a href="{{ route('search.index') }}" class="btn btn-outline-primary btn-lg">
                            <i class="fas fa-search"></i> Tìm Kiếm
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
.wishlist-card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    border: none;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.wishlist-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 5px 20px rgba(0,0,0,0.15);
}

.card-img-top {
    transition: transform 0.3s ease;
}

.wishlist-card:hover .card-img-top {
    transform: scale(1.05);
}

.badge {
    font-size: 0.8rem;
}

.btn {
    border-radius: 8px;
}

.card-footer {
    background-color: #f8f9fa;
    border-top: 1px solid #dee2e6;
}
</style>
@endpush

@push('scripts')
<script>
function moveAllToCart() {
    if (confirm('Bạn có chắc muốn chuyển tất cả sản phẩm còn hàng vào giỏ hàng?')) {
        // This would need a backend endpoint to handle bulk move
        alert('Tính năng này sẽ được phát triển trong phiên bản tiếp theo!');
    }
}

function removeOutOfStock() {
    if (confirm('Bạn có chắc muốn xóa tất cả sản phẩm hết hàng khỏi danh sách yêu thích?')) {
        // This would need a backend endpoint to handle bulk remove
        alert('Tính năng này sẽ được phát triển trong phiên bản tiếp theo!');
    }
}

// Auto-refresh cart badge
function updateCartBadge() {
    const cartBadges = document.querySelectorAll('.cart-badge');
    cartBadges.forEach(badge => {
        if (badge.textContent === '0') {
            badge.style.display = 'none';
        } else {
            badge.style.display = 'inline';
        }
    });
}

// Update cart badge on page load
document.addEventListener('DOMContentLoaded', updateCartBadge);
</script>
@endpush
