@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row">
        <div class="col-12">
            <h1 class="mb-4">
                <i class="fas fa-credit-card text-primary"></i> Đặt cọc đơn hàng
            </h1>

            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle"></i> {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="row">
                <!-- Form thanh toán -->
                <div class="col-lg-8">
                    <div class="card shadow-sm">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0"><i class="fas fa-user"></i> Thông tin đặt cọc</h5>
                        </div>
                        <div class="card-body">
                            <form id="paymentForm" method="POST" action="{{ route('user.payment.process') }}">
                                @if(request('order_id'))
                                    <input type="hidden" name="order_id" value="{{ request('order_id') }}">
                                @endif
                                @csrf
<meta name="csrf-token" content="{{ csrf_token() }}">
                                <input type="hidden" name="preview_voucher" id="preview_voucher" value="0">
                                <input type="hidden" name="selected_voucher_id" id="selected_voucher_id" value="">
                                
                                <!-- Địa chỉ giao hàng (Shopee style) -->
                                <div class="mb-3">
                                    <label class="form-label">
                                        <i class="fas fa-map-marker-alt text-danger"></i> Địa chỉ giao hàng
                                    </label>
                                    
                                    @if($addresses->count() > 0)
                                        <!-- Default address display -->
                                        <div class="address-display-card" id="selectedAddressDisplay">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div class="address-content">
                                                    <div class="d-flex align-items-center mb-2">
                                                        <strong id="displayName">{{ $defaultAddress->name ?? $addresses->first()->name }}</strong>
                                                        <span class="text-muted mx-2">|</span>
                                                        <span class="text-muted" id="displayPhone">{{ $defaultAddress->phone ?? $addresses->first()->phone }}</span>
                                                    </div>
                                                    <div class="text-muted" id="displayAddress">
                                                        {{ $defaultAddress->full_address ?? $addresses->first()->full_address }}
                                                    </div>
                                                    @if($defaultAddress || $addresses->first()->is_default)
                                                        <span class="badge bg-danger mt-2">Mặc định</span>
                                                    @endif
                                                </div>
                                                <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addressSelectionModal">
                                                    Thay đổi
                                                </button>
                                            </div>
                                        </div>
                                        
                                        <!-- Hidden inputs to submit with form -->
                                        <input type="hidden" name="name" id="selectedName" value="{{ $defaultAddress->name ?? $addresses->first()->name }}">
                                        <input type="hidden" name="phone" id="selectedPhone" value="{{ $defaultAddress->phone ?? $addresses->first()->phone }}">
                                        <input type="hidden" name="address" id="selectedAddress" value="{{ $defaultAddress->full_address ?? $addresses->first()->full_address }}">
                                        <input type="hidden" name="address_id" id="addressId" value="{{ $defaultAddress->id ?? $addresses->first()->id }}">
                                        <input type="hidden" name="use_saved_address" id="useSavedAddress" value="1">
                                    @else
                                        <!-- No address - show add button -->
                                        <div class="no-address-card">
                                            <div class="text-center py-4">
                                                <i class="fas fa-map-marker-alt text-muted mb-3" style="font-size: 2rem;"></i>
                                                <p class="text-muted mb-3">Bạn chưa có địa chỉ giao hàng</p>
                                                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addAddressModal">
                                                    <i class="fas fa-plus"></i> Thêm địa chỉ giao hàng
                                                </button>
                                            </div>
                                        </div>
                                        
                                        <!-- Hidden inputs for manual entry -->
                                        <input type="hidden" name="name" id="selectedName" value="">
                                        <input type="hidden" name="phone" id="selectedPhone" value="">
                                        <input type="hidden" name="address" id="selectedAddress" value="">
                                        <input type="hidden" name="address_id" id="addressId" value="">
                                        <input type="hidden" name="use_saved_address" id="useSavedAddress" value="0">
                                    @endif
                                    
                                    @error('address')
                                        <div class="text-danger small mt-2">{{ $message }}</div>
                                    @enderror
                                </div>

                                <input type="hidden" name="total_price" value="{{ $total }}">
                                
                                <!-- Phương thức đặt cọc -->
                                <div class="mb-4">
                                    <h6 class="mb-3"><i class="fas fa-credit-card"></i> Chọn phương thức đặt cọc</h6>
                                    
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <div class="payment-method-card active" onclick="selectPaymentMethod('momo', this)">
                                                <input type="radio" name="payment_method" value="momo" id="momo" checked>
                                                <label for="momo" class="payment-method-label">
                                                    <div class="payment-icon">
                                                        <i class="fas fa-mobile-alt text-danger"></i>
                                                    </div>
                                                    <div class="payment-info">
                                                        <h6 class="mb-1">Thanh toán MoMo</h6>
                                                        <small class="text-muted">Thanh toán nhanh chóng qua ví MoMo</small>
                                                    </div>
                                                </label>
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-6 mb-3">
                                            <div class="payment-method-card" onclick="selectPaymentMethod('cod', this)">
                                                <input type="radio" name="payment_method" value="cod" id="cod">
                                                <label for="cod" class="payment-method-label">
                                                    <div class="payment-icon">
                                                        <i class="fas fa-money-bill-wave text-success"></i>
                                                    </div>
                                                    <div class="payment-info">
                                                        <h6 class="mb-1">Thanh toán COD</h6>
                                                        <small class="text-muted">Thanh toán khi nhận hàng</small>
                                                    </div>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Nút thanh toán -->
                                <div class="d-grid gap-2">
                                    <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#voucherModal">
                                        <i class="fas fa-gift"></i> Chọn voucher / Quà tặng
                                    </button>
                                    <button type="submit" class="btn btn-primary btn-lg" id="submitBtn">
                                        <i class="fas fa-lock"></i> Xác nhận đặt cọc
                                    </button>
                                    <a href="{{ route('cart.index') }}" class="btn btn-outline-secondary">
                                        <i class="fas fa-arrow-left"></i> Quay lại giỏ hàng
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Tóm tắt đơn hàng -->
                <div class="col-lg-4">
                    <div class="card shadow-sm">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0"><i class="fas fa-shopping-cart"></i> Tóm tắt đặt cọc</h5>
                        </div>
                        <div class="card-body">
                            <div class="order-summary">
                                @foreach($cart as $item)
                                    <div class="order-item d-flex align-items-center mb-3">
                                        <img src="{{ $item['image'] ? asset('storage/' . $item['image']) : asset('images/default-car.svg') }}" 
                                             alt="{{ $item['name'] }}" 
                                             class="order-item-image me-3"
                                             onerror="this.src='{{ asset('images/default-car.svg') }}'">
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1">{{ $item['name'] }}</h6>
                                            <small class="text-muted">Số lượng: {{ $item['quantity'] }}</small>
                                        </div>
                                        <div class="text-end">
                                            <strong>{{ number_format($item['price'] * $item['quantity'], 0, ',', '.') }} đ</strong>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            
                            <hr>
                            
                            <div class="order-total">
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Tổng tiền hàng:</span>
                                    <strong>{{ number_format($total, 0, ',', '.') }} đ</strong>
                                </div>
                                <div class="d-flex justify-content-between mb-2" id="voucherDiscountRow" style="display:none;">
                                    <span>Giảm giá voucher:</span>
                                    <strong class="text-success" id="voucherDiscountText">-0 đ</strong>
                                </div>
                                <div class="d-flex justify-content-between mb-2" id="adjustedSubtotalRow" style="display:none;">
                                    <span>Tạm tính sau giảm:</span>
                                    <strong id="adjustedSubtotalText">0 đ</strong>
                                </div>
                                <div class="d-flex justify-content-between mb-2" id="selectedVoucherRow" style="display:none;">
                                    <span>Voucher đã chọn:</span>
                                    <strong id="selectedVoucherText">(chưa chọn)</strong>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Tiền cọc (30%):</span>
                                    <span class="fw-bold text-primary">{{ number_format($total * 0.3, 0, ',', '.') }} đ</span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Phí vận chuyển:</span>
                                    <span class="text-success">Miễn phí</span>
                                </div>
                                <hr>
                                <div class="d-flex justify-content-between">
                                    <span class="fw-bold fs-5">Số tiền cần đặt cọc:</span>
                                    <span class="fw-bold fs-5 text-primary" id="depositText">{{ number_format($total * 0.3, 0, ',', '.') }} đ</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Thông tin về đặt cọc -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h6><i class="fas fa-info-circle text-info"></i> Thông tin về đặt cọc:</h6>
                    <div class="row">
                        <div class="col-md-6">
                            <ul class="mb-0">
                                <li>Bạn cần đặt cọc 30% giá trị đơn hàng để giữ xe</li>
                                <li>Số tiền còn lại sẽ được thanh toán khi nhận xe</li>
                                <li>Thời gian giữ xe: 7 ngày kể từ ngày đặt cọc</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <ul class="mb-0">
                                <li>Liên hệ: 0123-456-789 để được tư vấn thêm</li>
                                <li>Email: support@autodealer.com</li>
                                <li>Giờ làm việc: 8:00 - 18:00 (Thứ 2 - Thứ 7)</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal chọn Voucher -->
