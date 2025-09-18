@extends('layouts.admin')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="mb-0">
                    <i class="fas fa-star me-2"></i>
                    Chi tiết đánh giá #{{ $review->id }}
                </h4>
                <a href="{{ route('admin.reviews.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-2"></i>
                    Quay lại
                </a>
            </div>

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="fas fa-check-circle me-2"></i>
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="row">
                <!-- Review Details -->
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-comment me-2"></i>
                                Nội dung đánh giá
                            </h5>
                        </div>
                        <div class="card-body">
                            <!-- Product Info -->
                            <div class="d-flex align-items-center mb-4 p-3 bg-light rounded">
                                <img src="{{ $review->product->image_url }}" 
                                     class="me-3" 
                                     style="width: 80px; height: 80px; object-fit: cover; border-radius: 10px;"
                                     alt="{{ $review->product->name }}">
                                <div>
                                    <h6 class="mb-1">{{ $review->product->name }}</h6>
                                    <small class="text-muted">Mã sản phẩm: #{{ $review->product->id }}</small>
                                    <br>
                                    <a href="{{ route('admin.reviews.by-product', $review->product) }}" class="btn btn-sm btn-outline-primary mt-1">
                                        <i class="fas fa-list me-1"></i>
                                        Xem tất cả đánh giá sản phẩm này
                                    </a>
                                </div>
                            </div>

                            <!-- Rating -->
                            <div class="mb-3">
                                <label class="form-label fw-bold">Đánh giá:</label>
                                <div class="d-flex align-items-center">
                                    <div class="me-3">
                                        @for($i = 1; $i <= 5; $i++)
                                            <i class="fas fa-star {{ $i <= $review->rating ? 'text-warning' : 'text-muted' }}" style="font-size: 1.2rem;"></i>
                                        @endfor
                                    </div>
                                    <span class="badge bg-primary fs-6">{{ $review->rating }}/5</span>
                                </div>
                            </div>

                            <!-- Title -->
                            @if($review->title)
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Tiêu đề:</label>
                                    <p class="mb-0">{{ $review->title }}</p>
                                </div>
                            @endif

                            <!-- Comment -->
                            <div class="mb-3">
                                <label class="form-label fw-bold">Nội dung:</label>
                                <div class="p-3 bg-light rounded">
                                    {{ $review->comment }}
                                </div>
                            </div>

                            <!-- Admin Responses -->
                            @php
                                $responses = $review->replies()->with('user')->get();
                            @endphp
                            
                            @if($responses->count() > 0)
                                <div class="mb-4">
                                    <label class="form-label fw-bold">Phản hồi từ cửa hàng:</label>
                                    @foreach($responses as $response)
                                        <div class="p-3 bg-primary bg-opacity-10 border-start border-primary border-3 rounded mb-2">
                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                <strong class="text-primary">
                                                    <i class="fas fa-store me-1"></i>
                                                    {{ $response->user->name }} (Admin)
                                                </strong>
                                                <small class="text-muted">{{ $response->created_at->format('d/m/Y H:i') }}</small>
                                            </div>
                                            <p class="mb-0">{{ $response->comment }}</p>
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            <!-- Response Form -->
                            <div class="border-top pt-4">
                                <h6 class="mb-3">
                                    <i class="fas fa-reply me-2"></i>
                                    Phản hồi đánh giá này
                                </h6>
                                <form method="POST" action="{{ route('admin.reviews.respond', $review) }}">
                                    @csrf
                                    <div class="mb-3">
                                        <textarea name="admin_response" class="form-control" rows="4" 
                                                  placeholder="Nhập phản hồi của bạn..." required></textarea>
                                    </div>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-paper-plane me-2"></i>
                                        Gửi phản hồi
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="col-md-4">
                    <!-- Customer Info -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="fas fa-user me-2"></i>
                                Thông tin khách hàng
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <strong>Tên:</strong> {{ $review->user->name }}
                            </div>
                            <div class="mb-3">
                                <strong>Email:</strong> {{ $review->user->email }}
                            </div>
                            @if($review->is_verified_purchase)
                                <div class="mb-3">
                                    <span class="badge bg-success">
                                        <i class="fas fa-check-circle me-1"></i>
                                        Đã xác minh mua hàng
                                    </span>
                                </div>
                            @endif
                            @if($review->order)
                                <div class="mb-3">
                                    <strong>Đơn hàng:</strong> 
                                    <a href="{{ route('admin.orders.show', $review->order) }}" class="text-decoration-none">
                                        #{{ $review->order->id }}
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Review Status -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="fas fa-cog me-2"></i>
                                Quản lý trạng thái
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <strong>Trạng thái hiện tại:</strong>
                                <br>
                                <span class="badge bg-{{ $review->status === 'approved' ? 'success' : ($review->status === 'rejected' ? 'danger' : 'warning') }} fs-6 mt-1">
                                    {{ ucfirst($review->status) }}
                                </span>
                            </div>
                            
                            <div class="mb-3">
                                <strong>Ngày tạo:</strong> {{ $review->created_at->format('d/m/Y H:i') }}
                            </div>

                            <!-- Status Actions -->
                            <div class="d-grid gap-2">
                                @if($review->status !== 'approved')
                                    <form method="POST" action="{{ route('admin.reviews.update-status', $review) }}">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="status" value="approved">
                                        <button type="submit" class="btn btn-success">
                                            <i class="fas fa-check me-2"></i>
                                            Duyệt đánh giá
                                        </button>
                                    </form>
                                @endif
                                
                                @if($review->status !== 'rejected')
                                    <form method="POST" action="{{ route('admin.reviews.update-status', $review) }}">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="status" value="rejected">
                                        <button type="submit" class="btn btn-danger">
                                            <i class="fas fa-times me-2"></i>
                                            Từ chối đánh giá
                                        </button>
                                    </form>
                                @endif

                                <form method="POST" action="{{ route('admin.reviews.destroy', $review) }}" 
                                      onsubmit="return confirm('Bạn có chắc muốn xóa đánh giá này?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger">
                                        <i class="fas fa-trash me-2"></i>
                                        Xóa đánh giá
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
