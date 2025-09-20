<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AdminActivityController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('admin');
    }

    /**
     * Hiển thị hoạt động tổng quan
     */
    public function index()
    {
        // Hoạt động gần đây
        $recentActivities = $this->getRecentActivities();
        
        // Thống kê hoạt động theo ngày
        $dailyActivities = $this->getDailyActivities();
        
        // Top user hoạt động
        $activeUsers = $this->getActiveUsers();
        
        // Sản phẩm được xem nhiều
        $popularProducts = $this->getPopularProducts();
        
        return view('admin.activities.index', compact(
            'recentActivities',
            'dailyActivities',
            'activeUsers',
            'popularProducts'
        ));
    }

    /**
     * Theo dõi hoạt động user
     */
    public function userActivity($userId)
    {
        $user = User::findOrFail($userId);
        
        // Lịch sử đăng nhập
        $loginHistory = $this->getUserLoginHistory($userId);
        
        // Lịch sử đơn hàng
        $orderHistory = $user->orders()->with('orderItems.product')->orderBy('created_at', 'desc')->get();
        
        // Hoạt động gần đây
        $recentActivity = $this->getUserRecentActivity($userId);
        
        // Thống kê hoạt động
        $activityStats = $this->getUserActivityStats($userId);
        
        return view('admin.activities.user-activity', compact(
            'user',
            'loginHistory',
            'orderHistory',
            'recentActivity',
            'activityStats'
        ));
    }

    /**
     * Theo dõi hoạt động sản phẩm
     */
    public function productActivity($productId)
    {
        $product = Product::findOrFail($productId);
        
        // Lịch sử bán hàng
        $salesHistory = $this->getProductSalesHistory($productId);
        
        // User đã mua
        $buyers = $this->getProductBuyers($productId);
        
        // Thống kê bán hàng
        $salesStats = $this->getProductSalesStats($productId);
        
        return view('admin.activities.product-activity', compact(
            'product',
            'salesHistory',
            'buyers',
            'salesStats'
        ));
    }

    /**
     * Báo cáo hoạt động
     */
    public function reports()
    {
        $dateRange = request('date_range', '30'); // 7, 30, 90 days
        
        // Báo cáo hoạt động user
        $userActivityReport = $this->getUserActivityReport($dateRange);
        
        // Báo cáo hoạt động sản phẩm
        $productActivityReport = $this->getProductActivityReport($dateRange);
        
        // Báo cáo hoạt động đơn hàng
        $orderActivityReport = $this->getOrderActivityReport($dateRange);
        
        return view('admin.activities.reports', compact(
            'dateRange',
            'userActivityReport',
            'productActivityReport',
            'orderActivityReport'
        ));
    }

    /**
     * Xuất báo cáo
     */
    public function exportReport(Request $request)
    {
        $type = $request->type; // user, product, order
        $format = $request->format; // csv, excel, pdf
        $dateRange = $request->date_range;
        
        try {
            switch ($type) {
                case 'user':
                    $data = $this->getUserActivityReport($dateRange);
                    break;
                case 'product':
                    $data = $this->getProductActivityReport($dateRange);
                    break;
                case 'order':
                    $data = $this->getOrderActivityReport($dateRange);
                    break;
                default:
                    return back()->with('error', 'Loại báo cáo không hợp lệ!');
            }
            
            // Xuất file theo format
            return $this->exportData($data, $type, $format);
            
        } catch (\Exception $e) {
            return back()->with('error', 'Lỗi khi xuất báo cáo: ' . $e->getMessage());
        }
    }

    /**
     * Lấy hoạt động gần đây
     */
    private function getRecentActivities()
    {
        $activities = collect();
        
        // Đơn hàng mới
        $recentOrders = Order::with('user')
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get()
            ->map(function ($order) {
                return [
                    'type' => 'order',
                    'action' => 'Đặt hàng mới',
                    'user' => optional($order->user)->name ?? 'Khách hàng (N/A)',
                    'details' => 'Đơn hàng #' . $order->id,
                    'time' => $order->created_at,
                    'status' => $order->status
                ];
            });
        
        // User mới đăng ký
        $recentUsers = User::orderBy('created_at', 'desc')
            ->take(10)
            ->get()
            ->map(function ($user) {
                return [
                    'type' => 'user',
                    'action' => 'Đăng ký mới',
                    'user' => $user->name,
                    'details' => $user->email,
                    'time' => $user->created_at,
                    'status' => 'active'
                ];
            });
        
        // Sản phẩm mới
        $recentProducts = Product::with('category')
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get()
            ->map(function ($product) {
                return [
                    'type' => 'product',
                    'action' => 'Thêm sản phẩm mới',
                    'user' => 'Admin',
                    'details' => $product->name . ' - ' . $product->category->name,
                    'time' => $product->created_at,
                    'status' => 'active'
                ];
            });
        
        return $activities->merge($recentOrders)
            ->merge($recentUsers)
            ->merge($recentProducts)
            ->sortByDesc('time')
            ->take(20);
    }

    /**
     * Lấy hoạt động theo ngày
     */
    private function getDailyActivities()
    {
        return Order::selectRaw('
                DATE(created_at) as date,
                COUNT(*) as orders_count,
                COUNT(DISTINCT user_id) as unique_users,
                SUM(total_price) as total_revenue
            ')
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date', 'desc')
            ->get();
    }

    /**
     * Lấy user hoạt động
     */
    private function getActiveUsers()
    {
        return User::whereHas('orders', function($query) {
                $query->where('created_at', '>=', now()->subDays(30));
            })
            ->withCount(['orders' => function($query) {
                $query->where('created_at', '>=', now()->subDays(30));
            }])
            ->withSum(['orders' => function($query) {
                $query->where('created_at', '>=', now()->subDays(30));
            }], 'total_price')
            ->orderBy('orders_count', 'desc')
            ->take(10)
            ->get();
    }

    /**
     * Lấy sản phẩm phổ biến
     */
    private function getPopularProducts()
    {
        return DB::table('order_items')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->selectRaw('
                products.name,
                products.id,
                SUM(order_items.quantity) as total_sold,
                COUNT(DISTINCT order_items.order_id) as order_count
            ')
            ->groupBy('products.id', 'products.name')
            ->orderBy('total_sold', 'desc')
            ->take(10)
            ->get();
    }

    /**
     * Lấy lịch sử đăng nhập user
     */
    private function getUserLoginHistory($userId)
    {
        // Có thể implement tracking login history
        return collect();
    }

    /**
     * Lấy hoạt động gần đây của user
     */
    private function getUserRecentActivity($userId)
    {
        $user = User::find($userId);
        $activities = collect();
        
        // Đơn hàng gần đây
        $recentOrders = $user->orders()
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get()
            ->map(function ($order) {
                return [
                    'type' => 'order',
                    'action' => 'Đặt hàng',
                    'details' => 'Đơn hàng #' . $order->id . ' - ' . number_format($order->total_price, 0, ',', '.') . ' $',
                    'time' => $order->created_at,
                    'status' => $order->status
                ];
            });
        
        return $activities->merge($recentOrders);
    }

    /**
     * Lấy thống kê hoạt động user
     */
    private function getUserActivityStats($userId)
    {
        $user = User::find($userId);
        
        return [
            'total_orders' => $user->orders()->count(),
            'total_spent' => $user->orders()->where('status', 'completed')->sum('total_price'),
            'avg_order_value' => $user->orders()->where('status', 'completed')->avg('total_price'),
            'last_order' => $user->orders()->latest()->first()?->created_at,
            'favorite_category' => $this->getUserFavoriteCategory($userId),
        ];
    }

    /**
     * Lấy danh mục yêu thích của user
     */
    private function getUserFavoriteCategory($userId)
    {
        $favoriteCategory = DB::table('order_items')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->where('order_items.order_id', function($query) use ($userId) {
                $query->select('id')
                    ->from('orders')
                    ->where('user_id', $userId);
            })
            ->selectRaw('categories.name, SUM(order_items.quantity) as total_quantity')
            ->groupBy('categories.id', 'categories.name')
            ->orderBy('total_quantity', 'desc')
            ->first();
        
        return $favoriteCategory ? $favoriteCategory->name : 'Không có';
    }

    /**
     * Lấy lịch sử bán hàng sản phẩm
     */
    private function getProductSalesHistory($productId)
    {
        return DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('order_items.product_id', $productId)
            ->selectRaw('
                DATE(orders.created_at) as date,
                SUM(order_items.quantity) as quantity_sold,
                SUM(order_items.quantity * order_items.price) as revenue
            ')
            ->groupBy('date')
            ->orderBy('date', 'desc')
            ->get();
    }

    /**
     * Lấy danh sách người mua sản phẩm
     */
    private function getProductBuyers($productId)
    {
        return DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('users', 'orders.user_id', '=', 'users.id')
            ->where('order_items.product_id', $productId)
            ->selectRaw('
                users.name,
                users.email,
                SUM(order_items.quantity) as total_quantity,
                SUM(order_items.quantity * order_items.price) as total_spent
            ')
            ->groupBy('users.id', 'users.name', 'users.email')
            ->orderBy('total_quantity', 'desc')
            ->get();
    }

    /**
     * Lấy thống kê bán hàng sản phẩm
     */
    private function getProductSalesStats($productId)
    {
        $stats = DB::table('order_items')
            ->where('product_id', $productId)
            ->selectRaw('
                SUM(quantity) as total_sold,
                SUM(quantity * price) as total_revenue,
                AVG(price) as avg_price,
                COUNT(DISTINCT order_id) as order_count
            ')
            ->first();
        
        return $stats;
    }

    /**
     * Lấy báo cáo hoạt động user
     */
    private function getUserActivityReport($dateRange)
    {
        $startDate = now()->subDays($dateRange);
        
        return User::selectRaw('
                users.name,
                users.email,
                COUNT(orders.id) as orders_count,
                SUM(orders.total_price) as total_spent,
                MAX(orders.created_at) as last_order,
                MIN(orders.created_at) as first_order
            ')
            ->leftJoin('orders', 'users.id', '=', 'orders.user_id')
            ->where('orders.created_at', '>=', $startDate)
            ->groupBy('users.id', 'users.name', 'users.email')
            ->orderBy('orders_count', 'desc')
            ->get();
    }

    /**
     * Lấy báo cáo hoạt động sản phẩm
     */
    private function getProductActivityReport($dateRange)
    {
        $startDate = now()->subDays($dateRange);
        
        return DB::table('order_items')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.created_at', '>=', $startDate)
            ->selectRaw('
                products.name,
                categories.name as category,
                SUM(order_items.quantity) as total_sold,
                SUM(order_items.quantity * order_items.price) as total_revenue,
                COUNT(DISTINCT orders.id) as order_count
            ')
            ->groupBy('products.id', 'products.name', 'categories.name')
            ->orderBy('total_sold', 'desc')
            ->get();
    }

    /**
     * Lấy báo cáo hoạt động đơn hàng
     */
    private function getOrderActivityReport($dateRange)
    {
        $startDate = now()->subDays($dateRange);
        
        return Order::selectRaw('
                DATE(created_at) as date,
                COUNT(*) as orders_count,
                COUNT(DISTINCT user_id) as unique_users,
                SUM(total_price) as total_revenue,
                AVG(total_price) as avg_order_value
            ')
            ->where('created_at', '>=', $startDate)
            ->groupBy('date')
            ->orderBy('date', 'desc')
            ->get();
    }

    /**
     * Xuất dữ liệu
     */
    private function exportData($data, $type, $format)
    {
        // Implement export logic
        return back()->with('success', 'Báo cáo đã được xuất thành công!');
    }
}
