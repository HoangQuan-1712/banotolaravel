@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4><i class="fas fa-upload"></i> Test Upload Ảnh</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h5>Thông tin Storage</h5>
                            <ul class="list-group">
                                <li class="list-group-item">
                                    <strong>Storage Path:</strong> {{ storage_path('app/public') }}
                                </li>
                                <li class="list-group-item">
                                    <strong>Products Path:</strong> {{ storage_path('app/public/products') }}
                                </li>
                                <li class="list-group-item">
                                    <strong>Public Storage:</strong> {{ public_path('storage') }}
                                </li>
                                <li class="list-group-item">
                                    <strong>Symbolic Link:</strong> {{ file_exists(public_path('storage')) ? '✅ Đã tạo' : '❌ Chưa tạo' }}
                                </li>
                                <li class="list-group-item">
                                    <strong>Storage Writable:</strong> {{ is_writable(storage_path('app/public')) ? '✅ Có' : '❌ Không' }}
                                </li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h5>Danh sách ảnh đã upload</h5>
                            @php
                                $products = \App\Models\Product::whereNotNull('image')->get();
                            @endphp
                            
                            @if($products->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Tên</th>
                                                <th>Ảnh</th>
                                                <th>Trạng thái</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($products as $product)
                                                <tr>
                                                    <td>{{ $product->id }}</td>
                                                    <td>{{ $product->name }}</td>
                                                    <td>
                                                        <img src="{{ $product->image_url }}" 
                                                             alt="{{ $product->name }}" 
                                                             style="width: 50px; height: 50px; object-fit: cover;"
                                                             onerror="this.src='{{ asset('images/default-car.svg') }}'">
                                                    </td>
                                                    <td>
                                                        @if($product->hasImage())
                                                            <span class="badge bg-success">✅ OK</span>
                                                        @else
                                                            <span class="badge bg-danger">❌ Lỗi</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="alert alert-info">
                                    Chưa có sản phẩm nào có ảnh.
                                </div>
                            @endif
                        </div>
                    </div>
                    
                    <hr>
                    
                    <div class="text-center">
                        <a href="{{ route('admin.products.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Thêm sản phẩm mới
                        </a>
                        <a href="{{ route('admin.products.index') }}" class="btn btn-secondary">
                            <i class="fas fa-list"></i> Danh sách sản phẩm
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
