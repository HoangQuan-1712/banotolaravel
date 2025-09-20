<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Product extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'quantity', 'reserved_quantity', 'price', 'image', 'description', 'category_id'];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function wishlistUsers()
    {
        return $this->belongsToMany(User::class, 'wishlists');
    }

    public function returnRequests()
    {
        return $this->hasMany(ReturnRequest::class);
    }

    public function getAverageRatingAttribute()
    {
        return $this->reviews()->count() > 0 ? round($this->reviews()->avg('rating'), 1) : 0;
    }

    public function getReviewsCountAttribute()
    {
        return $this->reviews()->count();
    }

    public function getRatingStarsAttribute()
    {
        $rating = $this->average_rating;
        $stars = '';
        
        for ($i = 1; $i <= 5; $i++) {
            if ($i <= $rating) {
                $stars .= '<i class="fas fa-star text-warning"></i>';
            } elseif ($i - $rating < 1) {
                $stars .= '<i class="fas fa-star-half-alt text-warning"></i>';
            } else {
                $stars .= '<i class="far fa-star text-warning"></i>';
            }
        }
        
        return $stars;
    }

    public function getImageUrlAttribute()
    {
        if ($this->image) {
            // Normalize value in DB: accept both 'products/...' and 'storage/products/...'
            $normalized = ltrim(preg_replace('/^storage\//', '', $this->image), '/');

            // If stored as absolute URL, return directly
            if (filter_var($this->image, FILTER_VALIDATE_URL)) {
                return $this->image;
            }

            // File exists in public disk?
            if (Storage::disk('public')->exists($normalized)) {
                return asset('storage/' . $normalized);
            }

            // Fall back to default image
            return $this->getDefaultImageUrl();
        }

        return $this->getDefaultImageUrl();
    }

    private function getDefaultImageUrl()
    {
        // Return simple default SVG image
        return asset('images/default-car.svg');
    }

    public function getImagePathAttribute()
    {
        if ($this->image) {
            $normalized = ltrim(preg_replace('/^storage\//', '', $this->image), '/');
            return storage_path('app/public/' . $normalized);
        }
        return null;
    }

    public function hasImage()
    {
        if (!$this->image) return false;
        if (filter_var($this->image, FILTER_VALIDATE_URL)) return true;
        $normalized = ltrim(preg_replace('/^storage\//', '', $this->image), '/');
        return Storage::disk('public')->exists($normalized);
    }

    // Available stock = total quantity minus reserved
    public function getAvailableStockAttribute()
    {
        $reserved = (int) ($this->reserved_quantity ?? 0);
        return max(0, (int) $this->quantity - $reserved);
    }
}
