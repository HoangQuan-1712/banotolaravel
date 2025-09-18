<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReviewRequest;
use App\Http\Requests\UpdateReviewContentRequest;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Review;
use App\Models\ReviewImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ReviewController extends Controller
{
    // Tạo review sau khi user hoàn tất mua hàng
    public function store(StoreReviewRequest $request)
    {
        $user = $request->user();
        $productId = (int) $request->input('product_id');
        $orderId   = (int) $request->input('order_id');

        // 1) Xác minh: đơn hàng thuộc user và đã hoàn tất + có chứa productId
        $order = Order::where('id', $orderId)
            ->where('user_id', $user->id)
            ->where('status', 'completed') // đổi theo trạng thái hoàn tất của bạn
            ->firstOrFail();

        $hasItem = OrderItem::where('order_id', $order->id)
            ->where('product_id', $productId)
            ->exists();

        abort_unless($hasItem, 403, 'Bạn không mua sản phẩm này trong đơn hàng.');

        // 2) Kiểm tra đã review chưa (unique constraint vẫn chốt lần cuối)
        $already = Review::where('order_id', $order->id)
            ->where('product_id', $productId)
            ->where('user_id', $user->id)
            ->exists();

        abort_if($already, 422, 'Bạn đã đánh giá sản phẩm này cho đơn hàng này.');

        // 3) Tạo review + upload ảnh (tối đa 6, chỉ ảnh tĩnh)
        DB::transaction(function () use ($request, $user, $order, $productId) {
            $review = Review::create([
                'product_id' => $productId,
                'order_id'   => $order->id,
                'user_id'    => $user->id,
                'rating'     => (int) $request->input('rating'),
                'content'    => $request->input('content'),
            ]);

            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $file) {
                    if (!$file->isValid()) {
                        continue;
                    }
                    // Lưu vào disk 'public' (nhớ storage:link)
                    $path = $file->store('reviews', 'public');
                    ReviewImage::create([
                        'review_id'  => $review->id,
                        'image_path' => $path,
                    ]);
                }
            }
        });

        return back()->with('success', 'Đã gửi đánh giá. Cảm ơn bạn!');
    }

    // Sửa NỘI DUNG review (chỉ trong 7 ngày)
    public function edit(Review $review)
    {
        $this->authorize('update', $review);
        return view('reviews.edit', compact('review'));
    }

    public function update(UpdateReviewContentRequest $request, Review $review)
    {
        $this->authorize('update', $review);

        $review->content = $request->input('content');
        $review->save();

        return redirect()->back()->with('success', 'Đã cập nhật nội dung đánh giá.');
    }
}
