@extends('layouts.app')

@push('styles')
<style>
.voucher-card {
    transition: all 0.3s ease;
    border: 2px solid transparent;
    height: 100%;
}

.voucher-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
}

.voucher-card.selected {
    border-color: #007bff;
    background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
}

.tier-badge {
    position: absolute;
    top: 15px;
    right: 15px;
    padding: 5px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: bold;
    color: white;
}

.tier-bronze { background: #CD7F32; }
.tier-silver { background: #C0C0C0; color: #333; }
.tier-gold { background: #FFD700; color: #333; }
.tier-platinum { background: #E5E4E2; color: #333; }

.order-summary {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-radius: 15px;
    padding: 25px;
    margin-bottom: 30px;
}

.random-gift-section {
    background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
    border-radius: 15px;
    padding: 25px;
    text-align: center;
    margin: 30px 0;
}

.vip-section {
    background: linear-gradient(135deg, #d1ecf1 0%, #bee5eb 100%);
    border-radius: 15px;
    padding: 25px;
    margin: 30px 0;
}
</style>
@endpush

@section('content')
<div class="container py-4">
    <!-- Order Summary -->
    <div class="order-summary">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h4 class="mb-3">
                    <i class="fas fa-car text-primary"></i>
                    Your Car Purchase - Order #{{ $order->id }}
                </h4>
                <div class="row">
                    <div class="col-sm-6">
                        <strong>Total Value:</strong> ${{ number_format($order->total, 2) }}
                    </div>
                    <div class="col-sm-6">
                        <strong>Your Tier:</strong> 
                        @if($user->tier)
                            <span class="badge tier-{{ $user->tier->level }}">{{ $user->tier->name }}</span>
                        @else
                            <span class="badge bg-secondary">Standard Customer</span>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-md-4 text-end">
                <i class="fas fa-gift fa-3x text-warning"></i>
            </div>
        </div>
    </div>

    <!-- VIP Tier Exclusive Vouchers -->
    @if($vipTierVouchers->isNotEmpty())
    <div class="vip-section">
        <h4 class="mb-4">
            <i class="fas fa-crown text-warning"></i>
            Exclusive {{ $user->tier->name }} Benefits
        </h4>
        <div class="row">
            @foreach($vipTierVouchers as $voucher)
            <div class="col-md-6 mb-3">
                <div class="card voucher-card">
                    <div class="card-body position-relative">
                        <span class="tier-badge tier-{{ $voucher->tier_level }}">VIP EXCLUSIVE</span>
                        <h5 class="card-title">{{ $voucher->name }}</h5>
                        <p class="card-text">{{ $voucher->description }}</p>
                        @if($voucher->value)
                            <p class="text-success"><strong>Value: ${{ number_format($voucher->value, 2) }}</strong></p>
                        @endif
                        <form method="POST" action="{{ route('vouchers.apply', $order) }}" class="d-inline">
                            @csrf
                            <input type="hidden" name="voucher_id" value="{{ $voucher->id }}">
                            <button type="submit" class="btn btn-warning">
                                <i class="fas fa-crown"></i> Claim VIP Benefit
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Tiered Choice Vouchers -->
    @if($tieredChoices->isNotEmpty())
    <div class="mb-5">
        <h4 class="mb-4">
            <i class="fas fa-layer-group text-primary"></i>
            Choose Your Premium Gift
            <small class="text-muted">(Select one from each tier you qualify for)</small>
        </h4>
        
        @foreach($tieredChoices as $groupCode => $vouchers)
            @php
                $minValue = $vouchers->min('min_order_value');
                $maxValue = $vouchers->max('max_order_value');
            @endphp
            <div class="mb-4">
                <h5 class="text-primary">
                    {{ $groupCode ? ucfirst(str_replace('_', ' ', $groupCode)) : 'Premium Tier' }}
                    <small class="text-muted">
                        (For orders ${{ number_format($minValue, 0) }}K - ${{ number_format($maxValue/1000, 0) }}K)
                    </small>
                </h5>
                
                <form method="POST" action="{{ route('vouchers.apply', $order) }}">
                    @csrf
                    <div class="row">
                        @foreach($vouchers as $voucher)
                        <div class="col-md-4 mb-3">
                            <div class="card voucher-card" onclick="selectVoucher(this, {{ $voucher->id }})">
                                <div class="card-body position-relative">
                                    <div class="form-check position-absolute" style="top: 15px; left: 15px;">
                                        <input class="form-check-input" type="radio" name="voucher_id" value="{{ $voucher->id }}" id="voucher_{{ $voucher->id }}">
                                    </div>
                                    
                                    <h5 class="card-title mt-3">{{ $voucher->name }}</h5>
                                    <p class="card-text">{{ $voucher->description }}</p>
                                    
                                    @if($voucher->value)
                                        <div class="text-success mb-2">
                                            <strong>Value: ${{ number_format($voucher->value, 2) }}</strong>
                                        </div>
                                    @endif
                                    
                                    <div class="text-muted small">
                                        <i class="fas fa-info-circle"></i>
                                        @if($voucher->metadata && isset($voucher->metadata['duration']))
                                            Duration: {{ $voucher->metadata['duration'] }}
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    
                    <div class="text-center mt-3">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-check"></i> Confirm Selection
                        </button>
                    </div>
                </form>
            </div>
        @endforeach
    </div>
    @endif

    <!-- Random Gift Section -->
    @if($randomGift)
    <div class="random-gift-section">
        <h4 class="mb-3">
            <i class="fas fa-dice text-warning"></i>
            Feeling Lucky?
        </h4>
        <p class="lead">Try your luck for a surprise gift! You might win something amazing!</p>
        <form method="POST" action="{{ route('vouchers.random', $order) }}">
            @csrf
            <button type="submit" class="btn btn-warning btn-lg">
                <i class="fas fa-gift"></i> Spin for Random Gift!
            </button>
        </form>
        <small class="text-muted d-block mt-2">
            * Random gifts include premium car accessories, service vouchers, and exclusive experiences
        </small>
    </div>
    @endif

    <!-- No Vouchers Available -->
    @if($tieredChoices->isEmpty() && !$randomGift && $vipTierVouchers->isEmpty())
    <div class="text-center py-5">
        <i class="fas fa-gift fa-4x text-muted mb-3"></i>
        <h4>No Special Offers Available</h4>
        <p class="text-muted">
            Complete more purchases to unlock exclusive VIP benefits and special offers!
        </p>
        <a href="{{ route('user.orders.show', $order) }}" class="btn btn-primary">
            Continue to Order Details
        </a>
    </div>
    @endif

    <!-- Back to Order -->
    <div class="text-center mt-4">
        <a href="{{ route('user.orders.show', $order) }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Quay lại chi tiết đơn hàng
        </a>
        <a href="{{ route('user.payment.index', ['order_id' => $order->id]) }}" class="btn btn-primary">
            <i class="fas fa-credit-card"></i> Đi đến thanh toán
        </a>
    </div>
</div>

<script>
function selectVoucher(card, voucherId) {
    // Remove selected class from all cards in the same group
    const group = card.closest('form');
    group.querySelectorAll('.voucher-card').forEach(c => c.classList.remove('selected'));
    
    // Add selected class to clicked card
    card.classList.add('selected');
    
    // Check the radio button
    const radio = card.querySelector('input[type="radio"]');
    radio.checked = true;
}
</script>
@endsection
