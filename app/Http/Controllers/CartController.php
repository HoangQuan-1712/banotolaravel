<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;

class CartController extends Controller
{
    // Hiển thị giỏ hàng
    public function index()
    {
        $cart = session()->get('cart', []);
        $total = 0;
        
        // Tính tổng tiền
        foreach ($cart as $id => $details) {
            $total += $details['price'] * $details['quantity'];
        }
        
        return view('cart.index', compact('cart', 'total'));
    }

    // Thêm sản phẩm vào giỏ hàng
    public function add(Request $request, Product $product)
    {
        $quantity = $request->input('quantity', 1);
        
        // Kiểm tra số lượng tồn kho
        if ($quantity > $product->quantity) {
            return redirect()->back()->with('error', 'Số lượng vượt quá tồn kho. Chỉ còn ' . $product->quantity . ' sản phẩm.');
        }
        
        $cart = session()->get('cart', []);

        if (isset($cart[$product->id])) {
            $newQuantity = $cart[$product->id]['quantity'] + $quantity;
            
            // Kiểm tra tổng số lượng sau khi thêm
            if ($newQuantity > $product->quantity) {
                return redirect()->back()->with('error', 'Tổng số lượng vượt quá tồn kho. Chỉ còn ' . $product->quantity . ' sản phẩm.');
            }
            
            $cart[$product->id]['quantity'] = $newQuantity;
            // Đảm bảo có id sản phẩm
            if (!isset($cart[$product->id]['id'])) {
                $cart[$product->id]['id'] = $product->id;
            }
        } else {
            $cart[$product->id] = [
                "id" => $product->id, // Thêm ID sản phẩm
                "name" => $product->name,
                "quantity" => $quantity,
                "price" => $product->price,
                "category" => $product->category->name,
                "image" => $product->image, // Lưu đường dẫn tương đối thay vì URL đầy đủ
                "max_quantity" => $product->quantity
            ];
        }

        session()->put('cart', $cart);
        return redirect()->route('cart.index')->with('success', 'Sản phẩm đã được thêm vào giỏ hàng.');
    }

    // Cập nhật số lượng sản phẩm
    public function update(Request $request, $id)
    {
        $quantity = $request->input('quantity');
        $product = Product::findOrFail($id);
        
        if ($quantity <= 0) {
            return $this->remove($product);
        }
        
        // Kiểm tra số lượng tồn kho
        if ($quantity > $product->quantity) {
            return redirect()->route('cart.index')->with('error', 'Số lượng vượt quá tồn kho. Chỉ còn ' . $product->quantity . ' sản phẩm.');
        }
        
        $cart = session()->get('cart', []);
        
        if (isset($cart[$product->id])) {
            $cart[$product->id]['quantity'] = $quantity;
            // Đảm bảo có id sản phẩm
            if (!isset($cart[$product->id]['id'])) {
                $cart[$product->id]['id'] = $product->id;
            }
            session()->put('cart', $cart);
        }
        
        return redirect()->route('cart.index')->with('success', 'Số lượng đã được cập nhật.');
    }

    // Xoá sản phẩm khỏi giỏ hàng
    public function remove(Product $product)
    {
        $cart = session()->get('cart', []);

        if (isset($cart[$product->id])) {
            unset($cart[$product->id]);
            session()->put('cart', $cart);
        }

        return redirect()->route('cart.index')->with('success', 'Sản phẩm đã được xoá khỏi giỏ hàng.');
    }

    // Xử lý đặt cọc
    public function checkout()
    {
        $cart = session()->get('cart', []);
        
        if (empty($cart)) {
            return redirect()->route('cart.index')->with('error', 'Giỏ hàng trống.');
        }
        
        // Tính tổng tiền cọc (30% tổng giá trị)
        $total = 0;
        foreach ($cart as $id => $details) {
            $total += $details['price'] * $details['quantity'];
        }
        
        $deposit = $total * 0.3; // 30% cọc
        
        // Lấy danh sách địa chỉ của user để hiển thị trong checkout
        $addresses = auth()->user()->addresses()->orderBy('is_default', 'desc')->get();
        $defaultAddress = auth()->user()->defaultAddress;
        
        // Chuyển hướng đến trang thanh toán với thông tin địa chỉ
        return view('cart.checkout', compact('cart', 'total', 'deposit', 'addresses', 'defaultAddress'));
    }
}
