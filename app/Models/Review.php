<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class Review extends Model
{
    use HasFactory;

    protected $table = 'product_reviews';

    protected $fillable = [
        'product_id',
        'user_id',
        'order_id',
        'rating',
        'title',
        'comment',
        'is_verified_purchase',
        'status',
        'parent_review_id',
    ];

    protected $casts = [
        'is_verified_purchase' => 'boolean',
    ];

    // Status constants
    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';

    // Quan hệ
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    // Accessor để tương thích với view cũ
    public function getContentAttribute()
    {
        return $this->comment;
    }

    public function getAdminResponseAttribute()
    {
        return null; // Bảng product_reviews không có trường này
    }

    public function getRespondedAtAttribute()
    {
        return null; // Bảng product_reviews không có trường này
    }

    public function adminResponder()
    {
        return null; // Bảng product_reviews không có relationship này
    }

    public function images()
    {
        return collect(); // Tạm thời trả về collection rỗng
    }

    // Relationship for admin responses
    public function parentReview()
    {
        return $this->belongsTo(Review::class, 'parent_review_id');
    }

    public function replies()
    {
        return $this->hasMany(Review::class, 'parent_review_id');
    }

    // Check if this is an admin response
    public function isAdminResponse()
    {
        return $this->parent_review_id !== null && $this->rating === 0;
    }

    // Helper: còn trong hạn sửa nội dung (7 ngày)
    public function isWithinEditWindow(): bool
    {
        return $this->created_at && $this->created_at->diffInDays(Carbon::now()) < 7;
    }

    // Helper: admin còn trong hạn sửa/xoá phản hồi (1 ngày)
    public function adminResponseEditable(): bool
    {
        return $this->responded_at && $this->responded_at->diffInHours(now()) < 24;
    }
}
