@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card search-results-card">
                <div class="search-results-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="mb-0"><i class="fas fa-car-side"></i> Kho Xe Hơi</h3>
                            <small class="opacity-75">Khám phá bộ sưu tập xe đa dạng của chúng tôi</small>
                        </div>
                        @auth
                            @if (auth()->user()->isAdmin())
                                <a class="btn btn-light" href="{{ route('admin.products.create') }}">
                                    <i class="fas fa-plus-circle"></i> Thêm Xe Mới
                                </a>
                            @endif
                        @endauth
                    </div>
                </div>
                <div class="card-body">
                    <!-- Enhanced Search Form -->
                    <div class="search-form-enhanced">
                        <form method="GET" action="{{ route('products.index') }}">
                            <div class="input-group">
                                <span class="input-group-text bg-gradient text-white">
                                    <i class="fas fa-search"></i>
                                </span>
                                <input type="text" 
                                       name="search" 
                                       class="form-control" 
                                       placeholder="Tìm kiếm xe theo tên, mô tả hoặc danh mục..." 
                                       value="{{ request('search') }}"
                                       autocomplete="off">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search"></i> Tìm kiếm
                                </button>
                                @if(request('search'))
                                    <a href="{{ route('products.index') }}" class="btn btn-outline-secondary">
                                        <i class="fas fa-times"></i> Xóa
                                    </a>
                                @endif
                            </div>
                        </form>
                    </div>

                    <!-- Enhanced Search Results Info -->
                    @if(request('search'))
                        <div class="search-info-alert">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-search me-2"></i>
                                <div>
                                    <strong>Kết quả tìm kiếm cho: "{{ request('search') }}"</strong>
                                    <br>
                                    <small>Tìm thấy {{ count($products) }} xe phù hợp</small>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if ($message = Session::get('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle"></i> {{ $message }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"
                                aria-label="Close"></button>
                        </div>
                    @endif

                        @if ($message = Session::get('error'))
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="fas fa-exclamation-circle"></i> {{ $message }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"
                                    aria-label="Close"></button>
                            </div>
                        @endif

                        <div class="row">
                            @forelse ($products as $product)
                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="card h-100 product-card-enhanced">
                                        <div class="position-relative">
                                            <img src="{{ $product->image_url }}" class="card-img-top"
                                                alt="{{ $product->name }}" style="height: 200px; object-fit: cover;">
                                            <div class="position-absolute top-0 end-0 m-2">
                                                <span class="badge bg-primary">{{ $product->category->name }}</span>
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            <h5 class="card-title">{{ $product->name }}</h5>
                                            <p class="card-text">
                                                <span class="price">${{ number_format($product->price, 2) }}</span>
                                            </p>
                                            <p class="card-text">
                                                <small class="text-muted">
                                                    <i class="fas fa-boxes"></i> Còn lại: {{ $product->available_stock }} chiếc
                                                </small>
                                            </p>
                                            @if ($product->description)
                                                <p class="card-text">
                                                    <small class="text-muted">
                                                        {{ Str::limit($product->description, 100) }}
                                                    </small>
                                                </p>
                                            @endif
                                        </div>
                                        <div class="card-footer bg-transparent">
                                            <div class="btn-group w-100" role="group">
                                                <a class="btn btn-info btn-sm"
                                                    href="{{ route('products.show', $product->id) }}" title="Xem Chi Tiết">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                @auth
                                                    @if (auth()->user()->isAdmin())
                                                        <a class="btn btn-primary btn-sm"
                                                            href="{{ route('admin.products.edit', $product->id) }}"
                                                            title="Chỉnh Sửa Xe">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <form action="{{ route('admin.products.destroy', $product->id) }}"
                                                            method="POST" style="display: inline;">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-danger btn-sm"
                                                                onclick="return confirm('Bạn có chắc chắn muốn xóa xe này?')"
                                                                title="Xóa Xe">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </form>
                                                    @endif
                                                @endauth
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="col-12">
                                    <div class="alert alert-info text-center">
                                        @if(request('search'))
                                            <i class="fas fa-search"></i> Không tìm thấy xe nào với từ khóa "<strong>{{ request('search') }}</strong>".
                                            <br>
                                            <a href="{{ route('products.index') }}" class="alert-link">Xem tất cả xe</a>
                                        @else
                                            <i class="fas fa-info-circle"></i> Không tìm thấy xe nào trong kho.
                                            @auth
                                                @if (auth()->user()->isAdmin())
                                                    <a href="{{ route('admin.products.create') }}" class="alert-link">Thêm xe đầu tiên</a>
                                                @endif
                                            @endauth
                                        @endif
                                    </div>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