<div class="modal fade" id="voucherModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-gift me-2"></i> Chọn Ưu Đãi & Quà Tặng</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="voucherModalBody">
                <div class="text-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Đang tìm ưu đãi tốt nhất cho bạn...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                <button type="button" class="btn btn-primary" id="applyVoucherBtn">Áp dụng</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal chọn địa chỉ -->
<div class="modal fade" id="addressSelectionModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-map-marker-alt"></i> Chọn địa chỉ giao hàng</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                @if($addresses->count() > 0)
                    <div class="address-list">
                        @foreach($addresses as $address)
                            <div class="address-option"
                                 data-address-id="{{ $address->id }}"
                                 data-name="{{ $address->name }}"
                                 data-phone="{{ $address->phone }}"
                                 data-address_line_1="{{ $address->address_line_1 }}"
                                 data-address_line_2="{{ $address->address_line_2 }}"
                                 data-city="{{ $address->city }}"
                                 data-district="{{ $address->district }}"
                                 data-ward="{{ $address->ward }}"
                                 data-postal_code="{{ $address->postal_code }}"
                                 data-update-url="{{ route('user.addresses.update', $address) }}"
                                 data-delete-url="{{ route('user.addresses.destroy', $address) }}"
                                 data-set-default-url="{{ route('user.addresses.set-default', $address) }}">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="modal_address_selection" 
                                           id="modal_address_{{ $address->id }}" value="{{ $address->id }}"
                                           {{ ($defaultAddress && $defaultAddress->id == $address->id) || (!$defaultAddress && $loop->first) ? 'checked' : '' }}>
                                    <label class="form-check-label w-100" for="modal_address_{{ $address->id }}">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div class="address-details">
                                                <div class="d-flex align-items-center mb-1">
                                                    <strong>{{ $address->name }}</strong>
                                                    @if($address->is_default)
                                                        <span class="badge bg-danger ms-2">Mặc định</span>
                                                    @endif
                                                </div>
                                                <div class="text-muted mb-1">{{ $address->phone }}</div>
                                                <div class="text-muted">{{ $address->full_address }}</div>
                                            </div>
                                            <div class="address-actions">
                                                <button type="button" class="btn btn-sm btn-outline-secondary" 
                                                        onclick="editAddress({{ $address->id }})" title="Chỉnh sửa">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                @if(!$address->is_default)
                                                    <button type="button" class="btn btn-sm btn-outline-success" 
                                                            onclick="setDefaultAddress({{ $address->id }})" title="Đặt mặc định">
                                                        <i class="fas fa-check-circle"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-outline-danger" 
                                                            onclick="deleteAddress({{ $address->id }})" title="Xóa">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                @endif
                                            </div>
                                        </div>
                                    </label>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
                
                <div class="mt-3">
                    <button type="button" class="btn btn-outline-primary w-100" data-bs-toggle="modal" data-bs-target="#addAddressModal">
                        <i class="fas fa-plus"></i> Thêm địa chỉ mới
                    </button>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                <button type="button" class="btn btn-primary" onclick="confirmAddressSelection()">
                    Xác nhận
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal sửa địa chỉ -->
<div class="modal fade" id="editAddressModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-edit"></i> Chỉnh sửa địa chỉ</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editAddressForm" method="POST" action="#">
                @csrf
                <input type="hidden" name="_method" value="PATCH">
                <div class="modal-body">
                    @include('user.addresses.partials.form')
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Lưu thay đổi
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal thêm địa chỉ mới -->
<div class="modal fade" id="addAddressModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-plus"></i> Thêm địa chỉ mới</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addAddressForm" method="POST" action="{{ route('user.addresses.store') }}">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Họ và tên <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Số điện thoại <span class="text-danger">*</span></label>
                            <input type="text" name="phone" class="form-control" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Tỉnh/Thành phố <span class="text-danger">*</span></label>
                            <input type="text" name="city" class="form-control" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Quận/Huyện <span class="text-danger">*</span></label>
                            <input type="text" name="district" class="form-control" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Phường/Xã <span class="text-danger">*</span></label>
                            <input type="text" name="ward" class="form-control" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Địa chỉ cụ thể <span class="text-danger">*</span></label>
                        <input type="text" name="address_line_1" class="form-control" placeholder="Số nhà, tên đường" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Địa chỉ bổ sung</label>
                        <input type="text" name="address_line_2" class="form-control" placeholder="Tòa nhà, tầng, căn hộ...">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Mã bưu điện</label>
                        <input type="text" name="postal_code" class="form-control">
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="is_default" id="is_default">
                        <label class="form-check-label" for="is_default">
                            Đặt làm địa chỉ mặc định
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Lưu địa chỉ
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.payment-method-card {
    border: 2px solid #e9ecef;
    border-radius: 10px;
    padding: 15px;
    cursor: pointer;
    transition: all 0.3s ease;
    position: relative;
}

