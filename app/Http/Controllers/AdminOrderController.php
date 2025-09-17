<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminOrderController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('admin');
    }

    /**
     * Hiển thị danh sách tất cả đơn hàng
     */
    public function index()
    {
        $orders = Order::with(['user', 'orderItems.product'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        $totalRevenue = Order::where('status', 'completed')->sum('total_price');
        $totalOrders = Order::count();
        $pendingOrders = Order::where('status', 'pending')->count();
        $completedOrders = Order::where('status', 'completed')->count();

        return view('admin.orders.index', compact(
            'orders', 
            'totalRevenue', 
            'totalOrders', 
            'pendingOrders', 
            'completedOrders'
        ));
    }

    /**
     * Hiển thị chi tiết đơn hàng
     */
    public function show($id)
    {
        $order = Order::with(['user', 'orderItems.product'])->findOrFail($id);
        
        return view('admin.orders.show', compact('order'));
    }

    /**
     * Cập nhật trạng thái đơn hàng
     */
    public function updateStatus(Request $request, $id)
    {
        $order = Order::findOrFail($id);

        $request->validate([
            'status' => 'required|in:pending,processing,completed,cancelled,đã đặt cọc (MoMo),đã đặt cọc (COD),chờ đặt cọc,thanh toán MoMo không thành công'
        ]);

        // Chỉ cập nhật nếu trạng thái thực sự thay đổi
        if ($order->status !== $request->status) {
            // Inventory side-effects based on target status
            try {
                if ($request->status === 'completed') {
                    // Convert reservations to actual stock deduction
                    $order->deductReservedToSold();
                } elseif ($request->status === 'cancelled' || $request->status === 'thanh toán MoMo không thành công') {
                    // Release reserved stock back to available
                    $order->releaseReservedStock();
                }
            } catch (\Throwable $e) {
                \Log::error('Inventory adjust on status change failed', [
                    'order_id' => $order->id,
                    'to_status' => $request->status,
                    'error' => $e->getMessage(),
                ]);
            }

            $order->update(['status' => $request->status]);
            
            return redirect()->route('admin.orders.show', $order->id)
                ->with('success', 'Trạng thái đơn hàng đã được cập nhật thành công!');
        }

        return redirect()->route('admin.orders.show', $order->id)
            ->with('info', 'Trạng thái đơn hàng không thay đổi.');
    }

    /**
     * Xóa đơn hàng
     */
    public function destroy($id)
    {
        $order = Order::findOrFail($id);
        
        // Xóa order items trước
        $order->orderItems()->delete();
        $order->delete();

        return redirect()->route('admin.orders.index')
            ->with('success', 'Đơn hàng đã được xóa thành công!');
    }

    /**
     * Hiển thị thống kê đơn hàng
     */
    public function statistics(Request $request)
    {
        $year = $request->get('year', now()->year);
        $month = $request->get('month');
        $status = $request->get('status');

        // Thống kê theo tháng
        $monthlyQuery = Order::selectRaw('
                MONTH(created_at) as month,
                YEAR(created_at) as year,
                COUNT(*) as total_orders,
                SUM(total_price) as total_revenue
            ')
            ->whereYear('created_at', $year);

        if ($month) {
            $monthlyQuery->whereMonth('created_at', $month);
        }

        if ($status) {
            $monthlyQuery->where('status', $status);
        }

        $monthlyStats = $monthlyQuery->groupBy('month', 'year')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->get();

        // Top sản phẩm bán chạy với bộ lọc
        $topProductsQuery = DB::table('order_items')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->selectRaw('
                products.name,
                products.id,
                SUM(order_items.quantity) as total_sold,
                SUM(order_items.quantity * order_items.price) as total_revenue
            ')
            ->whereYear('orders.created_at', $year);

        if ($month) {
            $topProductsQuery->whereMonth('orders.created_at', $month);
        }

        if ($status) {
            $topProductsQuery->where('orders.status', $status);
        }

        $topProducts = $topProductsQuery->groupBy('products.id', 'products.name')
            ->orderBy('total_sold', 'desc')
            ->take(10)
            ->get();

        // Thống kê theo trạng thái với bộ lọc
        $statusStatsQuery = Order::selectRaw('status, COUNT(*) as count')
            ->whereYear('created_at', $year);

        if ($month) {
            $statusStatsQuery->whereMonth('created_at', $month);
        }

        $statusStats = $statusStatsQuery->groupBy('status')->get();

        // Doanh thu theo ngày với bộ lọc
        $dailyRevenueQuery = Order::selectRaw('
                DATE(created_at) as date,
                SUM(total_price) as revenue,
                COUNT(*) as orders_count
            ')
            ->where('status', 'completed')
            ->whereYear('created_at', $year);

        if ($month) {
            $dailyRevenueQuery->whereMonth('created_at', $month)
                ->where('created_at', '>=', now()->startOfMonth())
                ->where('created_at', '<=', now()->endOfMonth());
        } else {
            $dailyRevenueQuery->where('created_at', '>=', now()->subDays(30));
        }

        $dailyRevenue = $dailyRevenueQuery->groupBy('date')
            ->orderBy('date', 'desc')
            ->get();

        return view('admin.orders.statistics', compact(
            'monthlyStats',
            'topProducts', 
            'statusStats',
            'dailyRevenue',
            'year',
            'month',
            'status'
        ));
    }

    /**
     * Hiển thị đơn hàng theo trạng thái
     */
    public function byStatus($status)
    {
        $orders = Order::with(['user', 'orderItems.product'])
            ->where('status', $status)
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        $statusLabels = [
            'pending' => 'Chờ xử lý',
            'processing' => 'Đang xử lý',
            'completed' => 'Hoàn thành',
            'cancelled' => 'Đã hủy'
        ];

        return view('admin.orders.by-status', compact('orders', 'status', 'statusLabels'));
    }

    /**
     * Tìm kiếm đơn hàng
     */
    public function search(Request $request)
    {
        $query = Order::with(['user', 'orderItems.product']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('id', 'LIKE', "%{$search}%")
                  ->orWhereHas('user', function($userQuery) use ($search) {
                      $userQuery->where('name', 'LIKE', "%{$search}%")
                               ->orWhere('email', 'LIKE', "%{$search}%");
                  });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $orders = $query->orderBy('created_at', 'desc')->paginate(15);

        return view('admin.orders.search', compact('orders'));
    }

    /**
     * Hiển thị form tạo đơn hàng mới
     */
    public function create()
    {
        $users = User::all();
        return view('admin.orders.create', compact('users'));
    }

    /**
     * Lưu đơn hàng mới
     */
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'total_price' => 'required|numeric|min:0',
            'status' => 'required|in:pending,processing,completed,cancelled'
        ]);

        $order = Order::create([
            'user_id' => $request->user_id,
            'total_price' => $request->total_price,
            'status' => $request->status
        ]);

        return redirect()->route('admin.orders.show', $order)
            ->with('success', 'Đơn hàng đã được tạo thành công!');
    }

    /**
     * Hiển thị form chỉnh sửa đơn hàng
     */
    public function edit($id)
    {
        $order = Order::findOrFail($id);
        $users = User::all();
        return view('admin.orders.edit', compact('order', 'users'));
    }

    /**
     * Cập nhật đơn hàng
     */
    public function update(Request $request, $id)
    {
        $order = Order::findOrFail($id);

        $request->validate([
            'user_id' => 'required|exists:users,id',
            'total_price' => 'required|numeric|min:0',
            'status' => 'required|in:pending,processing,completed,cancelled'
        ]);

        $order->update([
            'user_id' => $request->user_id,
            'total_price' => $request->total_price,
            'status' => $request->status
        ]);

        return redirect()->route('admin.orders.show', $order)
            ->with('success', 'Đơn hàng đã được cập nhật thành công!');
    }

    /**
     * Xuất báo cáo Excel
     */
    public function exportReport()
    {
        $orders = Order::with(['user', 'orderItems.product'])
            ->orderBy('created_at', 'desc')
            ->get();

        $filename = 'bao-cao-don-hang-' . now()->format('Y-m-d') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($orders) {
            $file = fopen('php://output', 'w');
            
            // BOM for UTF-8
            fputs($file, "\xEF\xBB\xBF");
            
            // Header
            fputcsv($file, [
                'Mã Đơn Hàng',
                'Khách Hàng',
                'Email',
                'Số Điện Thoại',
                'Tổng Tiền ($)',
                'Trạng Thái',
                'Ngày Đặt',
                'Sản Phẩm',
                'Số Lượng Sản Phẩm'
            ]);

            foreach ($orders as $order) {
                $products = $order->orderItems->map(function($item) {
                    return $item->product->name . ' (x' . $item->quantity . ')';
                })->implode(', ');

                fputcsv($file, [
                    '#' . $order->id,
                    $order->user->name,
                    $order->user->email,
                    $order->user->phone ?? 'N/A',
                    number_format($order->total_price, 2),
                    $this->getStatusLabel($order->status),
                    $order->created_at->format('d/m/Y H:i'),
                    $products,
                    $order->orderItems->sum('quantity')
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Xuất báo cáo PDF
     */
    public function exportPdf()
    {
        $orders = Order::with(['user', 'orderItems.product'])
            ->orderBy('created_at', 'desc')
            ->get();

        $totalRevenue = Order::where('status', 'completed')->sum('total_price');
        $totalOrders = Order::count();
        $pendingOrders = Order::where('status', 'pending')->count();
        $completedOrders = Order::where('status', 'completed')->count();

        $html = view('admin.orders.report-pdf', compact(
            'orders', 
            'totalRevenue', 
            'totalOrders', 
            'pendingOrders', 
            'completedOrders'
        ))->render();

        $filename = 'bao-cao-don-hang-' . now()->format('Y-m-d') . '.pdf';

        // Simple HTML to PDF conversion
        $headers = [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        return response($html, 200, [
            'Content-Type' => 'text/html',
            'Content-Disposition' => 'attachment; filename="bao-cao-don-hang-' . now()->format('Y-m-d') . '.html"',
        ]);
    }

    /**
     * Lấy nhãn trạng thái
     */
    private function getStatusLabel($status)
    {
        $labels = [
            'pending' => 'Chờ xử lý',
            'processing' => 'Đang xử lý',
            'completed' => 'Hoàn thành',
            'cancelled' => 'Đã hủy'
        ];

        return $labels[$status] ?? $status;
    }
}
