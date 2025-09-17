<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with(['orders' => function($q) {
            $q->select('user_id', 'status', 'total_price');
        }]);

        // Tìm kiếm theo tên hoặc email
        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }

        // Lọc theo role
        if ($request->role) {
            $query->where('role', $request->role);
        }

        // Lọc theo trạng thái (bao gồm cả soft deleted)
        if ($request->status === 'deleted') {
            $query->onlyTrashed();
        } elseif ($request->status === 'all') {
            $query->withTrashed();
        }

        $users = $query->paginate(20);

        return view('admin.users.index', compact('users'));
    }

    public function show(User $user)
    {
        $user->load([
            'orders.items.product',
            'addresses',
            'wishlist.product',
            'reviews.product',
            'loyaltyPoints',
            'returnRequests'
        ]);
        $totalOrders = $user->orders()->count();
        $completedOrders = $user->orders()->where('status', 'completed')->count();
        // Include deposit amounts from deposited orders + full amounts from completed orders
        $depositedSpent = $user->orders()
            ->where(function($q) {
                $q->where('status', 'like', '%đã đặt cọc%')
                  ->orWhere('status', 'đã đặt cọc (MoMo)')
                  ->orWhere('status', 'đã đặt cọc (COD)');
            })
            ->sum('deposit_amount');
        $completedSpent = $user->orders()->where('status', 'completed')->sum('total_price');
        $totalSpent = $depositedSpent + $completedSpent;

        return view('admin.users.show', compact('user', 'totalOrders', 'completedOrders', 'totalSpent'));
    }

    public function create()
    {
        return view('admin.users.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:admin,user',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'phone' => $request->phone,
            'address' => $request->address,
            'email_verified_at' => now(),
        ]);

        return redirect()->route('admin.users.index')
            ->with('success', 'Người dùng đã được tạo thành công.');
    }

    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|string|min:8|confirmed',
            'role' => 'required|in:admin,user',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
        ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            'phone' => $request->phone,
            'address' => $request->address,
        ];

        if ($request->password) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return redirect()->route('admin.users.index')
            ->with('success', 'Thông tin người dùng đã được cập nhật.');
    }

    public function destroy(User $user)
    {
        // Kiểm tra xem user có thể bị xóa không
        $orderSummary = $user->order_summary;
        
        if ($user->canBeDeleted()) {
            // Nếu không có đơn hàng nào, có thể xóa vĩnh viễn
            $user->forceDelete();
            $message = 'Người dùng đã được xóa vĩnh viễn (không có lịch sử đơn hàng).';
        } else {
            // Nếu có đơn hàng, chỉ soft delete
            $user->delete();
            $message = "Người dùng đã được ẩn khỏi hệ thống (có {$orderSummary['total_orders']} đơn hàng, tổng chi tiêu: " . number_format($orderSummary['total_spent']) . " $).";
        }

        return redirect()->route('admin.users.index')
            ->with('success', $message);
    }

    public function restore($id)
    {
        $user = User::withTrashed()->findOrFail($id);
        $user->restore();

        return redirect()->route('admin.users.index')
            ->with('success', 'Người dùng đã được khôi phục.');
    }

    public function forceDelete($id)
    {
        $user = User::withTrashed()->findOrFail($id);
        
        // Kiểm tra lại trước khi xóa vĩnh viễn
        if ($user->hasCompletedOrders()) {
            return redirect()->route('admin.users.index')
                ->with('error', 'Không thể xóa vĩnh viễn người dùng có đơn hàng đã hoàn thành.');
        }

        // Xóa tất cả dữ liệu liên quan trước
        $user->addresses()->delete();
        $user->wishlist()->delete();
        $user->reviews()->delete();
        $user->loyaltyPoints()->delete();
        $user->returnRequests()->delete();
        
        // Xóa các đơn hàng chưa hoàn thành
        $user->orders()->whereIn('status', [
            Order::STATUS_PENDING, 
            Order::STATUS_CANCELLED
        ])->delete();

        $user->forceDelete();

        return redirect()->route('admin.users.index')
            ->with('success', 'Người dùng đã được xóa vĩnh viễn cùng tất cả dữ liệu liên quan.');
    }

    public function deleteConfirmation(User $user)
    {
        $user->load('orders');
        $orderSummary = $user->order_summary;
        
        return view('admin.users.delete-confirmation', compact('user', 'orderSummary'));
    }

    public function toggleStatus(User $user)
    {
        // Toggle between active and soft deleted
        if ($user->trashed()) {
            $user->restore();
            $message = 'Người dùng đã được kích hoạt lại.';
        } else {
            $user->delete();
            $message = 'Người dùng đã được vô hiệu hóa.';
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'status' => $user->trashed() ? 'inactive' : 'active'
        ]);
    }

    public function statistics()
    {
        $stats = [
            'total_users' => User::withTrashed()->count(),
            'active_users' => User::count(),
            'deleted_users' => User::onlyTrashed()->count(),
            'admin_users' => User::where('role', 'admin')->count(),
            'regular_users' => User::where('role', 'user')->count(),
            'users_with_orders' => User::has('orders')->count(),
            'users_without_orders' => User::doesntHave('orders')->count(),
            'top_customers' => User::withSum('orders', 'total_price')
                ->orderBy('orders_sum_total_price', 'desc')
                ->limit(10)
                ->get(),
            'recent_registrations' => User::orderBy('created_at', 'desc')
                ->limit(10)
                ->get(),
            'monthly_registrations' => User::selectRaw('YEAR(created_at) as year, MONTH(created_at) as month, COUNT(*) as count')
                ->groupBy('year', 'month')
                ->orderBy('year', 'desc')
                ->orderBy('month', 'desc')
                ->limit(12)
                ->get(),
        ];

        return view('admin.users.statistics', compact('stats'));
    }
}
