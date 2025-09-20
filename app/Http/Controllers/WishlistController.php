<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Wishlist;
use Illuminate\Support\Facades\Auth;

class WishlistController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $wishlist = Auth::user()->wishlist()->with('product.category')->get();
        return view('wishlist.index', compact('wishlist'));
    }

    public function add(Request $request, Product $product)
    {
        $user = Auth::user();
        
        // Check if already in wishlist
        $existing = $user->wishlist()->where('product_id', $product->id)->first();
        
        if ($existing) {
            return redirect()->back()->with('info', 'Sản phẩm đã có trong danh sách yêu thích!');
        }

        // Add to wishlist
        $user->wishlist()->create([
            'product_id' => $product->id,
            'added_at' => now()
        ]);

        return redirect()->back()->with('success', 'Đã thêm vào danh sách yêu thích!');
    }

    public function remove(Product $product)
    {
        $user = Auth::user();
        
        $user->wishlist()->where('product_id', $product->id)->delete();
        
        return redirect()->back()->with('success', 'Đã xóa khỏi danh sách yêu thích!');
    }

    public function toggle(Request $request, Product $product)
    {
        $user = Auth::user();
        
        $existing = $user->wishlist()->where('product_id', $product->id)->first();
        
        if ($existing) {
            $existing->delete();
            $message = 'Đã xóa khỏi danh sách yêu thích!';
            $action = 'removed';
        } else {
            $user->wishlist()->create([
                'product_id' => $product->id,
                'added_at' => now()
            ]);
            $message = 'Đã thêm vào danh sách yêu thích!';
            $action = 'added';
        }

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'action' => $action,
                'wishlist_count' => $user->wishlist_count
            ]);
        }

        return redirect()->back()->with('success', $message);
    }

    public function clear()
    {
        $user = Auth::user();
        $user->wishlist()->delete();
        
        return redirect()->route('wishlist.index')->with('success', 'Đã xóa tất cả sản phẩm khỏi danh sách yêu thích!');
    }

    public function moveToCart(Product $product)
    {
        $user = Auth::user();
        
        // Remove from wishlist
        $user->wishlist()->where('product_id', $product->id)->delete();
        
        // Add to cart
        $cart = session()->get('cart', []);
        
        if (isset($cart[$product->id])) {
            $cart[$product->id]['quantity'] += 1;
        } else {
            $cart[$product->id] = [
                'id' => $product->id,
                'name' => $product->name,
                'price' => $product->price,
                'quantity' => 1,
                'image' => $product->image,
                'category' => optional($product->category)->name ?? 'Sản phẩm',
                'max_quantity' => $product->quantity
            ];
        }
        
        session()->put('cart', $cart);
        
        return redirect()->route('cart.index')->with('success', 'Đã chuyển sản phẩm vào giỏ hàng!');
    }
}
