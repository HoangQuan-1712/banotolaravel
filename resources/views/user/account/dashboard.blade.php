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
                            <span class="badge bg-warning text-dark">{{ $user->tier->name }}</span>
                        @else
                            <span class="badge bg-secondary">Thành viên</span>
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
                </div>
                <div class="account-card-body">
                    @forelse($vouchers as $voucher)
                        <div class="voucher-item"> 
                            <p><strong>{{ $voucher->code }}</strong> - {{ $voucher->description }}</p>
                        </div>
                    @empty
                        <div class="empty-state">
                            <i class="fas fa-tags"></i>
                            <p>Bạn chưa có voucher nào.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modals -->
@include('user.account.partials.modals')

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