.payment-method-card:hover {
    border-color: #007bff;
    box-shadow: 0 0 10px rgba(0, 123, 255, 0.1);
}

.payment-method-card.active {
    border-color: #007bff;
    background-color: #f8f9ff;
}

.payment-method-card input[type="radio"] {
    display: none;
}

.payment-method-label {
    display: flex;
    align-items: center;
    margin: 0;
    cursor: pointer;
}

.payment-icon {
    font-size: 2rem;
    margin-right: 15px;
    width: 50px;
    text-align: center;
}

.payment-info h6 {
    margin: 0;
    color: #333;
}

.order-item-image {
    width: 50px;
    height: 50px;
    object-fit: cover;
    border-radius: 8px;
}

.card {
    border: none;
    border-radius: 15px;
}

.card-header {
    border-radius: 15px 15px 0 0 !important;
    border: none;
}

.btn {
    border-radius: 10px;
}

.form-control {
    border-radius: 8px;
}

.form-control:focus {
    border-color: #007bff;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}

/* Shopee-style Address Styles */
.address-display-card {
    border: 1px solid #e9ecef;
    border-radius: 8px;
    padding: 16px;
    background-color: #fff;
    margin-top: 8px;
}

.no-address-card {
    border: 2px dashed #dee2e6;
    border-radius: 8px;
    background-color: #f8f9fa;
    margin-top: 8px;
}

.address-list {
    max-height: 400px;
    overflow-y: auto;
}

.address-option {
    border: 1px solid #e9ecef;
    border-radius: 8px;
    padding: 12px;
    margin-bottom: 12px;
    cursor: pointer;
    transition: all 0.3s ease;
}

.address-option:hover {
    border-color: #007bff;
    background-color: #f8f9ff;
}

.address-option .form-check-input:checked ~ .form-check-label {
    color: #007bff;
}

.address-option .form-check-input:checked ~ .form-check-label .address-details strong {
    color: #007bff;
}

.address-actions {
    opacity: 0;
    transition: opacity 0.3s ease;
}

.address-option:hover .address-actions {
    opacity: 1;
}

.address-actions .btn {
    margin-left: 5px;
    padding: 4px 8px;
}
</style>

<script>
// Voucher Modal Logic
document.addEventListener('DOMContentLoaded', function() {
    const voucherModal = document.getElementById('voucherModal');
    const voucherModalBody = document.getElementById('voucherModalBody');
    const applyVoucherBtn = document.getElementById('applyVoucherBtn');
    let selectedVoucherId = null;

    // Restore previously selected voucher from localStorage (if any) and update UI
    (async function restoreVoucherSelection() {
        try {
            const stored = localStorage.getItem('selected_voucher_id');
            if (stored) {
                document.getElementById('selected_voucher_id').value = stored;
                selectedVoucherId = stored;
                const url = '{{ route("vouchers.preview-apply") }}' + '?voucher_id=' + encodeURIComponent(stored) + '&amount=' + encodeURIComponent({{ (int) $total }});
                const res = await fetch(url, {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    credentials: 'same-origin'
                });
                if (res.ok) {
                    const data = await res.json();
                    if (data && data.ok) {
                        const discount = Math.round(data.discount || 0);
                        const newTotal = Math.round(data.new_total || {{ (int) $total }});
                        const newDeposit = Math.round(data.new_deposit || ({{ (int) $total }} * 0.3));
                        const fmt = (n) => n.toLocaleString('vi-VN') + ' đ';
                        document.getElementById('voucherDiscountText').textContent = '-' + fmt(discount);
                        document.getElementById('adjustedSubtotalText').textContent = fmt(newTotal);
                        document.getElementById('depositText').textContent = fmt(newDeposit);
                        document.getElementById('voucherDiscountRow').style.display = discount > 0 ? '' : 'none';
                        document.getElementById('adjustedSubtotalRow').style.display = discount > 0 ? '' : 'none';
                        const name = data.voucher?.name || 'Voucher đã chọn';
                        document.getElementById('selectedVoucherText').textContent = name;
                        document.getElementById('selectedVoucherRow').style.display = '';
                    }
                }
            }
        } catch (e) { console.warn('Restore voucher failed', e); }
    })();

    voucherModal.addEventListener('show.bs.modal', async function () {
        try {
            const previewUrl = '{{ route("vouchers.preview") }}' + '?amount=' + encodeURIComponent({{ (int) $total }});
            const response = await fetch(previewUrl, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin'
            });

            if (!response.ok) {
                const text = await response.text();
                console.error('Voucher preview failed:', response.status, text);
                voucherModalBody.innerHTML = '<div class="alert alert-danger">Không thể tải danh sách ưu đãi (mã ' + response.status + '). Vui lòng thử lại.</div>';
                return;
            }

            const data = await response.json();
            renderVouchers(data);
        } catch (error) {
            console.error('Error fetching vouchers:', error);
            voucherModalBody.innerHTML = '<div class="alert alert-danger">Không thể tải danh sách ưu đãi. Vui lòng thử lại.</div>';
        }
    });

    function renderVouchers(data) {
        let html = '';

        // VIP tier vouchers
        if (data.vip_tier_vouchers && data.vip_tier_vouchers.length) {
            html += '<div class="mb-3"><h6><i class="fas fa-crown text-warning me-1"></i> Ưu đãi VIP của bạn</h6>';
            data.vip_tier_vouchers.forEach(voucher => {
                html += `<div class="form-check">
                    <input class="form-check-input" type="radio" name="voucher_selection" value="${voucher.id}" id="voucher-${voucher.id}">
                    <label class="form-check-label" for="voucher-${voucher.id}">
                        <strong>${voucher.name}</strong>${voucher.description ? ' - ' + voucher.description : ''}
                    </label>
                </div>`;
            });
            html += '</div>';
        }

        // Tiered choices by group
        if (data.tiered_choices && Object.keys(data.tiered_choices).length > 0) {
            html += '<div class="mb-3"><h6><i class="fas fa-layer-group text-primary me-1"></i> Quà tặng theo giá trị đơn hàng</h6>';
            for (const group in data.tiered_choices) {
                html += `<p class="mb-1 text-muted"><strong>${group}</strong></p>`;
                data.tiered_choices[group].forEach(voucher => {
                    html += `<div class="form-check">
                        <input class="form-check-input" type="radio" name="voucher_selection" value="${voucher.id}" id="voucher-${voucher.id}">
                        <label class="form-check-label" for="voucher-${voucher.id}">
                            <strong>${voucher.name}</strong>${voucher.description ? ' - ' + voucher.description : ''}
                        </label>
                    </div>`;
                });
            }
            html += '</div>';
        }

        // Random gift availability
        if (data.random_gift_available) {
            html += `<div class="alert alert-warning d-flex align-items-center" role="alert">
                <i class="fas fa-dice me-2"></i>
                Bạn có thể nhận 1 quà ngẫu nhiên sau khi tạo đơn!
            </div>`;
        }

        if (!html) {
            html = '<div class="alert alert-info">Hiện không có ưu đãi nào dành cho bạn.</div>';
        }

        voucherModalBody.innerHTML = html;

        // Add event listeners for radio buttons
        document.querySelectorAll('input[name="voucher_selection"]').forEach(radio => {
            radio.addEventListener('change', function() {
                selectedVoucherId = this.value;
            });
        });

        // Preselect previously chosen voucher if any
        const prev = document.getElementById('selected_voucher_id')?.value;
        if (prev) {
            const el = document.querySelector(`input[name="voucher_selection"][value="${prev}"]`);
            if (el) {
                el.checked = true;
                selectedVoucherId = prev;
            }
        }
    }

    // Edit Address via AJAX
    const editForm = document.getElementById('editAddressForm');
    if (editForm) {
        editForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(editForm);
            fetch(editForm.action, {
                method: 'POST', // Laravel method spoofing
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            })
            .then(response => {
                if (!response.ok) return response.text().then(t => { throw new Error(t) });
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    const modal = bootstrap.Modal.getInstance(document.getElementById('editAddressModal'));
                    if (modal) modal.hide();
                    window.location.reload();
                } else {
                    alert(data.message || 'Không thể cập nhật địa chỉ');
                }
            })
            .catch(err => alert('Lỗi: ' + err.message));
        });
    }

    applyVoucherBtn.addEventListener('click', async function() {
        if (!selectedVoucherId) {
            alert('Vui lòng chọn một ưu đãi.');
            return;
        }

        try {
            const url = '{{ route("vouchers.preview-apply") }}' + '?voucher_id=' + encodeURIComponent(selectedVoucherId) + '&amount=' + encodeURIComponent({{ (int) $total }});
            const res = await fetch(url, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin'
            });
            const data = await res.json();
            if (!res.ok || !data.ok) {
                const msg = data.message || 'Voucher không áp dụng được.';
                alert(msg);
                return;
            }

            // Update hidden input to submit with form
            document.getElementById('selected_voucher_id').value = selectedVoucherId;
            localStorage.setItem('selected_voucher_id', String(selectedVoucherId));

            // Update UI totals
            const discount = Math.round(data.discount || 0);
            const newTotal = Math.round(data.new_total || {{ (int) $total }});
            const newDeposit = Math.round(data.new_deposit || ({{ (int) $total }} * 0.3));

            const fmt = (n) => n.toLocaleString('vi-VN') + ' đ';

            document.getElementById('voucherDiscountText').textContent = '-' + fmt(discount);
            document.getElementById('adjustedSubtotalText').textContent = fmt(newTotal);
            document.getElementById('depositText').textContent = fmt(newDeposit);
            document.getElementById('voucherDiscountRow').style.display = discount > 0 ? '' : 'none';
            document.getElementById('adjustedSubtotalRow').style.display = discount > 0 ? '' : 'none';

            // Show selected voucher summary (name). For gifts, discount == 0 but still show name
            const name = data.voucher?.name || 'Voucher đã chọn';
            document.getElementById('selectedVoucherText').textContent = name;
            document.getElementById('selectedVoucherRow').style.display = '';

            // Close modal
            bootstrap.Modal.getInstance(voucherModal).hide();
        } catch (e) {
            console.error(e);
            alert('Không thể áp dụng voucher.');
        }
    });
});

