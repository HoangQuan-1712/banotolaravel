<div class="list-group">
    <a href="{{ route('user.account.dashboard') }}" class="list-group-item list-group-item-action {{ request()->routeIs('user.account.dashboard') ? 'active' : '' }}">
        <i class="fas fa-tachometer-alt me-2"></i> Tổng quan
    </a>
    <a href="{{ route('user.account.profile') }}" class="list-group-item list-group-item-action {{ request()->routeIs('user.account.profile') ? 'active' : '' }}">
        <i class="fas fa-user-edit me-2"></i> Thông tin cá nhân
    </a>
    <a href="{{ route('user.account.addresses') }}" class="list-group-item list-group-item-action {{ request()->routeIs('user.account.addresses') ? 'active' : '' }}">
        <i class="fas fa-map-marker-alt me-2"></i> Sổ địa chỉ
    </a>
    <a href="{{ route('user.orders.index') }}" class="list-group-item list-group-item-action {{ request()->routeIs('user.orders.index') ? 'active' : '' }}">
        <i class="fas fa-history me-2"></i> Lịch sử đơn hàng
    </a>
    <a href="{{ route('user.account.vouchers') }}" class="list-group-item list-group-item-action {{ request()->routeIs('user.account.vouchers') ? 'active' : '' }}">
        <i class="fas fa-ticket-alt me-2"></i> Voucher của tôi
    </a>
</div>
