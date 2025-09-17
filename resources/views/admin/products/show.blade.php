@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0"><i class="fas fa-car"></i> Car Details</h4>
                    <div>
                                                    <a class="btn btn-primary btn-sm" href="{{ route('admin.products.edit', $product->id) }}">
                                <i class="fas fa-edit"></i> Edit Car
                            </a>
                            <a class="btn btn-secondary btn-sm" href="{{ route('admin.products.index') }}">
                                <i class="fas fa-arrow-left"></i> Back to Cars
                            </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="text-center mb-4">
                                <img src="{{ $product->image_url }}" class="img-fluid rounded shadow" 
                                     alt="{{ $product->name }}" style="max-height: 400px; width: 100%; object-fit: cover;">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h3 class="mb-3">{{ $product->name }}</h3>
                            
                            <div class="mb-4">
                                <span class="badge bg-primary fs-6">{{ $product->category->name }}</span>
                            </div>

                            <div class="row mb-3">
                                <div class="col-6">
                                    <div class="card bg-light">
                                        <div class="card-body text-center">
                                            <h5 class="price mb-0">${{ number_format($product->price, 2) }}</h5>
                                            <small class="text-muted">Price</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="card bg-light">
                                        <div class="card-body text-center">
                                            <h5 class="quantity mb-0">{{ $product->quantity }}</h5>
                                            <small class="text-muted">In Stock</small>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            @if($product->description)
                                <div class="mb-4">
                                    <h5>Description</h5>
                                    <p class="text-muted">{{ $product->description }}</p>
                                </div>
                            @endif

                            <div class="mb-4">
                                <h5>Car Information</h5>
                                <table class="table table-borderless">
                                    <tbody>
                                        <tr>
                                            <th width="120px" class="text-muted">Car ID:</th>
                                            <td>{{ $product->id }}</td>
                                        </tr>
                                        <tr>
                                            <th class="text-muted">Category:</th>
                                            <td>{{ $product->category->name }}</td>
                                        </tr>
                                        <tr>
                                            <th class="text-muted">Added:</th>
                                            <td>{{ $product->created_at->format('F d, Y \a\t g:i A') }}</td>
                                        </tr>
                                        <tr>
                                            <th class="text-muted">Last Updated:</th>
                                            <td>{{ $product->updated_at->format('F d, Y \a\t g:i A') }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
