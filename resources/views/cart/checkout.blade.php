@extends('layouts.app')

@section('title', 'Thanh toán')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4>Thông tin đặt hàng</h4>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('user.payment.process') }}" id="checkout-form">
                        @csrf
                        <input type="hidden" name="total_price" value="{{ $total }}">
                        
                        <!-- Chọn địa chỉ -->
                        <div class="form-group">
                            <label><strong>Địa chỉ giao hàng</strong></label>
                            
                            @if($addresses->count() > 0)
                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="address_option" 
                                               id="use_saved_address" value="saved" checked>
                                        <label class="form-check-label" for="use_saved_address">
                                            Sử dụng địa chỉ đã lưu
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="address_option" 
                                               id="use_new_address" value="new">
                                        <label class="form-check-label" for="use_new_address">
                                            Nhập địa chỉ mới
                                        </label>
                                    </div>
                                </div>

                                <!-- Danh sách địa chỉ đã lưu -->
                                <div id="saved-addresses" class="mb-3">
                                    @foreach($addresses as $address)
                                        <div class="card mb-2 address-card {{ $address->is_default ? 'border-primary' : '' }}" 
                                             data-address-id="{{ $address->id }}">
                                            <div class="card-body p-3">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="address_id" 
                                                           value="{{ $address->id }}" id="address_{{ $address->id }}"
                                                           {{ $address->is_default ? 'checked' : '' }}>
                                                    <label class="form-check-label w-100" for="address_{{ $address->id }}">
                                                        <div class="d-flex justify-content-between">
                                                            <div>
                                                                <strong>{{ $address->name }}</strong>
                                                                @if($address->is_default)
                                                                    <span class="badge badge-primary ml-2">Mặc định</span>
                                                                @endif
                                                                <br>
                                                                <small class="text-muted">{{ $address->phone }}</small>
                                                                <br>
                                                                <span>{{ $address->full_address }}</span>
                                                            </div>
                                                        </div>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                    
                                    <div class="text-right">
                                        <a href="{{ route('user.addresses.create') }}" class="btn btn-sm btn-outline-primary" target="_blank">
                                            <i class="fas fa-plus"></i> Thêm địa chỉ mới
                                        </a>
                                    </div>
                                </div>
                            @endif

                            <!-- Form nhập địa chỉ mới -->
                            <div id="new-address-form" class="{{ $addresses->count() > 0 ? 'd-none' : '' }}">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="name">Tên người nhận <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                                   id="name" name="name" value="{{ old('name', auth()->user()->name) }}">
                                            @error('name')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="phone">Số điện thoại <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control @error('phone') is-invalid @enderror" 
                                                   id="phone" name="phone" value="{{ old('phone', auth()->user()->phone) }}">
                                            @error('phone')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label for="address">Địa chỉ <span class="text-danger">*</span></label>
                                    <textarea class="form-control @error('address') is-invalid @enderror" 
                                              id="address" name="address" rows="3" 
                                              placeholder="Nhập địa chỉ đầy đủ...">{{ old('address', auth()->user()->address) }}</textarea>
                                    @error('address')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Phương thức thanh toán -->
                        <div class="form-group">
                            <label><strong>Phương thức thanh toán</strong></label>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="card payment-method" data-method="cod">
                                        <div class="card-body text-center">
                                            <input type="radio" name="payment_method" value="cod" id="cod" checked>
                                            <label for="cod" class="w-100">
                                                <i class="fas fa-money-bill-wave fa-2x text-success mb-2"></i>
                                                <h6>Thanh toán khi nhận hàng (COD)</h6>
                                                <small class="text-muted">Đặt cọc 30%, thanh toán phần còn lại khi nhận xe</small>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card payment-method" data-method="momo">
                                        <div class="card-body text-center">
                                            <input type="radio" name="payment_method" value="momo" id="momo">
                                            <label for="momo" class="w-100">
                                                <i class="fab fa-cc-visa fa-2x text-primary mb-2"></i>
                                                <h6>Thanh toán MoMo</h6>
                                                <small class="text-muted">Đặt cọc 30% qua ví điện tử MoMo</small>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <input type="hidden" name="use_saved_address" id="use_saved_address_input" value="1">

                        <div class="text-center">
                            <a href="{{ route('cart.index') }}" class="btn btn-secondary mr-3">
                                <i class="fas fa-arrow-left"></i> Quay lại giỏ hàng
                            </a>
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-credit-card"></i> Đặt cọc ngay
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Tóm tắt đơn hàng -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5>Tóm tắt đơn hàng</h5>
                </div>
                <div class="card-body">
                    @foreach($cart as $item)
                        <div class="d-flex justify-content-between mb-2">
                            <div>
                                <strong>{{ $item['name'] }}</strong><br>
                                <small class="text-muted">{{ $item['category'] }} × {{ $item['quantity'] }}</small>
                            </div>
                            <div class="text-right">
                                {{ number_format($item['price'] * $item['quantity']) }} $
                            </div>
                        </div>
                        <hr>
                    @endforeach
                    
                    <div class="d-flex justify-content-between mb-2">
                        <strong>Tổng cộng:</strong>
                        <strong>{{ number_format($total) }} $</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2 text-primary">
                        <strong>Tiền cọc (30%):</strong>
                        <strong>{{ number_format($deposit) }} $</strong>
                    </div>
                    <div class="d-flex justify-content-between text-muted">
                        <span>Còn lại khi nhận xe:</span>
                        <span>{{ number_format($total - $deposit) }} $</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // Xử lý chuyển đổi giữa địa chỉ đã lưu và địa chỉ mới
    $('input[name="address_option"]').change(function() {
        if ($(this).val() === 'saved') {
            $('#saved-addresses').removeClass('d-none');
            $('#new-address-form').addClass('d-none');
            $('#use_saved_address_input').val('1');
            
            // Bỏ required cho form địa chỉ mới
            $('#new-address-form input, #new-address-form textarea').removeAttr('required');
        } else {
            $('#saved-addresses').addClass('d-none');
            $('#new-address-form').removeClass('d-none');
            $('#use_saved_address_input').val('0');
            
            // Thêm required cho form địa chỉ mới
            $('#name, #phone, #address').attr('required', true);
        }
    });

    // Highlight phương thức thanh toán được chọn
    $('.payment-method').click(function() {
        $('.payment-method').removeClass('border-primary');
        $(this).addClass('border-primary');
        $(this).find('input[type="radio"]').prop('checked', true);
    });

    // Highlight địa chỉ được chọn
    $('.address-card').click(function() {
        $('.address-card').removeClass('border-success');
        $(this).addClass('border-success');
        $(this).find('input[type="radio"]').prop('checked', true);
    });

    // Validation form
    $('#checkout-form').submit(function(e) {
        const addressOption = $('input[name="address_option"]:checked').val();
        
        if (addressOption === 'saved') {
            const selectedAddress = $('input[name="address_id"]:checked').val();
            if (!selectedAddress) {
                e.preventDefault();
                alert('Vui lòng chọn một địa chỉ giao hàng.');
                return false;
            }
        } else {
            const name = $('#name').val().trim();
            const phone = $('#phone').val().trim();
            const address = $('#address').val().trim();
            
            if (!name || !phone || !address) {
                e.preventDefault();
                alert('Vui lòng điền đầy đủ thông tin giao hàng.');
                return false;
            }
        }
    });
});
</script>
@endpush
@endsection
