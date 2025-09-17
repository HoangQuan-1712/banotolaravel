<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\ProductReview;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ProductReviewController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function store(Request $request, Product $product)
    {
        $validator = Validator::make($request->all(), [
            'rating' => 'required|integer|min:1|max:5',
            'title' => 'required|string|max:255',
            'comment' => 'required|string|max:1000',
        ], [
            'rating.required' => 'Vui lòng chọn đánh giá.',
            'rating.min' => 'Đánh giá phải từ 1-5 sao.',
            'rating.max' => 'Đánh giá phải từ 1-5 sao.',
            'title.required' => 'Vui lòng nhập tiêu đề đánh giá.',
            'title.max' => 'Tiêu đề không được quá 255 ký tự.',
            'comment.required' => 'Vui lòng nhập nội dung đánh giá.',
            'comment.max' => 'Nội dung không được quá 1000 ký tự.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $user = Auth::user();

        // Check if user has purchased this product
        $hasPurchased = Order::where('user_id', $user->id)
            ->whereHas('orderItems', function($query) use ($product) {
                $query->where('product_id', $product->id);
            })
            ->where('status', 'completed')
            ->exists();

        // Check if user already reviewed this product
        $existingReview = $user->reviews()
            ->where('product_id', $product->id)
            ->first();

        if ($existingReview) {
            return redirect()->back()->with('error', 'Bạn đã đánh giá sản phẩm này rồi!');
        }

        // Create review
        $review = $user->reviews()->create([
            'product_id' => $product->id,
            'rating' => $request->rating,
            'title' => $request->title,
            'comment' => $request->comment,
            'is_verified_purchase' => $hasPurchased,
            'status' => $hasPurchased ? ProductReview::STATUS_APPROVED : ProductReview::STATUS_PENDING
        ]);

        $message = $hasPurchased 
            ? 'Cảm ơn bạn đã đánh giá sản phẩm!' 
            : 'Đánh giá của bạn đã được gửi và đang chờ duyệt!';

        return redirect()->back()->with('success', $message);
    }

    public function update(Request $request, ProductReview $review)
    {
        // Check if user owns this review
        if ($review->user_id !== Auth::id()) {
            abort(403, 'Bạn không có quyền chỉnh sửa đánh giá này.');
        }

        $validator = Validator::make($request->all(), [
            'rating' => 'required|integer|min:1|max:5',
            'title' => 'required|string|max:255',
            'comment' => 'required|string|max:1000',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $review->update([
            'rating' => $request->rating,
            'title' => $request->title,
            'comment' => $request->comment,
            'status' => ProductReview::STATUS_PENDING // Reset to pending for admin review
        ]);

        return redirect()->back()->with('success', 'Đã cập nhật đánh giá! Đánh giá sẽ được duyệt lại.');
    }

    public function destroy(ProductReview $review)
    {
        // Check if user owns this review or is admin
        if ($review->user_id !== Auth::id() && !Auth::user()->isAdmin()) {
            abort(403, 'Bạn không có quyền xóa đánh giá này.');
        }

        $review->delete();

        return redirect()->back()->with('success', 'Đã xóa đánh giá!');
    }

    public function myReviews()
    {
        $reviews = Auth::user()->reviews()
            ->with('product.category')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('reviews.my-reviews', compact('reviews'));
    }

    public function productReviews(Product $product)
    {
        $reviews = $product->reviews()
            ->where('status', ProductReview::STATUS_APPROVED)
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('reviews.product-reviews', compact('product', 'reviews'));
    }
}
