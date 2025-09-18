<?php

namespace App\Policies;

use App\Models\Review;
use App\Models\User;

class ReviewPolicy
{
    // User chỉ sửa review của chính mình và trong 7 ngày; KHÔNG cho sửa rating/ảnh ở Request
    public function update(User $user, Review $review): bool
    {
        return $user->id === $review->user_id && $review->isWithinEditWindow();
    }

    // (Tuỳ chọn) Cho xoá review nếu muốn, hiện không dùng
    public function delete(User $user, Review $review): bool
    {
        return $user->id === $review->user_id && $review->isWithinEditWindow();
    }

    // Admin phản hồi: phải là admin, và review CHƯA có phản hồi
    public function respond(User $user, Review $review): bool
    {
        return $user->is_admin ?? false; // hoặc kiểm tra role
    }

    // Admin sửa/xoá phản hồi trong 24h
    public function updateResponse(User $user, Review $review): bool
    {
        return ($user->is_admin ?? false) && $review->adminResponseEditable();
    }

    public function deleteResponse(User $user, Review $review): bool
    {
        return ($user->is_admin ?? false) && $review->adminResponseEditable();
    }
}
