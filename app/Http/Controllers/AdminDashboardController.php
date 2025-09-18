<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Order;
use App\Models\Product;
use App\Models\Category;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AdminDashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('admin');
    }

    /**
     * Hiển thị dashboard tổng quan
     */
    public function index()
    {
        // Thống kê tổng quan
        $totalUsers = User::count();
        $totalCustomers = User::whereHas('orders')->count();
        $totalProducts = Product::count();
        $totalCategories = Category::count();
        $totalOrders = Order::count();
        // Include deposit amounts from deposited orders + full amounts from completed orders
        $depositedRevenue = Order::where(function($q) {
            $q->where('status', 'like', '%đã đặt cọc%')
              ->orWhere('status', 'đã đặt cọc (MoMo)')
              ->orWhere('status', 'đã đặt cọc (COD)');
        })->sum('deposit_amount');
        $completedRevenue = Order::where('status', 'completed')->sum('total_price');
        $totalRevenue = $depositedRevenue + $completedRevenue;

        // Thống kê theo thời gian
        $newUsersThisMonth = User::whereMonth('created_at', now()->month)->count();
        $newOrdersThisMonth = Order::whereMonth('created_at', now()->month)->count();
        // Include deposit amounts from deposited orders + full amounts from completed orders this month
        $depositedRevenueThisMonth = Order::where(function($q) {
            $q->where('status', 'like', '%đã đặt cọc%')
              ->orWhere('status', 'đã đặt cọc (MoMo)')
              ->orWhere('status', 'đã đặt cọc (COD)');
        })->whereMonth('created_at', now()->month)->sum('deposit_amount');
        $completedRevenueThisMonth = Order::where('status', 'completed')
            ->whereMonth('created_at', now()->month)
            ->sum('total_price');
        $revenueThisMonth = $depositedRevenueThisMonth + $completedRevenueThisMonth;

        // Thống kê đơn hàng theo trạng thái
        $pendingOrders = Order::where('status', 'pending')->count();
        $processingOrders = Order::where('status', 'processing')->count();
        $completedOrders = Order::where('status', 'completed')->count();
        $cancelledOrders = Order::where('status', 'cancelled')->count();

        // Thống kê đánh giá
        $totalReviews = Review::whereNull('parent_review_id')->count();
        $pendingReviews = Review::where('status', 'pending')->whereNull('parent_review_id')->count();
        $approvedReviews = Review::where('status', 'approved')->whereNull('parent_review_id')->count();
        $avgRating = round(Review::whereNull('parent_review_id')->avg('rating'), 1);

        // Top sản phẩm bán chạy
        $topProducts = DB::table('order_items')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->selectRaw('
                products.name,
                products.id,
                SUM(order_items.quantity) as total_sold,
                SUM(order_items.quantity * order_items.price) as total_revenue
            ')
            ->groupBy('products.id', 'products.name')
            ->orderBy('total_sold', 'desc')
            ->take(5)
            ->get();

        // Top khách hàng - include deposit amounts
        $topCustomers = User::whereHas('orders')
            ->with(['orders' => function($query) {
                $query->select('user_id', 'status', 'total_price', 'deposit_amount');
            }])
            ->get()
            ->map(function($user) {
                $depositSpent = $user->orders->where('status', 'like', '%đã đặt cọc%')->sum('deposit_amount');
                $completedSpent = $user->orders->where('status', 'completed')->sum('total_price');
                $user->total_spent = $depositSpent + $completedSpent;
                $user->orders_count = $user->orders->count();
                return $user;
            })
            ->sortByDesc('total_spent')
            ->take(5);

        // Thống kê theo ngày (7 ngày gần nhất) - include deposits
        $dailyStats = Order::selectRaw('
                DATE(created_at) as date,
                COUNT(*) as orders_count,
                SUM(CASE WHEN status = "completed" THEN total_price 
                    WHEN status LIKE "%đã đặt cọc%" THEN deposit_amount 
                    ELSE 0 END) as revenue
            ')
            ->where('created_at', '>=', now()->subDays(7))
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();

        // Thống kê user theo tháng (6 tháng gần nhất)
        $monthlyUserStats = User::selectRaw('
                YEAR(created_at) as year,
                MONTH(created_at) as month,
                COUNT(*) as new_users
            ')
            ->where('created_at', '>=', now()->subMonths(6))
            ->groupBy('year', 'month')
            ->orderBy('year', 'asc')
            ->orderBy('month', 'asc')
            ->get();

        // Sản phẩm sắp hết hàng (dưới 10)
        $lowStockProducts = Product::where('quantity', '<', 10)
            ->orderBy('quantity', 'asc')
            ->take(5)
            ->get();

        // Đơn hàng gần đây
        $recentOrders = Order::with(['user', 'orderItems.product'])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        // User mới đăng ký
        $recentUsers = User::orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        return view('admin.dashboard.index', compact(
            'totalUsers',
            'totalCustomers', 
            'totalProducts',
            'totalCategories',
            'totalOrders',
            'totalRevenue',
            'newUsersThisMonth',
            'newOrdersThisMonth',
            'revenueThisMonth',
            'totalReviews',
            'pendingReviews',
            'approvedReviews',
            'avgRating',
            'pendingOrders',
            'processingOrders',
            'completedOrders',
            'cancelledOrders',
            'topProducts',
            'topCustomers',
            'dailyStats',
            'monthlyUserStats',
            'lowStockProducts',
            'recentOrders',
            'recentUsers'
        ));
    }

    /**
     * Hiển thị báo cáo chi tiết
     */
    public function reports()
    {
        // Báo cáo doanh thu theo tháng - include deposits
        $monthlyRevenue = Order::selectRaw('
                YEAR(created_at) as year,
                MONTH(created_at) as month,
                COUNT(*) as orders_count,
                SUM(CASE WHEN status = "completed" THEN total_price 
                    WHEN status LIKE "%đã đặt cọc%" THEN deposit_amount 
                    ELSE 0 END) as revenue,
                AVG(CASE WHEN status = "completed" THEN total_price 
                    WHEN status LIKE "%đã đặt cọc%" THEN deposit_amount 
                    ELSE 0 END) as avg_order_value
            ')
            ->where(function($q) {
                $q->where('status', 'completed')
                  ->orWhere('status', 'like', '%đã đặt cọc%');
            })
            ->whereYear('created_at', now()->year)
            ->groupBy('year', 'month')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->get();

        // Báo cáo doanh thu theo năm - include deposits
        $yearlyRevenue = Order::selectRaw('
                YEAR(created_at) as year,
                COUNT(*) as orders_count,
                SUM(CASE WHEN status = "completed" THEN total_price 
                    WHEN status LIKE "%đã đặt cọc%" THEN deposit_amount 
                    ELSE 0 END) as revenue,
                AVG(CASE WHEN status = "completed" THEN total_price 
                    WHEN status LIKE "%đã đặt cọc%" THEN deposit_amount 
                    ELSE 0 END) as avg_order_value
            ')
            ->where(function($q) {
                $q->where('status', 'completed')
                  ->orWhere('status', 'like', '%đã đặt cọc%');
            })
            ->groupBy('year')
            ->orderBy('year', 'desc')
            ->limit(5)
            ->get();

        // Báo cáo sản phẩm
        $productStats = Product::selectRaw('
                categories.name as category_name,
                COUNT(products.id) as product_count,
                SUM(products.quantity) as total_stock,
                AVG(products.price) as avg_price
            ')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->groupBy('categories.id', 'categories.name')
            ->orderBy('product_count', 'desc')
            ->get();

        // Báo cáo user
        $userStats = User::selectRaw('
                role,
                COUNT(*) as count,
                COUNT(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 END) as new_this_month
            ')
            ->groupBy('role')
            ->get();

        // Thống kê theo danh mục sản phẩm (doanh thu)
        $categoryRevenue = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->selectRaw('
                categories.name as category_name,
                SUM(order_items.quantity * order_items.price) as revenue,
                COUNT(DISTINCT orders.id) as orders_count,
                SUM(order_items.quantity) as products_sold
            ')
            ->where('orders.status', 'completed')
            ->whereYear('orders.created_at', now()->year)
            ->groupBy('categories.id', 'categories.name')
            ->orderBy('revenue', 'desc')
            ->get();


        // Doanh thu theo ngày trong tháng hiện tại - include deposits
        $dailyRevenue = Order::selectRaw('
                DAY(created_at) as day,
                SUM(CASE WHEN status = "completed" THEN total_price 
                    WHEN status LIKE "%đã đặt cọc%" THEN deposit_amount 
                    ELSE 0 END) as revenue,
                COUNT(*) as orders_count
            ')
            ->where(function($q) {
                $q->where('status', 'completed')
                  ->orWhere('status', 'like', '%đã đặt cọc%');
            })
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->groupBy('day')
            ->orderBy('day')
            ->get();

        // Doanh thu theo giờ trong ngày - include deposits
        $hourlyRevenue = Order::selectRaw('
                HOUR(created_at) as hour,
                SUM(CASE WHEN status = "completed" THEN total_price 
                    WHEN status LIKE "%đã đặt cọc%" THEN deposit_amount 
                    ELSE 0 END) as revenue,
                COUNT(*) as orders_count
            ')
            ->where(function($q) {
                $q->where('status', 'completed')
                  ->orWhere('status', 'like', '%đã đặt cọc%');
            })
            ->whereDate('created_at', now()->toDateString())
            ->groupBy('hour')
            ->orderBy('hour')
            ->get();

        return view('admin.dashboard.reports', compact(
            'monthlyRevenue',
            'yearlyRevenue',
            'productStats',
            'userStats',
            'categoryRevenue',
            'dailyRevenue',
            'hourlyRevenue'
        ));
    }

    /**
     * Hiển thị cài đặt hệ thống
     */
    public function settings()
    {
        return view('admin.dashboard.settings');
    }

    /**
     * Cập nhật cài đặt hệ thống
     */
    public function updateSettings(Request $request)
    {
        // Cập nhật các cài đặt hệ thống
        // Có thể lưu vào database hoặc file config
        
        return back()->with('success', 'Cài đặt hệ thống đã được cập nhật thành công!');
    }
}
