@extends('layouts.app')

@push('styles')
<style>
.tier-card {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-radius: 15px;
    padding: 25px;
    margin-bottom: 20px;
    position: relative;
    overflow: hidden;
}

.tier-bronze { background: linear-gradient(135deg, #CD7F32 0%, #B8860B 100%); }
.tier-silver { background: linear-gradient(135deg, #C0C0C0 0%, #A9A9A9 100%); }
.tier-gold { background: linear-gradient(135deg, #FFD700 0%, #FFA500 100%); }
.tier-platinum { background: linear-gradient(135deg, #E5E4E2 0%, #D3D3D3 100%); }

.tier-icon {
    position: absolute;
    top: 20px;
    right: 20px;
    font-size: 3rem;
    opacity: 0.3;
}

.progress-bar-custom {
    height: 8px;
    border-radius: 10px;
    background: rgba(255,255,255,0.3);
    overflow: hidden;
}

.progress-fill {
    height: 100%;
    background: linear-gradient(90deg, #28a745, #20c997);
    border-radius: 10px;
    transition: width 0.8s ease;
}

.stats-card {
    background: white;
    border-radius: 10px;
    padding: 20px;
    text-align: center;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}
</style>
@endpush

@section('content')
<div class="container py-4">
    <div class="row">
        <div class="col-md-8">
            <!-- Tier Status Card -->
            <div class="tier-card {{ auth()->user()->tier ? 'tier-' . auth()->user()->tier->level : '' }}">
                @if(auth()->user()->tier)
                    <i class="fas fa-crown tier-icon"></i>
                    <h3 class="text-white mb-3">
                        {{ auth()->user()->tier->name }}
                        <span class="badge bg-light text-dark ms-2">{{ strtoupper(auth()->user()->tier->level) }}</span>
                    </h3>
                    <p class="text-white mb-4">{{ auth()->user()->tier->benefits }}</p>
                @else
                    <i class="fas fa-user tier-icon"></i>
                    <h3 class="mb-3">Standard Customer</h3>
                    <p class="text-muted mb-4">Start your journey with us! Purchase your first car to unlock VIP benefits.</p>
                @endif

                <!-- Progress to Next Tier -->
                @php
                    $currentSpent = auth()->user()->lifetime_spent;
                    $nextTier = \App\Models\CustomerTier::where('min_spent', '>', $currentSpent)
                                                        ->orderBy('min_spent')
                                                        ->first();
                @endphp
                
                @if($nextTier)
                    <div class="mt-4">
                        <div class="d-flex justify-content-between text-white mb-2">
                            <span>Progress to {{ $nextTier->name }}</span>
                            <span>${{ number_format($currentSpent, 0) }} / ${{ number_format($nextTier->min_spent, 0) }}</span>
                        </div>
                        <div class="progress-bar-custom">
                            <div class="progress-fill" style="width: {{ min(100, ($currentSpent / $nextTier->min_spent) * 100) }}%"></div>
                        </div>
                        <small class="text-white-50 mt-2 d-block">
                            ${{ number_format($nextTier->min_spent - $currentSpent, 0) }} more to unlock {{ $nextTier->name }}
                        </small>
                    </div>
                @endif
            </div>

            <!-- Recent Orders -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-car text-primary"></i>
                        Your Recent Car Purchases
                    </h5>
                </div>
                <div class="card-body">
                    @forelse(auth()->user()->orders()->latest()->take(5)->get() as $order)
                        <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                            <div>
                                <strong>Order #{{ $order->id }}</strong>
                                <br>
                                <small class="text-muted">{{ $order->created_at->format('M d, Y') }}</small>
                            </div>
                            <div class="text-end">
                                <div class="fw-bold">${{ number_format($order->total, 2) }}</div>
                                <span class="badge bg-{{ $order->status === 'completed' ? 'success' : 'warning' }}">
                                    {{ ucfirst($order->status) }}
                                </span>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-4">
                            <i class="fas fa-car fa-3x text-muted mb-3"></i>
                            <h5>No Car Purchases Yet</h5>
                            <p class="text-muted">Browse our collection and find your perfect car!</p>
                            <a href="{{ route('products.index') }}" class="btn btn-primary">
                                <i class="fas fa-search"></i> Browse Cars
                            </a>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <!-- Stats Cards -->
            <div class="row">
                <div class="col-12 mb-3">
                    <div class="stats-card">
                        <i class="fas fa-dollar-sign fa-2x text-success mb-2"></i>
                        <h4>${{ number_format(auth()->user()->lifetime_spent, 2) }}</h4>
                        <small class="text-muted">Total Spent</small>
                    </div>
                </div>
                
                <div class="col-12 mb-3">
                    <div class="stats-card">
                        <i class="fas fa-car fa-2x text-primary mb-2"></i>
                        <h4>{{ auth()->user()->total_cars_bought ?? 0 }}</h4>
                        <small class="text-muted">Cars Purchased</small>
                    </div>
                </div>

                @if(auth()->user()->tier)
                <div class="col-12 mb-3">
                    <div class="stats-card">
                        <i class="fas fa-percentage fa-2x text-warning mb-2"></i>
                        <h4>{{ auth()->user()->tier->discount_percentage }}%</h4>
                        <small class="text-muted">Service Discount</small>
                    </div>
                </div>
                @endif
            </div>

            <!-- VIP Benefits -->
            @if(auth()->user()->tier)
            <div class="card">
                <div class="card-header bg-warning text-dark">
                    <h6 class="mb-0">
                        <i class="fas fa-crown"></i>
                        Your VIP Benefits
                    </h6>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2">
                            <i class="fas fa-check text-success"></i>
                            {{ auth()->user()->tier->discount_percentage }}% service discount
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success"></i>
                            Priority support (Level {{ auth()->user()->tier->priority_support }})
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success"></i>
                            Exclusive VIP vouchers
                        </li>
                        <li>
                            <i class="fas fa-check text-success"></i>
                            Special event invitations
                        </li>
                    </ul>
                </div>
            </div>
            @else
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-star"></i>
                        Unlock VIP Benefits
                    </h6>
                </div>
                <div class="card-body">
                    <p class="small text-muted">Purchase cars to unlock exclusive benefits:</p>
                    <ul class="list-unstyled small">
                        <li><strong>Silver VIP</strong> - $50K+ spent</li>
                        <li><strong>Gold Elite</strong> - $150K+ spent</li>
                        <li><strong>Platinum Exclusive</strong> - $500K+ spent</li>
                    </ul>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
