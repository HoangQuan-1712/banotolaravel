@extends('layouts.app')

@section('title', 'Quản lý địa chỉ')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Địa chỉ của tôi</h3>
                    <a href="{{ route('user.addresses.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Thêm địa chỉ mới
                    </a>
                </div>
                
                <div class="card-body">
                    @if($addresses->count() > 0)
                        <div class="row">
                            @foreach($addresses as $address)
                            <div class="col-md-6 mb-3">
                                <div class="card {{ $address->is_default ? 'border-primary' : '' }}">
                                    <div class="card-body">
                                        @if($address->is_default)
                                            <span class="badge badge-primary mb-2">Địa chỉ mặc định</span>
                                        @endif
                                        
                                        <h5 class="card-title">{{ $address->name }}</h5>
                                        <p class="card-text">
                                            <i class="fas fa-phone"></i> {{ $address->phone }}<br>
                                            <i class="fas fa-map-marker-alt"></i> {{ $address->full_address }}
                                        </p>
                                        
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('user.addresses.edit', $address) }}" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-edit"></i> Sửa
                                            </a>
                                            
                                            @if(!$address->is_default)
                                                <button type="button" class="btn btn-sm btn-outline-success set-default-btn" 
                                                        data-address-id="{{ $address->id }}">
                                                    <i class="fas fa-star"></i> Đặt mặc định
                                                </button>
                                            @endif
                                            
                                            <form method="POST" action="{{ route('user.addresses.destroy', $address) }}" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger" 
                                                        onclick="return confirm('Xác nhận xóa địa chỉ này?')">
                                                    <i class="fas fa-trash"></i> Xóa
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-map-marker-alt fa-3x text-muted mb-3"></i>
                            <h5>Chưa có địa chỉ nào</h5>
                            <p class="text-muted">Thêm địa chỉ để việc đặt hàng trở nên thuận tiện hơn.</p>
                            <a href="{{ route('user.addresses.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Thêm địa chỉ đầu tiên
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    $('.set-default-btn').click(function() {
        const addressId = $(this).data('address-id');
        const button = $(this);
        
        $.ajax({
            url: `/addresses/${addressId}/set-default`,
            method: 'PATCH',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    location.reload();
                }
            },
            error: function() {
                alert('Có lỗi xảy ra. Vui lòng thử lại.');
            }
        });
    });
});
</script>
@endpush
@endsection
