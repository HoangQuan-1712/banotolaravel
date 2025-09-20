@php
    // Đặt giá trị mặc định nếu biến $address không tồn tại (cho form thêm mới)
    $addressName = isset($address) ? $address->name : '';
    $addressLine1 = isset($address) ? $address->address_line_1 : '';
    $addressLine2 = isset($address) ? $address->address_line_2 : '';
    $addressCity = isset($address) ? $address->city : '';
    $addressDistrict = isset($address) ? $address->district : '';
    $addressWard = isset($address) ? $address->ward : '';
    $addressPostalCode = isset($address) ? $address->postal_code : '';
    $addressPhone = isset($address) ? $address->phone : '';
    $addressIsDefault = isset($address) ? (bool)$address->is_default : false;
@endphp

<div class="mb-3">
    <label for="name" class="form-label">Tên người nhận</label>
    <input type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name', $addressName) }}" required>
    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

<div class="mb-3">
    <label for="phone" class="form-label">Số điện thoại</label>
    <input type="text" class="form-control @error('phone') is-invalid @enderror" name="phone" value="{{ old('phone', $addressPhone) }}" required>
    @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

<div class="mb-3">
    <label for="address_line_1" class="form-label">Địa chỉ</label>
    <input type="text" class="form-control @error('address_line_1') is-invalid @enderror" name="address_line_1" value="{{ old('address_line_1', $addressLine1) }}" placeholder="Ví dụ: 123 Đường ABC" required>
    @error('address_line_1')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

<div class="mb-3">
    <label for="address_line_2" class="form-label">Địa chỉ chi tiết (Tùy chọn)</label>
    <input type="text" class="form-control @error('address_line_2') is-invalid @enderror" name="address_line_2" value="{{ old('address_line_2', $addressLine2) }}" placeholder="Ví dụ: Tòa nhà XYZ, Tầng 5, Phòng 502">
    @error('address_line_2')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

<div class="row">
    <div class="col-md-4 mb-3">
        <label for="ward" class="form-label">Phường/Xã</label>
        <input type="text" class="form-control @error('ward') is-invalid @enderror" name="ward" value="{{ old('ward', $addressWard) }}" required>
        @error('ward')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-4 mb-3">
        <label for="district" class="form-label">Quận/Huyện</label>
        <input type="text" class="form-control @error('district') is-invalid @enderror" name="district" value="{{ old('district', $addressDistrict) }}" required>
        @error('district')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-4 mb-3">
        <label for="city" class="form-label">Tỉnh/Thành phố</label>
        <input type="text" class="form-control @error('city') is-invalid @enderror" name="city" value="{{ old('city', $addressCity) }}" required>
        @error('city')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <label for="postal_code" class="form-label">Mã bưu chính (tùy chọn)</label>
        <input type="text" class="form-control @error('postal_code') is-invalid @enderror" name="postal_code" value="{{ old('postal_code', $addressPostalCode) }}">
        @error('postal_code')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6 d-flex align-items-center">
        <div class="form-check mt-4">
            <input class="form-check-input" type="checkbox" name="is_default" id="is_default" value="1" {{ old('is_default', $addressIsDefault) ? 'checked' : '' }}>
            <label class="form-check-label" for="is_default">
                Đặt làm địa chỉ mặc định
            </label>
        </div>
    </div>
</div>
