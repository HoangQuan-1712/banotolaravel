@extends('layouts.admin')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="mb-0">
                    <i class="fas fa-star me-2"></i>
                    Đánh giá sản phẩm: {{ $product->name }}
                </h4>
                <a href="{{ route('admin.reviews.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-2"></i>
                    Quay lại
                </a>
            </div>

            <!-- Product Info -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-2">
                            <img src="{{ $product->image_url }}" 
                                 class="img-fluid rounded" 
                                 alt="{{ $product->name }}"
                                 style="max-height: 120px; object-fit: cover;">
                        </div>
                        <div class="col-md-6">
                            <h5 class="mb-2">{{ $product->name }}</h5>
                            <p class="text-muted mb-2">{{ $product->description }}</p>
                            <div class="d-flex align-items-center">
                                <span class="badge bg-primary me-2">{{ $product->category->name }}</span>
                                <span class="text-success fw-bold">${{ number_format($product->price, 2) }}</span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center">
                                <div class="display-6 fw-bold text-warning">{{ $stats['avg_rating'] ?: '0' }}</div>
                                <div class="mb-2">
                                    @for ($i = 1; $i <= 5; $i++)
                                        <i class="fas fa-star {{ $i <= round($stats['avg_rating']) ? 'text-warning' : 'text-muted' }}"></i>
                                    @endfor
                                </div>
                                <div class="text-muted">{{ $stats['total'] }} đánh giá</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Rating Statistics -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-chart-bar me-2"></i>
                        Thống kê đánh giá
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach($stats['rating_breakdown'] as $rating => $data)
                            <div class="col-md-2 text-center mb-3">
                                <div class="mb-2">
                                    {{ $rating }} <i class="fas fa-star text-warning"></i>
                                </div>
                                <div class="progress mb-2" style="height: 20px;">
                                    <div class="progress-bar bg-warning" 
                                         style="width: {{ $data['percentage'] }}%"
                                         title="{{ $data['percentage'] }}%">
                                    </div>
                                </div>
                                <small class="text-muted">{{ $data['count'] }} đánh giá</small>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Reviews List -->
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-comments me-2"></i>
                        Danh sách đánh giá ({{ $reviews->total() }})
                    </h6>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show">
                            <i class="fas fa-check-circle me-2"></i>
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if($reviews->count() > 0)
                        @foreach($reviews as $review)
                            @if(!$review->isAdminResponse())
                                <div class="border rounded p-3 mb-3">
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <div class="d-flex align-items-center">
                                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" 
                                                 style="width: 50px; height: 50px;">
                                                {{ strtoupper(substr($review->user->name, 0, 1)) }}
                                            </div>
                                            <div>
                                                <h6 class="mb-1">{{ $review->user->name }}</h6>
                                                <small class="text-muted">
                                                    {{ $review->created_at->format('d/m/Y H:i') }}
                                                    @if($review->is_verified_purchase)
                                                        • <span class="badge bg-success">Đã mua</span>
                                                    @endif
                                                </small>
                                            </div>
                                        </div>
                                        <div class="text-end">
                                            <div class="mb-2">
                                                @for ($i = 1; $i <= 5; $i++)
                                                    <i class="fas fa-star {{ $i <= $review->rating ? 'text-warning' : 'text-muted' }}"></i>
                                                @endfor
                                            </div>
                                            <span class="badge bg-{{ $review->status === 'approved' ? 'success' : ($review->status === 'rejected' ? 'danger' : 'warning') }}">
                                                {{ ucfirst($review->status) }}
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Review Content -->
                                    @if($review->title)
                                        <h6 class="fw-bold mb-2">{{ $review->title }}</h6>
                                    @endif
                                    <p class="mb-3">{{ $review->comment }}</p>

                                    <!-- Admin Responses -->
                                    @php
                                        $responses = $review->replies()->with('user')->get();
                                    @endphp
                                    
                                    @if($responses->count() > 0)
                                        <div class="mb-3">
                                            <strong class="text-primary mb-2 d-block">
                                                <i class="fas fa-reply me-1"></i>
                                                Phản hồi từ cửa hàng:
                                            </strong>
                                            @foreach($responses as $response)
                                                <div class="p-3 bg-primary bg-opacity-10 border-start border-primary border-3 rounded mb-2">
                                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                                        <strong class="text-primary">{{ $response->user->name }}</strong>
                                                        <small class="text-muted">{{ $response->created_at->format('d/m/Y H:i') }}</small>
                                                    </div>
                                                    <p class="mb-0">{{ $response->comment }}</p>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif

                                    <!-- Quick Actions -->
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('admin.reviews.show', $review) }}" 
                                               class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-eye me-1"></i>
                                                Chi tiết
                                            </a>
                                            @if($review->status === 'pending')
                                                <form method="POST" action="{{ route('admin.reviews.update-status', $review) }}" class="d-inline">
                                                    @csrf
                                                    @method('PATCH')
                                                    <input type="hidden" name="status" value="approved">
                                                    <button type="submit" class="btn btn-sm btn-success" title="Duyệt">
                                                        <i class="fas fa-check me-1"></i>
                                                        Duyệt
                                                    </button>
                                                </form>
                                                <form method="POST" action="{{ route('admin.reviews.update-status', $review) }}" class="d-inline">
                                                    @csrf
                                                    @method('PATCH')
                                                    <input type="hidden" name="status" value="rejected">
                                                    <button type="submit" class="btn btn-sm btn-danger" title="Từ chối">
                                                        <i class="fas fa-times me-1"></i>
                                                        Từ chối
                                                    </button>
                                                </form>
                                            @endif
                                        </div>

                                        <!-- Quick Response Form -->
                                        <button class="btn btn-sm btn-outline-secondary" 
                                                type="button" 
                                                data-bs-toggle="collapse" 
                                                data-bs-target="#quickResponse{{ $review->id }}">
                                            <i class="fas fa-reply me-1"></i>
                                            Phản hồi nhanh
                                        </button>
                                    </div>

                                    <!-- Quick Response Form (Collapsed) -->
                                    <div class="collapse mt-3" id="quickResponse{{ $review->id }}">
                                        <form method="POST" action="{{ route('admin.reviews.respond', $review) }}" class="border-top pt-3">
                                            @csrf
                                            <div class="mb-3">
                                                <textarea name="admin_response" class="form-control" rows="3" 
                                                          placeholder="Nhập phản hồi của bạn..." required></textarea>
                                            </div>
                                            <div class="text-end">
                                                <button type="submit" class="btn btn-primary btn-sm">
                                                    <i class="fas fa-paper-plane me-1"></i>
                                                    Gửi phản hồi
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            @endif
                        @endforeach

                        <!-- Pagination -->
                        <div class="d-flex justify-content-center mt-4">
                            {{ $reviews->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-comments fa-4x text-muted mb-3"></i>
                            <h5 class="text-muted">Chưa có đánh giá nào</h5>
                            <p class="text-muted">Sản phẩm này chưa có đánh giá từ khách hàng</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
