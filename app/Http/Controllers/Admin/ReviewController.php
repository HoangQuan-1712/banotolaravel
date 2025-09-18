<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Models\Product;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function __construct()
    {
        $this->middleware('admin');
    }

    // Danh sách tất cả đánh giá
    public function index(Request $request)
    {
        $query = Review::with(['user', 'product', 'order'])
                      ->orderBy('created_at', 'desc');

        // Filter by status
        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }

        // Filter by rating
        if ($request->has('rating') && $request->rating !== '') {
            $query->where('rating', $request->rating);
        }

        // Search by product name or user name
        if ($request->has('search') && $request->search !== '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->whereHas('product', function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                })->orWhereHas('user', function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                });
            });
        }

        $reviews = $query->paginate(15);
        
        // Statistics
        $stats = [
            'total' => Review::count(),
            'pending' => Review::where('status', 'pending')->count(),
            'approved' => Review::where('status', 'approved')->count(),
            'rejected' => Review::where('status', 'rejected')->count(),
            'avg_rating' => round(Review::avg('rating'), 1),
        ];

        return view('admin.reviews.index', compact('reviews', 'stats'));
    }

    // Xem chi tiết đánh giá
    public function show(Review $review)
    {
        $review->load(['user', 'product', 'order']);
        return view('admin.reviews.show', compact('review'));
    }

    // Cập nhật trạng thái đánh giá
    public function updateStatus(Request $request, Review $review)
    {
        $request->validate([
            'status' => 'required|in:pending,approved,rejected'
        ]);

        $review->update(['status' => $request->status]);

        return redirect()->back()->with('success', 'Đã cập nhật trạng thái đánh giá!');
    }

    // Phản hồi đánh giá (sử dụng trường comment để lưu phản hồi admin)
    public function respond(Request $request, Review $review)
    {
        $request->validate([
            'admin_response' => 'required|string|max:1000'
        ]);

        // Tạo một review mới từ admin như là phản hồi
        Review::create([
            'user_id' => auth()->id(), // Admin user
            'product_id' => $review->product_id,
            'order_id' => null, // Admin không có order
            'rating' => 0, // Admin response không có rating
            'title' => 'Phản hồi từ cửa hàng',
            'comment' => $request->admin_response,
            'is_verified_purchase' => false,
            'status' => 'approved',
            'parent_review_id' => $review->id, // Liên kết với review gốc
        ]);

        return redirect()->back()->with('success', 'Đã gửi phản hồi thành công!');
    }

    // Đánh giá theo sản phẩm
    public function byProduct(Product $product)
    {
        $reviews = $product->reviews()
                          ->with(['user', 'order'])
                          ->orderBy('created_at', 'desc')
                          ->paginate(10);

        $stats = [
            'total' => $product->reviews()->count(),
            'avg_rating' => round($product->reviews()->avg('rating'), 1),
            'rating_breakdown' => []
        ];

        // Rating breakdown
        for ($i = 1; $i <= 5; $i++) {
            $count = $product->reviews()->where('rating', $i)->count();
            $stats['rating_breakdown'][$i] = [
                'count' => $count,
                'percentage' => $stats['total'] > 0 ? round(($count / $stats['total']) * 100) : 0
            ];
        }

        return view('admin.reviews.by-product', compact('product', 'reviews', 'stats'));
    }

    // Xóa đánh giá
    public function destroy(Review $review)
    {
        $review->delete();
        return redirect()->back()->with('success', 'Đã xóa đánh giá!');
    }
}
