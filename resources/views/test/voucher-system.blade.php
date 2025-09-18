@extends('layouts.app')

@push('styles')
<style>
.test-section {
    background: white;
    border-radius: 15px;
    padding: 25px;
    margin-bottom: 20px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.tier-demo {
    display: flex;
    gap: 15px;
    flex-wrap: wrap;
}

.tier-card {
    flex: 1;
    min-width: 200px;
    padding: 20px;
    border-radius: 10px;
    text-align: center;
    color: white;
    font-weight: bold;
}

.tier-bronze { background: linear-gradient(135deg, #CD7F32, #B8860B); }
.tier-silver { background: linear-gradient(135deg, #C0C0C0, #A9A9A9); color: #333; }
.tier-gold { background: linear-gradient(135deg, #FFD700, #FFA500); color: #333; }
.tier-platinum { background: linear-gradient(135deg, #E5E4E2, #D3D3D3); color: #333; }

.voucher-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 15px;
}

.voucher-card {
    border: 2px solid #e9ecef;
    border-radius: 10px;
    padding: 15px;
    background: white;
}

.voucher-tiered { border-color: #007bff; }
.voucher-random { border-color: #ffc107; }
.voucher-vip { border-color: #28a745; }

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 15px;
}

.stat-card {
    background: linear-gradient(135deg, #f8f9fa, #e9ecef);
    padding: 20px;
    border-radius: 10px;
    text-align: center;
}

.stat-number {
    font-size: 2rem;
    font-weight: bold;
    color: #007bff;
}
</style>
@endpush

@section('content')
<div class="container py-4">
    <div class="text-center mb-4">
        <h1>🎯 Car Dealership Voucher System</h1>
        <p class="lead">Kiểm tra hệ thống hạng khách hàng và voucher</p>
    </div>

    <!-- Customer Tiers -->
    <div class="test-section">
        <h3><i class="fas fa-crown text-warning"></i> Customer Tiers</h3>
        <div class="tier-demo">
            @foreach(\App\Models\CustomerTier::orderBy('min_spent')->get() as $tier)
            <div class="tier-card tier-{{ $tier->level }}">
                <h5>{{ $tier->name }}</h5>
                <div>Min: ${{ number_format($tier->min_spent) }}</div>
                <div>Discount: {{ $tier->discount_percentage }}%</div>
                <div>Users: {{ $tier->users()->count() }}</div>
            </div>
            @endforeach
        </div>
    </div>

    <!-- Statistics -->
    <div class="test-section">
        <h3><i class="fas fa-chart-bar text-primary"></i> System Statistics</h3>
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number">{{ \App\Models\CustomerTier::count() }}</div>
                <div>Customer Tiers</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">{{ \App\Models\Voucher::count() }}</div>
                <div>Total Vouchers</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">{{ \App\Models\Voucher::where('type', 'tiered_choice')->count() }}</div>
                <div>Tiered Choice</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">{{ \App\Models\Voucher::where('type', 'random_gift')->count() }}</div>
                <div>Random Gift</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">{{ \App\Models\Voucher::where('type', 'vip_tier')->count() }}</div>
                <div>VIP Tier</div>
            </div>
        </div>
    </div>

    <!-- Tiered Choice Vouchers -->
    <div class="test-section">
        <h3><i class="fas fa-layer-group text-primary"></i> Tiered Choice Vouchers (Upselling)</h3>
        <p class="text-muted">Khuyến khích khách mua xe đắt hơn để được quà tốt hơn</p>
        <div class="voucher-grid">
            @foreach(\App\Models\Voucher::where('type', 'tiered_choice')->get() as $voucher)
            <div class="voucher-card voucher-tiered">
                <h5>{{ $voucher->name }}</h5>
                <p class="small">{{ $voucher->description }}</p>
                <div class="d-flex justify-content-between">
                    <span class="badge bg-primary">{{ $voucher->group_code }}</span>
                    <span class="fw-bold">${{ number_format($voucher->value) }}</span>
                </div>
                <div class="small text-muted mt-2">
                    Range: ${{ number_format($voucher->min_order_value) }} - ${{ number_format($voucher->max_order_value ?? 999999) }}
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <!-- Random Gift Vouchers -->
    <div class="test-section">
        <h3><i class="fas fa-dice text-warning"></i> Random Gift Vouchers (Marketing Buzz)</h3>
        <p class="text-muted">Tạo sự thích thú và buzz marketing</p>
        <div class="voucher-grid">
            @foreach(\App\Models\Voucher::where('type', 'random_gift')->get() as $voucher)
            <div class="voucher-card voucher-random">
                <h5>{{ $voucher->name }}</h5>
                <p class="small">{{ $voucher->description }}</p>
                <div class="d-flex justify-content-between">
                    <span class="badge bg-warning">Weight: {{ $voucher->weight }}</span>
                    <span class="fw-bold">${{ number_format($voucher->value) }}</span>
                </div>
                <div class="small text-muted mt-2">
                    Probability: {{ round(($voucher->weight / \App\Models\Voucher::where('type', 'random_gift')->sum('weight')) * 100, 1) }}%
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <!-- VIP Tier Vouchers -->
    <div class="test-section">
        <h3><i class="fas fa-crown text-success"></i> VIP Tier Vouchers (Loyalty Program)</h3>
        <p class="text-muted">Ưu đãi độc quyền cho khách hàng VIP</p>
        <div class="voucher-grid">
            @foreach(\App\Models\Voucher::where('type', 'vip_tier')->get() as $voucher)
            <div class="voucher-card voucher-vip">
                <h5>{{ $voucher->name }}</h5>
                <p class="small">{{ $voucher->description }}</p>
                <div class="d-flex justify-content-between">
                    <span class="badge bg-success tier-{{ $voucher->tier_level }}">{{ strtoupper($voucher->tier_level) }}</span>
                    <span class="fw-bold">{{ $voucher->value ? '$' . number_format($voucher->value) : 'Priceless' }}</span>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <!-- Current User Status -->
    @auth
    <div class="test-section">
        <h3><i class="fas fa-user text-info"></i> Your Current Status</h3>
        <div class="row">
            <div class="col-md-6">
                <div class="tier-card {{ auth()->user()->tier ? 'tier-' . auth()->user()->tier->level : 'tier-bronze' }}">
                    <h5>{{ auth()->user()->tier ? auth()->user()->tier->name : 'Standard Customer' }}</h5>
                    <div>Lifetime Spent: ${{ number_format(auth()->user()->lifetime_spent ?? 0) }}</div>
                    <div>Cars Bought: {{ auth()->user()->total_cars_bought ?? 0 }}</div>
                </div>
            </div>
            <div class="col-md-6">
                @php
                    $currentSpent = auth()->user()->lifetime_spent ?? 0;
                    $nextTier = \App\Models\CustomerTier::where('min_spent', '>', $currentSpent)->orderBy('min_spent')->first();
                @endphp
                @if($nextTier)
                <div class="card">
                    <div class="card-body">
                        <h6>Progress to {{ $nextTier->name }}</h6>
                        <div class="progress mb-2">
                            <div class="progress-bar" style="width: {{ min(100, ($currentSpent / $nextTier->min_spent) * 100) }}%"></div>
                        </div>
                        <small class="text-muted">
                            ${{ number_format($nextTier->min_spent - $currentSpent) }} more needed
                        </small>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
    @endauth

    <!-- Test Links -->
    <div class="test-section">
        <h3><i class="fas fa-tools text-secondary"></i> Test Links</h3>
        <div class="row">
            <div class="col-md-4">
                <a href="/dashboard" class="btn btn-primary w-100 mb-2">
                    <i class="fas fa-tachometer-alt"></i> User Dashboard
                </a>
            </div>
            <div class="col-md-4">
                <a href="/admin/chats" class="btn btn-success w-100 mb-2">
                    <i class="fas fa-comments"></i> Admin Chat
                </a>
            </div>
            <div class="col-md-4">
                <a href="/products" class="btn btn-info w-100 mb-2">
                    <i class="fas fa-car"></i> Browse Cars
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
