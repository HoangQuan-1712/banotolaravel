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
        return $this->hasMany(ProductReview::class);
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
        $approvedReviews = $this->reviews()->where('status', ProductReview::STATUS_APPROVED);
        return $approvedReviews->count() > 0 ? round($approvedReviews->avg('rating'), 1) : 0;
    }

    public function getReviewsCountAttribute()
    {
        return $this->reviews()->where('status', ProductReview::STATUS_APPROVED)->count();
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
            // Check if the image file exists in storage
            if (Storage::disk('public')->exists($this->image)) {
                return asset('storage/' . $this->image);
            } else {
                // If file doesn't exist, return default image (no logging to avoid spam)
                return $this->getDefaultImageUrl();
            }
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
            return storage_path('app/public/' . $this->image);
        }
        return null;
    }

    public function hasImage()
    {
        return $this->image && Storage::disk('public')->exists($this->image);
    }

    // Available stock = total quantity minus reserved
    public function getAvailableStockAttribute()
    {
        $reserved = (int) ($this->reserved_quantity ?? 0);
        return max(0, (int) $this->quantity - $reserved);
    }
}
