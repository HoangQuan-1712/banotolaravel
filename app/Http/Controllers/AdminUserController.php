<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AdminUserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('admin');
    }

    /**
     * Hiển thị danh sách tất cả user
     */
    public function index()
    {
        $users = User::withCount('orders')
            ->with(['orders' => function($query) {
                $query->latest()->take(5); // Lấy 5 đơn hàng gần nhất
            }])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('admin.users.index', compact('users'));
    }

    /**
     * Hiển thị chi tiết user và lịch sử đơn hàng
     */
    public function show($id)
    {
        $user = User::with(['orders' => function($query) {
            $query->with('orderItems.product')->orderBy('created_at', 'desc');
        }])->findOrFail($id);

        $totalSpent = $user->orders->sum('total_price');
        $totalOrders = $user->orders->count();
        $completedOrders = $user->orders->where('status', 'completed')->count();

        return view('admin.users.show', compact('user', 'totalSpent', 'totalOrders', 'completedOrders'));
    }

    /**
     * Hiển thị form chỉnh sửa user
     */
    public function edit($id)
    {
        $user = User::findOrFail($id);
        return view('admin.users.edit', compact('user'));
    }

    /**
     * Cập nhật thông tin user
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'role' => 'required|in:user,admin',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
        ]);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            'phone' => $request->phone,
            'address' => $request->address,
        ]);

        return redirect()->route('admin.users.show', $user->id)
            ->with('success', 'Thông tin người dùng đã được cập nhật thành công!');
    }

    /**
     * Xóa user (chỉ xóa khi không có đơn hàng)
     */
    public function destroy($id)
    {
        $user = User::findOrFail($id);

        // Kiểm tra xem user có đơn hàng không
        if ($user->orders()->count() > 0) {
            return back()->with('error', 'Không thể xóa người dùng này vì họ đã có đơn hàng!');
        }

        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'Người dùng đã được xóa thành công!');
    }

    /**
     * Hiển thị danh sách user đã mua hàng
     */
    public function customers()
    {
        $customers = User::whereHas('orders')
            ->withCount('orders')
            ->with(['orders' => function($query) {
                $query->select('id', 'user_id', 'total_price', 'status', 'created_at')
                    ->latest()
                    ->take(3);
            }])
            ->orderBy('orders_count', 'desc')
            ->paginate(15);

        return view('admin.users.customers', compact('customers'));
    }

    /**
     * Hiển thị thống kê tổng quan về user
     */
    public function statistics()
    {
        $totalUsers = User::count();
        $totalCustomers = User::whereHas('orders')->count();
        $newUsersThisMonth = User::whereMonth('created_at', now()->month)->count();
        $activeUsers = User::whereHas('orders', function($query) {
            $query->where('created_at', '>=', now()->subDays(30));
        })->count();

        $topCustomers = User::whereHas('orders')
            ->withSum('orders', 'total_price')
            ->withCount('orders')
            ->orderBy('orders_sum_total_price', 'desc')
            ->take(10)
            ->get();

        return view('admin.users.statistics', compact(
            'totalUsers', 
            'totalCustomers', 
            'newUsersThisMonth', 
            'activeUsers',
            'topCustomers'
        ));
    }
}
