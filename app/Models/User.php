<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',  // Thêm role
        'phone',
        'address',
        'tier_id',
        'lifetime_spent',
        'total_cars_bought',
        'tier_updated_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function wishlist()
    {
        return $this->hasMany(Wishlist::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function loyaltyPoints()
    {
        return $this->hasMany(LoyaltyPoint::class);
    }

    public function returnRequests()
    {
        return $this->hasMany(ReturnRequest::class);
    }

    public function getTotalSpentAttribute()
    {
        return $this->orders()->where('status', 'completed')->sum('total_price');
    }

    public function getOrdersCountAttribute()
    {
        return $this->orders()->count();
    }

    public function getCompletedOrdersCountAttribute()
    {
        return $this->orders()->where('status', 'completed')->count();
    }

    public function getLoyaltyPointsAttribute()
    {
        return $this->loyaltyPoints()
            ->where('type', LoyaltyPoint::TYPE_EARNED)
            ->where('expires_at', '>', now())
            ->sum('points') - 
            $this->loyaltyPoints()
            ->where('type', LoyaltyPoint::TYPE_SPENT)
            ->sum('points');
    }

    public function getWishlistCountAttribute()
    {
        return $this->wishlist()->count();
    }

    public function getReviewsCountAttribute()
    {
        return $this->reviews()->count();
    }

    public function addresses()
    {
        return $this->hasMany(UserAddress::class);
    }

    public function defaultAddress()
    {
        return $this->hasOne(UserAddress::class)->where('is_default', true);
    }

    // Kiểm tra xem user có thể bị xóa không (dựa trên lịch sử đơn hàng)
    public function canBeDeleted()
    {
        return $this->orders()->count() === 0;
    }

    // Kiểm tra xem user có đơn hàng đã hoàn thành không
    public function hasCompletedOrders()
    {
        return $this->orders()->where('status', Order::STATUS_COMPLETED)->exists();
    }
    public function tier()
    {
        return $this->belongsTo(CustomerTier::class, 'tier_id');
    }

    public function voucherUsages()
    {
        return $this->hasMany(VoucherUsage::class);
    }

    // Lấy thông tin tóm tắt về đơn hàng để hiển thị khi admin xóa user
    public function getLifetimeSpentAttribute()
    {
        $completedSpent = $this->orders()
            ->where('status', Order::STATUS_COMPLETED)
            ->sum('total_price');

        $depositSpent = $this->orders()
            ->whereIn('status', ['đã đặt cọc (COD)', 'đã đặt cọc (MoMo)'])
            ->sum('deposit_amount');

        return $completedSpent + $depositSpent;
    }

    public function getOrderSummaryAttribute()
    {
        return [
            'total_orders' => $this->orders()->count(),
            'completed_orders' => $this->orders()->where('status', Order::STATUS_COMPLETED)->count(),
            'pending_orders' => $this->orders()->where('status', Order::STATUS_PENDING)->count(),
            'total_spent' => $this->getLifetimeSpentAttribute(),
        ];
    }
};
