<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoyaltyPoint extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'order_id',
        'points',
        'type',
        'description',
        'expires_at'
    ];

    protected $casts = [
        'points' => 'integer',
        'expires_at' => 'datetime'
    ];

    const TYPE_EARNED = 'earned';
    const TYPE_SPENT = 'spent';
    const TYPE_EXPIRED = 'expired';
    const TYPE_BONUS = 'bonus';

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function isEarned()
    {
        return $this->type === self::TYPE_EARNED;
    }

    public function isSpent()
    {
        return $this->type === self::TYPE_SPENT;
    }

    public function isExpired()
    {
        return $this->type === self::TYPE_EXPIRED;
    }

    public function isBonus()
    {
        return $this->type === self::TYPE_BONUS;
    }

    public function getTypeLabelAttribute()
    {
        $labels = [
            self::TYPE_EARNED => 'Tích điểm',
            self::TYPE_SPENT => 'Sử dụng điểm',
            self::TYPE_EXPIRED => 'Hết hạn',
            self::TYPE_BONUS => 'Điểm thưởng'
        ];

        return $labels[$this->type] ?? $this->type;
    }

    public function getTypeColorAttribute()
    {
        $colors = [
            self::TYPE_EARNED => 'success',
            self::TYPE_SPENT => 'warning',
            self::TYPE_EXPIRED => 'danger',
            self::TYPE_BONUS => 'info'
        ];

        return $colors[$this->type] ?? 'secondary';
    }

    public function hasExpired()
    {
        return $this->expires_at && $this->expires_at->isPast();
    }
}
