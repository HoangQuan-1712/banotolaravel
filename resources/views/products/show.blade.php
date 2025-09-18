@extends('layouts.app')

@section('content')
    <div class="container py-4">
        <div class="row">
            <div class="col-12">
                <!-- Breadcrumb -->
                <nav aria-label="breadcrumb" class="mb-4">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('categories.index') }}"><i class="fas fa-home"></i> Trang
                                chủ</a></li>
                        <li class="breadcrumb-item"><a
                                href="{{ route('categories.show', $product->category->id) }}">{{ $product->category->name }}</a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">{{ $product->name }}</li>
                    </ol>
                </nav>

                <!-- Alert Messages -->
                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-triangle"></i> {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle"></i> {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <div class="card product-detail-card">
                    <div class="card-header bg-gradient-primary text-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <h3 class="mb-0"><i class="fas fa-car"></i> {{ $product->name }}</h3>
                            <div>
                                @auth
                                    @if (auth()->user()->isAdmin())
                                        <a class="btn btn-light btn-sm me-2"
                                            href="{{ route('admin.products.edit', $product->id) }}">
                                            <i class="fas fa-edit"></i> Chỉnh sửa
                                        </a>
                                    @endif
                                @endauth
                                <a class="btn btn-outline-light btn-sm" href="{{ route('products.index') }}">
                                    <i class="fas fa-arrow-left"></i> Quay lại
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="card-body p-0">
                        <div class="row g-0">
                            <!-- Product Images Section -->
                            <div class="col-lg-6">
                                <div class="product-gallery p-4">
                                    <div class="main-image-container mb-3">
                                        <img src="{{ $product->image_url }}" class="main-product-image"
                                            alt="{{ $product->name }}" id="mainImage"
                                            onerror="this.src='{{ asset('images/default-car.svg') }}'">
                                        <div class="image-overlay">
                                            <button class="btn btn-light btn-sm" onclick="openImageModal()">
                                                <i class="fas fa-expand"></i> Xem ảnh lớn
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Thumbnail Gallery -->
                                    <div class="thumbnail-gallery">
                                        <div class="row g-2">
                                            <div class="col-3">
                                                <img src="{{ $product->image_url }}" class="thumbnail-image active"
                                                    alt="Thumbnail 1" onclick="changeMainImage(this.src)"
                                                    onerror="this.src='{{ asset('images/default-car.svg') }}'">
                                            </div>
                                            <!-- Add more thumbnails here if you have multiple images -->
                                            @auth
                                                @if (auth()->user()->isAdmin())
                                                    <div class="col-3">
                                                        <div class="thumbnail-placeholder"
                                                            onclick="alert('Tính năng thêm ảnh sẽ được phát triển sau')">
                                                            <i class="fas fa-plus"></i>
                                                            <small>Thêm ảnh</small>
                                                        </div>
                                                    </div>
                                                @endif
                                            @endauth
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Product Details Section -->
                            <div class="col-lg-6">
                                <div class="product-details p-4">
                                    <!-- Category Badge -->
                                    <div class="mb-3">
                                        <span class="badge bg-primary fs-6 px-3 py-2">
                                            <i class="fas fa-tag"></i> {{ $product->category->name }}
                                        </span>
                                    </div>

                                    <!-- Price and Stock Info -->
                                    <div class="row mb-4">
                                        <div class="col-6">
                                            <div class="price-card">
                                                <div class="price-amount">${{ number_format($product->price, 2) }}</div>
                                                <div class="price-label">Giá bán</div>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="stock-card">
                                                <div class="stock-amount">{{ $product->available_stock }}</div>
                                                <div class="stock-label">Còn lại</div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Description -->
                                    @if ($product->description)
                                        <div class="description-section mb-4">
                                            <h5><i class="fas fa-info-circle"></i> Mô tả sản phẩm</h5>
                                            <p class="description-text">{{ $product->description }}</p>
                                        </div>
                                    @endif

                                    <!-- Add to Cart Form -->
                                    @auth
                                        <div class="add-to-cart-section mb-4">
                                            <h5><i class="fas fa-shopping-cart"></i> Thêm vào giỏ hàng</h5>
                                            <form action="{{ route('cart.add', $product->id) }}" method="POST"
                                                class="cart-form">
                                                @csrf
                                                <div class="row align-items-end">
                                                    <div class="col-md-4">
                                                        <label for="quantity" class="form-label">Số lượng:</label>
                                                        <div class="quantity-input-group">
                                                            <button type="button" class="btn btn-outline-secondary btn-sm"
                                                                onclick="decreaseQuantity()">
                                                                <i class="fas fa-minus"></i>
                                                            </button>
                                                            <input type="number" name="quantity" id="quantity" value="1"
                                                                min="1" max="{{ $product->available_stock }}"
                                                                class="form-control quantity-input">
                                                            <button type="button" class="btn btn-outline-secondary btn-sm"
                                                                onclick="increaseQuantity()">
                                                                <i class="fas fa-plus"></i>
                                                            </button>
                                                        </div>
                                                        <small class="text-muted">Tối đa: {{ $product->available_stock }} sản
                                                            phẩm</small>
                                                    </div>
                                                    <div class="col-md-8">
                                                        <button type="submit" class="btn btn-success btn-lg w-100">
                                                            <i class="fas fa-shopping-cart"></i> Thêm vào giỏ hàng
                                                        </button>
                                                    </div>
                                                </div>
                                            </form>

                                            <!-- Wishlist Button -->
                                            <div class="mt-3">
                                                @php
                                                    $isInWishlist = auth()
                                                        ->user()
                                                        ->wishlist()
                                                        ->where('product_id', $product->id)
                                                        ->exists();
                                                @endphp

                                                <form action="{{ route('wishlist.toggle', $product) }}" method="POST"
                                                    class="d-inline">
                                                    @csrf
                                                    <button type="submit"
                                                        class="btn {{ $isInWishlist ? 'btn-danger' : 'btn-outline-danger' }} btn-lg w-100">
                                                        <i class="fas fa-heart"></i>
                                                        {{ $isInWishlist ? 'Đã Yêu Thích' : 'Thêm Vào Yêu Thích' }}
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    @else
                                        <div class="login-prompt mb-4">
                                            <div class="alert alert-info">
                                                <i class="fas fa-info-circle"></i>
                                                Vui lòng <a href="{{ route('login') }}" class="alert-link">đăng nhập</a> để
                                                thêm sản phẩm vào giỏ hàng.
                                            </div>
                                        </div>
                                    @endauth

                                    <!-- Product Information - Only show to admin -->
                                    @auth
                                        @if (auth()->user()->isAdmin())
                                            <div class="product-info-section">
                                                <h5><i class="fas fa-list"></i> Thông tin sản phẩm (Admin)</h5>
                                                <div class="info-grid">
                                                    <div class="info-item">
                                                        <span class="info-label">Mã sản phẩm:</span>
                                                        <span class="info-value">#{{ $product->id }}</span>
                                                    </div>
                                                    <div class="info-item">
                                                        <span class="info-label">Danh mục:</span>
                                                        <span class="info-value">{{ $product->category->name }}</span>
                                                    </div>
                                                    <div class="info-item">
                                                        <span class="info-label">Ngày thêm:</span>
                                                        <span
                                                            class="info-value">{{ $product->created_at->format('d/m/Y H:i') }}</span>
                                                    </div>
                                                    <div class="info-item">
                                                        <span class="info-label">Cập nhật:</span>
                                                        <span
                                                            class="info-value">{{ $product->updated_at->format('d/m/Y H:i') }}</span>
                                                    </div>
                                                    <div class="info-item">
                                                        <span class="info-label">Đường dẫn ảnh:</span>
                                                        <span
                                                            class="info-value text-break">{{ $product->image ?? 'Không có ảnh' }}</span>
                                                    </div>
                                                    <div class="info-item">
                                                        <span class="info-label">URL ảnh:</span>
                                                        <span class="info-value text-break">{{ $product->image_url }}</span>
                                                    </div>
                                                    <div class="info-item">
                                                        <span class="info-label">Ảnh tồn tại:</span>
                                                        <span
                                                            class="info-value">{{ $product->hasImage() ? '✅ Có' : '❌ Không' }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    @endauth
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Reviews Section -->
        <div class="row">
            <div class="col-12">
                @include('products.partials.reviews-block', ['product' => $product])
            </div>
        </div>
    </div>

    <!-- Image Modal -->
    <div class="modal fade" id="imageModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ $product->name }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center">
                    <img src="{{ $product->image_url }}" class="img-fluid" alt="{{ $product->name }}"
                        onerror="this.src='{{ asset('images/default-car.svg') }}'">
                </div>
            </div>
        </div>
    </div>

    <style>
        .product-detail-card {
            border: none;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .bg-gradient-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .main-image-container {
            position: relative;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .main-product-image {
            width: 100%;
            height: 400px;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .main-product-image:hover {
            transform: scale(1.05);
        }

        .image-overlay {
            position: absolute;
            top: 10px;
            right: 10px;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .main-image-container:hover .image-overlay {
            opacity: 1;
        }

        .thumbnail-gallery {
            margin-top: 20px;
        }

        .thumbnail-image {
            width: 100%;
            height: 80px;
            object-fit: cover;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s ease;
            border: 3px solid transparent;
        }

        .thumbnail-image:hover,
        .thumbnail-image.active {
            border-color: #667eea;
            transform: scale(1.05);
        }

        .thumbnail-placeholder {
            width: 100%;
            height: 80px;
            border: 2px dashed #ddd;
            border-radius: 10px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: #999;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .thumbnail-placeholder:hover {
            border-color: #667eea;
            color: #667eea;
        }

        .price-card,
        .stock-card {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
            padding: 20px;
            border-radius: 15px;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .price-amount,
        .stock-amount {
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .price-label,
        .stock-label {
            font-size: 0.9rem;
            opacity: 0.9;
        }

        .description-section {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 15px;
        }

        .description-text {
            color: #666;
            line-height: 1.6;
            margin-bottom: 0;
        }

        .quantity-input-group {
            display: flex;
            align-items: center;
            border: 1px solid #ddd;
            border-radius: 8px;
            overflow: hidden;
        }

        .quantity-input {
            border: none;
            text-align: center;
            flex: 1;
            padding: 10px;
        }

        .quantity-input:focus {
            box-shadow: none;
        }

        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        .info-item {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }

        .info-label {
            color: #666;
            font-weight: 500;
        }

        .info-value {
            color: #333;
            font-weight: 600;
        }

        .cart-form {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 15px;
        }

        @media (max-width: 768px) {
            .info-grid {
                grid-template-columns: 1fr;
            }

            .main-product-image {
                height: 300px;
            }

            .price-amount,
            .stock-amount {
                font-size: 1.5rem;
            }
        }
    </style>

    <script>
        function changeMainImage(src) {
            document.getElementById('mainImage').src = src;

            // Update active thumbnail
            document.querySelectorAll('.thumbnail-image').forEach(img => {
                img.classList.remove('active');
            });
            event.target.classList.add('active');
        }

        function openImageModal() {
            const modal = new bootstrap.Modal(document.getElementById('imageModal'));
            modal.show();
        }

        function decreaseQuantity() {
            const input = document.getElementById('quantity');
            const currentValue = parseInt(input.value);
            if (currentValue > 1) {
                input.value = currentValue - 1;
            }
        }

        function increaseQuantity() {
            const input = document.getElementById('quantity');
            const currentValue = parseInt(input.value);
            const maxValue = parseInt(input.max);
            if (currentValue < maxValue) {
                input.value = currentValue + 1;
            }
        }

        // Add loading state to form
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('.cart-form');
            if (form) {
                form.addEventListener('submit', function() {
                    const button = this.querySelector('button[type="submit"]');
                    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang xử lý...';
                    button.disabled = true;
                });
            }
        });
    </script>
@endsection
