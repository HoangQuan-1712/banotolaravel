@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/account-dashboard.css') }}">
@endpush

@section('content')
<div class="container account-container">
    <h1 class="account-header">Quản lý tài khoản</h1>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row">
        <!-- Profile Card -->
        <div class="col-lg-4">
            <div class="account-card profile-card">
                <div class="account-card-body profile-card-body">
                    <div class="profile-avatar">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </div>
                    <h4 class="profile-name">{{ $user->name }}</h4>
                    <p class="profile-email">{{ $user->email }}</p>
                    <div class="profile-tier">
                        Hạng:
                        @if($user->tier)
                            <span role="button" class="badge bg-warning text-dark" data-bs-toggle="modal" data-bs-target="#tierProgressModal" title="Xem tiến trình hạng" style="cursor: pointer;">
                                {{ $user->tier->name }}
                            </span>
                            <small class="d-block mt-1">
                                <a href="#" data-bs-toggle="modal" data-bs-target="#tierProgressModal">Xem chi tiết hạng</a>
                            </small>
                        @else
                            <span role="button" class="badge bg-secondary" data-bs-toggle="modal" data-bs-target="#tierProgressModal" title="Xem tiến trình hạng" style="cursor: pointer;">Thành viên</span>
                            <small class="d-block mt-1">
                                <a href="#" data-bs-toggle="modal" data-bs-target="#tierProgressModal">Xem cách lên hạng</a>
                            </small>
                        @endif
                    </div>
                    <div class="profile-actions">
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#editProfileModal">Chỉnh sửa thông tin</button>
                        <button class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#changePasswordModal">Đổi mật khẩu</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="col-lg-8">
            <!-- Address Card -->
            <div class="account-card">
                <div class="account-card-header">
                    <h5><i class="fas fa-map-marker-alt"></i> Sổ địa chỉ</h5>
                    <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addressModal">Thêm địa chỉ mới</button>
                </div>
                <div class="account-card-body">
                    @forelse($addresses as $address)
                        <div class="address-item {{ $address->is_default ? 'is-default' : '' }}">
                            @if($address->is_default)
                                <span class="badge bg-primary default-badge">Mặc định</span>
                            @endif
                            <strong>{{ $address->name }}</strong>
                            <p class="mb-1 text-muted">{{ $address->full_address }}</p>
                            <p class="mb-0"><i class="fas fa-phone text-secondary me-1"></i>{{ $address->phone }}</p>
                            <div class="address-actions mt-2">
                                <a href="#" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#addressModal-{{$address->id}}">Sửa</a>
                                <form action="{{ route('user.addresses.destroy', $address) }}" method="POST" class="d-inline ms-1 js-address-delete" data-id="{{ $address->id }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Xóa</button>
                                </form>
                                @if(!$address->is_default)
                                <form action="{{ route('user.addresses.set-default', $address) }}" method="POST" class="d-inline ms-1 js-address-set-default" data-id="{{ $address->id }}">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-sm btn-outline-success">Đặt làm mặc định</button>
                                </form>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="empty-state">
                            <i class="fas fa-map-signs"></i>
                            <p>Bạn chưa có địa chỉ nào được lưu.</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Voucher Card -->
            <div class="account-card mt-4">
                <div class="account-card-header">
                    <h5><i class="fas fa-ticket-alt"></i> Voucher của bạn</h5>
                    <small class="text-muted d-block">Voucher đã dùng sẽ tự ẩn khỏi danh sách này.</small>
                </div>
                <div class="account-card-body">
                    @php
                        $hasAny = collect($vouchers['vip'] ?? [])->isNotEmpty()
                                 || collect($vouchers['discount'] ?? [])->isNotEmpty()
                                 || collect($vouchers['tiered_choice'] ?? [])->isNotEmpty()
                                 || collect($vouchers['random_gift'] ?? [])->isNotEmpty();
                    @endphp

                    @if(!$hasAny)
                        <div class="empty-state">
                            <i class="fas fa-tags"></i>
                            <p>Bạn chưa có voucher nào khả dụng.</p>
                        </div>
                    @else
                        {{-- VIP vouchers --}}
                        @if(collect($vouchers['vip'] ?? [])->isNotEmpty())
                            <h6 class="mt-2"><i class="fas fa-crown text-warning"></i> Ưu đãi VIP</h6>
                            @foreach($vouchers['vip'] as $voucher)
                                <div class="voucher-item py-2 border-bottom">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <strong>{{ $voucher->name ?? $voucher->code }}</strong>
                                            @if($voucher->code)
                                                <span class="badge bg-secondary ms-1">{{ $voucher->code }}</span>
                                            @endif
                                            @if($voucher->description)
                                                <div class="text-muted small">{{ $voucher->description }}</div>
                                            @endif
                                            <div class="small mt-1">
                                                <span class="badge bg-light text-dark">Loại: VIP</span>
                                                @if($voucher->tier_level)
                                                    <span class="badge bg-info text-dark">Hạng: {{ $voucher->tier_level }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @endif

                        {{-- Discount vouchers --}}
                        @if(collect($vouchers['discount'] ?? [])->isNotEmpty())
                            <h6 class="mt-3"><i class="fas fa-percent text-success"></i> Giảm giá trực tiếp</h6>
                            @foreach($vouchers['discount'] as $voucher)
                                <div class="voucher-item py-2 border-bottom">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <strong>{{ $voucher->name ?? $voucher->code }}</strong>
                                            @if($voucher->code)
                                                <span class="badge bg-secondary ms-1">{{ $voucher->code }}</span>
                                            @endif
                                            @if($voucher->description)
                                                <div class="text-muted small">{{ $voucher->description }}</div>
                                            @endif
                                            <div class="small mt-1">
                                                <span class="badge bg-light text-dark">Loại: Discount</span>
                                                @if(!is_null($voucher->value))
                                                    <span class="badge bg-success">Giá trị: ${{ number_format((float)$voucher->value, 0, ',', '.') }}</span>
                                                @endif
                                                @if($voucher->min_order_value)
                                                    <span class="badge bg-primary">ĐH tối thiểu: ${{ number_format($voucher->min_order_value, 0, ',', '.') }}</span>
                                                @endif
                                                @if($voucher->max_order_value)
                                                    <span class="badge bg-primary">ĐH tối đa: ${{ number_format($voucher->max_order_value, 0, ',', '.') }}</span>
                                                @endif
                                                @if(!empty($voucher->applicable_categories))
                                                    <span class="badge bg-dark">Danh mục: {{ implode(', ', $voucher->applicable_categories) }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @endif

                        {{-- Tiered choice vouchers --}}
                        @if(collect($vouchers['tiered_choice'] ?? [])->isNotEmpty())
                            <h6 class="mt-3"><i class="fas fa-layer-group text-primary"></i> Quà theo bậc giá</h6>
                            @foreach($vouchers['tiered_choice'] as $voucher)
                                <div class="voucher-item py-2 border-bottom">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <strong>{{ $voucher->name ?? $voucher->code }}</strong>
                                            @if($voucher->code)
                                                <span class="badge bg-secondary ms-1">{{ $voucher->code }}</span>
                                            @endif
                                            @if($voucher->description)
                                                <div class="text-muted small">{{ $voucher->description }}</div>
                                            @endif
                                            <div class="small mt-1">
                                                <span class="badge bg-light text-dark">Loại: Quà tặng theo bậc</span>
                                                @if($voucher->min_order_value)
                                                    <span class="badge bg-primary">ĐH tối thiểu: ${{ number_format($voucher->min_order_value, 0, ',', '.') }}</span>
                                                @endif
                                                @if($voucher->max_order_value)
                                                    <span class="badge bg-primary">ĐH tối đa: ${{ number_format($voucher->max_order_value, 0, ',', '.') }}</span>
                                                @endif
                                                @if(!empty($voucher->applicable_categories))
                                                    <span class="badge bg-dark">Danh mục: {{ implode(', ', $voucher->applicable_categories) }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @endif

                        {{-- Random gift vouchers --}}
                        @if(collect($vouchers['random_gift'] ?? [])->isNotEmpty())
                            <h6 class="mt-3"><i class="fas fa-dice text-warning"></i> Quà tặng ngẫu nhiên</h6>
                            @foreach($vouchers['random_gift'] as $voucher)
                                <div class="voucher-item py-2 border-bottom">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <strong>{{ $voucher->name ?? $voucher->code }}</strong>
                                            @if($voucher->code)
                                                <span class="badge bg-secondary ms-1">{{ $voucher->code }}</span>
                                            @endif
                                            @if($voucher->description)
                                                <div class="text-muted small">{{ $voucher->description }}</div>
                                            @endif
                                            <div class="small mt-1">
                                                <span class="badge bg-light text-dark">Loại: Quà ngẫu nhiên</span>
                                                @if(!is_null($voucher->value))
                                                    <span class="badge bg-success">Giá trị ước tính: ${{ number_format((float)$voucher->value, 0, ',', '.') }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modals -->
@include('user.account.partials.modals')

{{-- Tier Progress Modal --}}
@include('user.account.modals.tier-progress')

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    function handleAjaxForm(form, onSuccessMessage = 'Thao tác thành công') {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const action = form.getAttribute('action');
            const methodInput = form.querySelector('input[name="_method"]');
            const method = methodInput ? methodInput.value : (form.getAttribute('method') || 'POST');
            const formData = new FormData(form);

            fetch(action, {
                method: method.toUpperCase(),
                headers: {
                    'X-CSRF-TOKEN': csrf,
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(async (res) => {
                if (!res.ok) {
                    let msg = 'Có lỗi xảy ra';
                    try { const data = await res.json(); msg = data.message || JSON.stringify(data.errors || {}); } catch (_) {}
                    throw new Error(msg);
                }
                return res.json();
            })
            .then(() => {
                // Đóng modal nếu có và reload để đồng bộ danh sách
                const modalEl = form.closest('.modal');
                if (modalEl) {
                    const modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
                    modal.hide();
                }
                window.location.reload();
            })
            .catch(err => {
                alert(err.message || 'Không thể xử lý yêu cầu.');
            });
        });
    }

    // Add address form in account page
    const addForm = document.getElementById('addAddressFormAccount');
    if (addForm) handleAjaxForm(addForm);

    // Edit address forms
    document.querySelectorAll('.js-edit-address-form').forEach(f => handleAjaxForm(f));

    // Delete address
    document.querySelectorAll('.js-address-delete').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            if (!confirm('Bạn có chắc muốn xóa địa chỉ này?')) return;
            fetch(form.action, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                body: new FormData(form)
            }).then(res => {
                if (!res.ok) throw new Error('Xóa địa chỉ thất bại');
                return res.json();
            }).then(() => window.location.reload())
              .catch(err => alert(err.message));
        });
    });

    // Set default address
    document.querySelectorAll('.js-address-set-default').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            fetch(form.action, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                body: new FormData(form)
            }).then(res => {
                if (!res.ok) throw new Error('Không thể đặt mặc định');
                return res.json();
            }).then(() => window.location.reload())
              .catch(err => alert(err.message));
        });
    });
});
</script>
@endpush
