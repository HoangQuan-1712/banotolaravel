@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="mb-0"><i class="fas fa-tag"></i> Danh Mục: {{ $category->name }}</h4>
                        <div>
                            @auth
                                @if(auth()->user()->isAdmin())
                                    <a class="btn btn-primary btn-sm" href="{{ route('admin.categories.edit', $category->id) }}">
                                        <i class="fas fa-edit"></i> Chỉnh Sửa Danh Mục
                                    </a>
                                @endif
                            @endauth
                            <a class="btn btn-secondary btn-sm" href="{{ route('categories.index') }}">
                                <i class="fas fa-arrow-left"></i> Quay Lại Danh Mục
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <h5>Thông Tin Danh Mục</h5>
                                <table class="table table-borderless">
                                    <tbody>
                                        <tr>
                                            <th width="150px" class="text-muted">Mã Danh Mục:</th>
                                            <td>{{ $category->id }}</td>
                                        </tr>
                                        <tr>
                                            <th class="text-muted">Tên Danh Mục:</th>
                                            <td><strong>{{ $category->name }}</strong></td>
                                        </tr>
                                        <tr>
                                            <th class="text-muted">Tổng Số Xe:</th>
                                            <td><span class="badge bg-primary">{{ $category->products->count() }}
                                                    xe</span></td>
                                        </tr>
                                        <tr>
                                            <th class="text-muted">Ngày Tạo:</th>
                                            <td>{{ $category->created_at->format('d/m/Y \lúc H:i') }}</td>
                                        </tr>
                                        <tr>
                                            <th class="text-muted">Cập Nhật Cuối:</th>
                                            <td>{{ $category->updated_at->format('d/m/Y \lúc H:i') }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <h5>Xe Trong Danh Mục Này</h5>
                        @if ($category->products->count() > 0)
                            <div class="row">
                                @foreach ($category->products as $product)
                                    <div class="col-md-6 col-lg-4 mb-4">
                                        <div class="card h-100 shadow-sm">
                                            <div class="position-relative">
                                                <img src="{{ $product->image_url }}" class="card-img-top"
                                                    alt="{{ $product->name }}" style="height: 200px; object-fit: cover;">
                                            </div>
                                            <div class="card-body">
                                                <h6 class="card-title">{{ $product->name }}</h6>
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
                                                            {{ Str::limit($product->description, 80) }}
                                                        </small>
                                                    </p>
                                                @endif
                                            </div>
                                            <div class="card-footer bg-transparent">
                                                <div class="btn-group w-100" role="group">
                                                    <a class="btn btn-info btn-sm"
                                                        href="{{ route('products.show', $product->id) }}"
                                                        title="Xem Chi Tiết Xe">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    @auth
                                                        @if(auth()->user()->isAdmin())
                                                            <a class="btn btn-primary btn-sm"
                                                                href="{{ route('admin.products.edit', $product->id) }}"
                                                                title="Chỉnh Sửa Xe">
                                                                <i class="fas fa-edit"></i>
                                                            </a>
                                                        @endif
                                                    @endauth
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i> Không tìm thấy xe nào trong danh mục này.
                                @auth
                                    @if(auth()->user()->isAdmin())
                                        <a href="{{ route('admin.products.create') }}" class="alert-link">Thêm xe đầu tiên vào danh mục này</a>
                                    @endif
                                @endauth
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
