<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserAddress extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'phone',
        'address_line_1',
        'address_line_2',
        'city',
        'district',
        'ward',
        'postal_code',
        'is_default',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getFullAddressAttribute()
    {
        $address = $this->address_line_1;
        if ($this->address_line_2) {
            $address .= ', ' . $this->address_line_2;
        }
        $address .= ', ' . $this->ward . ', ' . $this->district . ', ' . $this->city;
        if ($this->postal_code) {
            $address .= ' ' . $this->postal_code;
        }
        return $address;
    }

    public function getFormattedAddressAttribute()
    {
        return [
            'name' => $this->name,
            'phone' => $this->phone,
            'address' => $this->full_address,
        ];
    }

    // Đặt làm địa chỉ mặc định
    public function setAsDefault()
    {
        // Bỏ mặc định của các địa chỉ khác
        $this->user->addresses()->where('id', '!=', $this->id)->update(['is_default' => false]);
        
        // Đặt địa chỉ này làm mặc định
        $this->update(['is_default' => true]);
    }

    // Boot method để đảm bảo chỉ có 1 địa chỉ mặc định
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($address) {
            if ($address->is_default) {
                // Bỏ mặc định của các địa chỉ khác cùng user
                static::where('user_id', $address->user_id)
                    ->where('id', '!=', $address->id)
                    ->update(['is_default' => false]);
            }
        });
    }
}
