<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class CustomerTier extends Model {
    protected $fillable = [
        'level', 'name', 'min_spent', 'benefits', 'color', 
        'priority_support', 'discount_percentage'
    ];
    
    protected $casts = [
        'min_spent' => 'decimal:2',
        'discount_percentage' => 'decimal:2'
    ];

    // Relationships
    public function users() {
        return $this->hasMany(User::class, 'tier_id');
    }

    public function vouchers() {
        return $this->hasMany(Voucher::class, 'tier_level', 'level');
    }

    // Helper methods
    public static function getTierBySpending($amount) {
        return self::where('min_spent', '<=', $amount)
                  ->orderByDesc('min_spent')
                  ->first();
    }

    public function getFormattedMinSpentAttribute() {
        return '$' . number_format($this->min_spent, 2);
    }

    public function getBadgeColorAttribute() {
        $colors = [
            'bronze' => '#CD7F32',
            'silver' => '#C0C0C0', 
            'gold' => '#FFD700',
            'platinum' => '#E5E4E2'
        ];
        
        return $colors[$this->level] ?? $this->color;
    }
}
