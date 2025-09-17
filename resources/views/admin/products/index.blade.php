@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h3 class="mb-0"><i class="fas fa-car-side"></i> Quản Lý Kho Xe Hơi</h3>
                        <small class="text-muted">Quản lý toàn bộ xe trong hệ thống</small>
                    </div>
                    <a class="btn btn-primary" href="{{ route('admin.products.create') }}">
                        <i class="fas fa-plus-circle"></i> Thêm Xe Mới
                    </a>
                </div>
                <div class="card-body">
                    @if ($message = Session::get('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle"></i> {{ $message }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    @if ($message = Session::get('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle"></i> {{ $message }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <div class="row">
                        @forelse ($products as $product)
                            <div class="col-md-6 col-lg-4 mb-4">
                                <div class="card h-100 shadow-sm">
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
                                        <div class="card-text">
                                            <div class="small text-muted mb-1">
                                                <i class="fas fa-warehouse"></i> Tổng kho: <strong>{{ $product->quantity }}</strong>
                                            </div>
                                            <div class="small text-muted mb-1">
                                                <i class="fas fa-pause-circle"></i> Đang giữ chỗ: <strong class="text-warning">{{ $product->reserved_quantity ?? 0 }}</strong>
                                            </div>
                                            <div class="small text-muted">
                                                <i class="fas fa-check-circle"></i> Khả dụng: <strong class="text-success">{{ $product->available_stock }}</strong>
                                            </div>
                                        </div>
                                        @if($product->description)
                                            <p class="card-text">
                                                <small class="text-muted">
                                                    {{ Str::limit($product->description, 100) }}
                                                </small>
                                            </p>
                                        @endif
                                    </div>
                                    <div class="card-footer bg-transparent">
                                        <div class="btn-group w-100" role="group">
                                                                            <a class="btn btn-info btn-sm" href="{{ route('admin.products.show', $product->id) }}" title="Xem Chi Tiết">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a class="btn btn-primary btn-sm" href="{{ route('admin.products.edit', $product->id) }}" title="Chỉnh Sửa Xe">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('admin.products.destroy', $product->id) }}" method="POST" style="display: inline;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm" 
                                                        onclick="return confirm('Bạn có chắc chắn muốn xóa xe này?')" title="Xóa Xe">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-12">
                                <div class="alert alert-info text-center">
                                    <i class="fas fa-info-circle"></i> Không tìm thấy xe nào trong kho.
                                    <a href="{{ route('admin.products.create') }}" class="alert-link">Thêm xe đầu tiên</a>
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
