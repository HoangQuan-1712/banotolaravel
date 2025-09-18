<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdminRespondReviewRequest;
use App\Http\Requests\AdminUpdateResponseRequest;
use App\Models\Review;
use Illuminate\Http\Request;

class ReviewResponseController extends Controller
{
    // Admin gửi phản hồi (mỗi review 1 phản hồi)
    public function store(AdminRespondReviewRequest $request, Review $review)
    {
        $this->authorize('respond', $review);

        if ($review->admin_response !== null) {
            return back()->withErrors(['response' => 'Đánh giá này đã có phản hồi.']);
        }

        $review->admin_response = $request->input('response');
        $review->responded_by   = $request->user()->id;
        $review->responded_at   = now();
        $review->save();

        return back()->with('success', 'Đã gửi phản hồi cho đánh giá.');
    }

    // Admin sửa phản hồi trong 24h
    public function update(AdminUpdateResponseRequest $request, Review $review)
    {
        $this->authorize('updateResponse', $review);

        if ($review->admin_response === null) {
            return back()->withErrors(['response' => 'Chưa có phản hồi để sửa.']);
        }

        $review->admin_response = $request->input('response');
        $review->save();

        return back()->with('success', 'Đã cập nhật phản hồi.');
    }

    // Admin xoá phản hồi trong 24h
    public function destroy(Request $request, Review $review)
    {
        $this->authorize('deleteResponse', $review);

        if ($review->admin_response === null) {
            return back()->withErrors(['response' => 'Không có phản hồi để xoá.']);
        }

        $review->admin_response = null;
        $review->responded_by = null;
        $review->responded_at = null;
        $review->save();

        return back()->with('success', 'Đã xoá phản hồi.');
    }
}
