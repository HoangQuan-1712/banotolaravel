@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <h4>{{ isset($voucher) ? 'Sửa Voucher' : 'Tạo Voucher' }}</h4>

    <div class="card">
        <div class="card-body">
            <form action="{{ isset($voucher) ? route('admin.vouchers.update', $voucher) : route('admin.vouchers.store') }}" method="POST">
                @csrf
                @if(isset($voucher))
                    @method('PUT')
                @endif

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label>Tên Voucher</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name', $voucher->name ?? '') }}" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label>Mã Voucher</label>
                        <input type="text" name="code" class="form-control" value="{{ old('code', $voucher->code ?? '') }}" required>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label>Loại</label>
                        <select name="type" class="form-select">
                            <option value="tiered_choice" {{ old('type', $voucher->type ?? '') == 'tiered_choice' ? 'selected' : '' }}>Tiered Choice</option>
                            <option value="random_gift" {{ old('type', $voucher->type ?? '') == 'random_gift' ? 'selected' : '' }}>Random Gift</option>
                            <option value="vip_tier" {{ old('type', $voucher->type ?? '') == 'vip_tier' ? 'selected' : '' }}>VIP Tier</option>
                            <option value="discount" {{ old('type', $voucher->type ?? '') == 'discount' ? 'selected' : '' }}>Discount</option>
                            <option value="service_voucher" {{ old('type', $voucher->type ?? '') == 'service_voucher' ? 'selected' : '' }}>Service Voucher</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label>Giá trị (nếu có)</label>
                        <input type="number" step="0.01" name="value" class="form-control" value="{{ old('value', $voucher->value ?? '') }}">
                    </div>
                </div>

                <div class="mb-3">
                    <label>Mô tả</label>
                    <textarea name="description" class="form-control">{{ old('description', $voucher->description ?? '') }}</textarea>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label>Ngày bắt đầu</label>
                        <input type="date" name="start_date" class="form-control" value="{{ old('start_date', isset($voucher) ? $voucher->start_date?->format('Y-m-d') : '') }}">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label>Ngày kết thúc</label>
                        <input type="date" name="end_date" class="form-control" value="{{ old('end_date', isset($voucher) ? $voucher->end_date?->format('Y-m-d') : '') }}">
                    </div>
                </div>

                <div class="form-check mb-3">
                    <input type="hidden" name="active" value="0">
                    <input class="form-check-input" type="checkbox" name="active" value="1" id="activeCheck" {{ old('active', $voucher->active ?? false) ? 'checked' : '' }}>
                    <label class="form-check-label" for="activeCheck">Hoạt động</label>
                </div>

                <button type="submit" class="btn btn-primary">{{ isset($voucher) ? 'Cập nhật' : 'Lưu' }}</button>
            </form>
        </div>
    </div>
</div>
@endsection
