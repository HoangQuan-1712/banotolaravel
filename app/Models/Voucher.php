<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Voucher extends Model {
    protected $fillable = [
        'code', 'type', 'value', 'name', 'description', 'group_code',
        'min_order_value', 'max_order_value', 'applicable_categories',
        'usage_limit', 'usage_limit_per_user', 'used_count',
        'stock', 'weight', 'tier_level', 'start_date', 'end_date', 'active', 'metadata'
    ];
    
    protected $casts = [
        'applicable_categories' => 'array',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'metadata' => 'array',
        'value' => 'decimal:2'
    ];

    // Relationships
    public function usages() { 
        return $this->hasMany(VoucherUsage::class); 
    }

    // Scopes
    public function scopeActive($query) {
        return $query->where('active', true)
                    ->where(function($q) {
                        $q->whereNull('start_date')
                          ->orWhere('start_date', '<=', now());
                    })
                    ->where(function($q) {
                        $q->whereNull('end_date')
                          ->orWhere('end_date', '>=', now());
                    });
    }

    public function scopeTieredChoice($query) {
        return $query->where('type', 'tiered_choice');
    }

    public function scopeRandomGift($query) {
        return $query->where('type', 'random_gift');
    }

    public function scopeVipTier($query, $tierLevel = null) {
        $q = $query->where('type', 'vip_tier');
        if ($tierLevel) {
            $q->where('tier_level', $tierLevel);
        }
        return $q;
    }

    // Helper methods
    public function isAvailable() {
        if (!$this->active) return false;
        if ($this->start_date && $this->start_date->isFuture()) return false;
        if ($this->end_date && $this->end_date->isPast()) return false;
        if ($this->stock !== null && $this->stock <= 0) return false;
        if ($this->usage_limit && $this->used_count >= $this->usage_limit) return false;
        
        return true;
    }

    public function canBeUsedBy($user, $orderValue = null) {
        if (!$this->isAvailable()) return false;
        
        // Check order value constraints
        if ($this->min_order_value && $orderValue < $this->min_order_value) return false;
        if ($this->max_order_value && $orderValue > $this->max_order_value) return false;
        
        // Check user usage limit
        $userUsageCount = $this->usages()->where('user_id', $user->id)->count();
        if ($userUsageCount >= $this->usage_limit_per_user) return false;
        
        // Check tier requirement
        if ($this->type === 'vip_tier' && $this->tier_level) {
            if (!$user->tier || $user->tier->level !== $this->tier_level) return false;
        }
        
        return true;
    }

    public function getFormattedValueAttribute() {
        return $this->value ? '$' . number_format($this->value, 2) : 'N/A';
    }
}
