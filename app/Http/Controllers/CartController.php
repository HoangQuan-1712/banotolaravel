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

        if (!empty($cart)) {
            $productIds = array_keys($cart);
            $products = Product::with('category')->findMany($productIds)->keyBy('id');

            foreach ($cart as $id => &$details) {
                if (isset($products[$id])) {
                    $product = $products[$id];
                    // Cập nhật lại thông tin để đảm bảo luôn đúng
                    $details['name'] = $product->name;
                    $details['price'] = $product->price;
                    $details['image'] = $product->image;
                    $details['category'] = optional($product->category)->name ?? 'Sản phẩm';
                    $available = max(0, (int)$product->quantity - (int)($product->reserved_quantity ?? 0));
                    $details['max_quantity'] = $available;

                    // Đảm bảo số lượng trong giỏ không vượt quá số lượng còn khả dụng
                    if ($details['quantity'] > $available) {
                        $details['quantity'] = $available;
                    }
                } else {
                    // Nếu sản phẩm không còn tồn tại, loại bỏ khỏi giỏ hàng
                    unset($cart[$id]);
                }
            }
            unset($details); // Hủy tham chiếu

            // Lưu lại giỏ hàng đã được làm sạch
            session()->put('cart', $cart);
        }

        // Tính lại tổng tiền sau khi đã cập nhật
        foreach ($cart as $id => $details) {
            $total += $details['price'] * $details['quantity'];
        }

        return view('cart.index', compact('cart', 'total'));
    }

    // Thêm sản phẩm vào giỏ hàng
    public function add(Request $request, Product $product)
    {
        $quantity = (int) $request->input('quantity', 1);
        if ($quantity < 1) { $quantity = 1; }
        
        // Kiểm tra số lượng còn khả dụng (tồn kho trừ số đã giữ chỗ)
        $available = max(0, (int)$product->quantity - (int)($product->reserved_quantity ?? 0));
        if ($available <= 0) {
            return redirect()->back()->with('error', 'Sản phẩm đã hết hàng.');
        }
        if ($quantity > $available) {
            return redirect()->back()->with('error', 'Số lượng vượt quá khả dụng. Chỉ còn ' . $available . ' sản phẩm.');
        }
        
        $cart = session()->get('cart', []);

        if (isset($cart[$product->id])) {
            $newQuantity = $cart[$product->id]['quantity'] + $quantity;
            
            // Kiểm tra tổng số lượng sau khi thêm theo số lượng còn khả dụng
            if ($newQuantity > $available) {
                return redirect()->back()->with('error', 'Tổng số lượng vượt quá khả dụng. Chỉ còn ' . $available . ' sản phẩm.');
            }
            
            $cart[$product->id]['quantity'] = $newQuantity;
            // Đảm bảo có id sản phẩm
            if (!isset($cart[$product->id]['id'])) {
                $cart[$product->id]['id'] = $product->id;
            }
            // Bổ sung các key còn thiếu (đề phòng thêm từ wishlist thiếu dữ liệu)
            if (empty($cart[$product->id]['category'])) {
                $cart[$product->id]['category'] = optional($product->category)->name ?? 'Sản phẩm';
            }
            if (!isset($cart[$product->id]['image'])) {
                $cart[$product->id]['image'] = $product->image;
            }
            if (!isset($cart[$product->id]['price'])) {
                $cart[$product->id]['price'] = $product->price;
            }
            if (!isset($cart[$product->id]['max_quantity'])) {
                $cart[$product->id]['max_quantity'] = $available;
            }
        } else {
            $cart[$product->id] = [
                "id" => $product->id, // Thêm ID sản phẩm
                "name" => $product->name,
                "quantity" => $quantity,
                "price" => $product->price,
                "category" => optional($product->category)->name ?? 'Sản phẩm',
                "image" => $product->image, // Lưu đường dẫn tương đối thay vì URL đầy đủ
                "max_quantity" => $available
            ];
        }

        session()->put('cart', $cart);
        return redirect()->route('cart.index')->with('success', 'Sản phẩm đã được thêm vào giỏ hàng.');
    }

    // Cập nhật số lượng sản phẩm
    public function update(Request $request, $id)
    {
        $quantity = (int) $request->input('quantity');
        $product = Product::findOrFail($id);
        
        if ($quantity <= 0) {
            // Xóa sản phẩm nếu người dùng nhập 0 hoặc âm
            return $this->remove($product->id);
        }
        
        // Kiểm tra số lượng còn khả dụng
        $available = max(0, (int)$product->quantity - (int)($product->reserved_quantity ?? 0));
        if ($available <= 0) {
            return redirect()->route('cart.index')->with('error', 'Sản phẩm đã hết hàng.');
        }
        if ($quantity > $available) {
            return redirect()->route('cart.index')->with('error', 'Số lượng vượt quá khả dụng. Chỉ còn ' . $available . ' sản phẩm.');
        }
        
        $cart = session()->get('cart', []);
        
        if (isset($cart[$product->id])) {
            $cart[$product->id]['quantity'] = $quantity;
            // Đảm bảo có id sản phẩm
            if (!isset($cart[$product->id]['id'])) {
                $cart[$product->id]['id'] = $product->id;
            }
            // Bổ sung key còn thiếu
            if (empty($cart[$product->id]['category'])) {
                $cart[$product->id]['category'] = optional($product->category)->name ?? 'Sản phẩm';
            }
            if (!isset($cart[$product->id]['image'])) {
                $cart[$product->id]['image'] = $product->image;
            }
            if (!isset($cart[$product->id]['price'])) {
                $cart[$product->id]['price'] = $product->price;
            }
            if (!isset($cart[$product->id]['max_quantity'])) {
                $cart[$product->id]['max_quantity'] = $product->quantity;
            }
            session()->put('cart', $cart);
        }
        
        return redirect()->route('cart.index')->with('success', 'Số lượng đã được cập nhật.');
    }

    // Xoá sản phẩm khỏi giỏ hàng
    public function remove($id)
    {
        $cart = session()->get('cart', []);

        if (isset($cart[$id])) {
            unset($cart[$id]);
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