function selectPaymentMethod(method, el) {
    // Remove active class from all cards
    document.querySelectorAll('.payment-method-card').forEach(card => {
        card.classList.remove('active');
    });
    
    // Add active class to selected card
    if (el) {
        el.classList.add('active');
    } else {
        const input = document.getElementById(method);
        if (input) {
            const card = input.closest('.payment-method-card');
            if (card) card.classList.add('active');
        }
    }
    
    // Check the radio button
    const radio = document.getElementById(method);
    if (radio) radio.checked = true;
}

// Form validation and loading state
document.getElementById('paymentForm').addEventListener('submit', function(e) {
    const submitBtn = document.getElementById('submitBtn');
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang xử lý...';
    submitBtn.disabled = true;
    // Clear stored voucher after submit to avoid leaking to next session
    try { localStorage.removeItem('selected_voucher_id'); } catch(_) {}
});

// Auto-select first payment method on load
document.addEventListener('DOMContentLoaded', function() {
    // Initialize default selection without relying on event
    selectPaymentMethod('momo');
    
    // Add address form submission
    const addAddressForm = document.getElementById('addAddressForm');
    if (addAddressForm) {
        addAddressForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            fetch('{{ route("user.addresses.store") }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            })
            .then(response => {
                if (!response.ok) {
                    return response.text().then(text => {
                        console.error('Server response:', text);
                        throw new Error(`HTTP ${response.status}: ${text}`);
                    });
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    // Close modal and reload page
                    const modal = bootstrap.Modal.getInstance(document.getElementById('addAddressModal'));
                    if (modal) modal.hide();
                    window.location.reload();
                } else {
                    console.error('Server returned error:', data);
                    alert('Có lỗi xảy ra: ' + (data.message || 'Vui lòng thử lại.'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Có lỗi xảy ra: ' + error.message);
            });
        });
    }
});

// Confirm address selection from modal
function confirmAddressSelection() {
    const selectedRadio = document.querySelector('input[name="modal_address_selection"]:checked');
    if (selectedRadio) {
        const addressId = selectedRadio.value;
        const addressOption = document.querySelector(`[data-address-id="${addressId}"]`);
        
        if (addressOption) {
            const name = addressOption.querySelector('strong').textContent;
            const phone = addressOption.querySelector('.text-muted').textContent;
            const address = addressOption.querySelectorAll('.text-muted')[1].textContent;
            
            // Update display
            document.getElementById('displayName').textContent = name;
            document.getElementById('displayPhone').textContent = phone;
            document.getElementById('displayAddress').textContent = address;
            
            // Update hidden inputs used by OrderController::processPayment
            document.getElementById('selectedName').value = name;
            document.getElementById('selectedPhone').value = phone;
            document.getElementById('selectedAddress').value = address;
            const addressIdInput = document.getElementById('addressId');
            if (addressIdInput) addressIdInput.value = addressId;
            const useSavedAddressInput = document.getElementById('useSavedAddress');
            if (useSavedAddressInput) useSavedAddressInput.value = '1';
        }
    }
    
    // Close modal
    const modal = bootstrap.Modal.getInstance(document.getElementById('addressSelectionModal'));
    modal.hide();
}

// Edit address function
function editAddress(addressId) {
    // For now, just show alert - can be enhanced later
    alert('Chức năng chỉnh sửa địa chỉ sẽ được cập nhật trong phiên bản tiếp theo.');
}

// Delete address function
function deleteAddress(addressId) {
    if (confirm('Bạn có chắc chắn muốn xóa địa chỉ này?')) {
        fetch(`/user/addresses/${addressId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.reload();
            } else {
                alert('Có lỗi xảy ra khi xóa địa chỉ.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Có lỗi xảy ra.');
        });
    }
}
</script>
@endsection
