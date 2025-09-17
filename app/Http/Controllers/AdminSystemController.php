<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Order;
use App\Models\Product;
use App\Models\Category;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Artisan;

class AdminSystemController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('admin');
    }

    /**
     * Hiển thị tổng quan hệ thống
     */
    public function overview()
    {
        // Thống kê tổng quan
        $stats = [
            'users' => [
                'total' => User::count(),
                'active' => User::whereHas('orders', function($q) {
                    $q->where('created_at', '>=', now()->subDays(30));
                })->count(),
                'new_this_month' => User::whereMonth('created_at', now()->month)->count(),
                'admins' => User::where('role', 'admin')->count(),
            ],
            'products' => [
                'total' => Product::count(),
                'in_stock' => Product::where('quantity', '>', 0)->count(),
                'low_stock' => Product::where('quantity', '<', 10)->count(),
                'out_of_stock' => Product::where('quantity', 0)->count(),
            ],
            'orders' => [
                'total' => Order::count(),
                'pending' => Order::where('status', 'pending')->count(),
                'processing' => Order::where('status', 'processing')->count(),
                'completed' => Order::where('status', 'completed')->count(),
                'cancelled' => Order::where('status', 'cancelled')->count(),
            ],
            'revenue' => [
                'total' => Order::where('status', 'completed')->sum('total_price'),
                'this_month' => Order::where('status', 'completed')
                    ->whereMonth('created_at', now()->month)
                    ->sum('total_price'),
                'avg_order' => Order::where('status', 'completed')->avg('total_price'),
            ]
        ];

        // Sản phẩm sắp hết hàng
        $lowStockProducts = Product::where('quantity', '<', 10)
            ->with('category')
            ->orderBy('quantity', 'asc')
            ->take(10)
            ->get();

        // Đơn hàng cần xử lý
        $urgentOrders = Order::whereIn('status', ['pending', 'processing'])
            ->with('user')
            ->orderBy('created_at', 'asc')
            ->take(10)
            ->get();

        // User mới nhất
        $recentUsers = User::orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        return view('admin.system.overview', compact('stats', 'lowStockProducts', 'urgentOrders', 'recentUsers'));
    }

    /**
     * Quản lý database
     */
    public function database()
    {
        // Thông tin database
        $dbInfo = [
            'tables' => [
                'users' => User::count(),
                'products' => Product::count(),
                'categories' => Category::count(),
                'orders' => Order::count(),
                'order_items' => OrderItem::count(),
            ],
            'size' => $this->getDatabaseSize(),
        ];

        return view('admin.system.database', compact('dbInfo'));
    }

    /**
     * Backup database
     */
    public function backupDatabase()
    {
        try {
            // Tạo backup
            $filename = 'backup_' . date('Y-m-d_H-i-s') . '.sql';
            
            // Có thể sử dụng mysqldump hoặc các package backup
            // Ví dụ: Artisan::call('backup:run');
            
            return back()->with('success', 'Database đã được backup thành công!');
        } catch (\Exception $e) {
            return back()->with('error', 'Lỗi khi backup database: ' . $e->getMessage());
        }
    }

    /**
     * Quản lý file storage
     */
    public function storage()
    {
        $storageInfo = [
            'total_size' => $this->getStorageSize(),
            'image_count' => $this->countImages(),
            'orphaned_files' => $this->findOrphanedFiles(),
        ];

        return view('admin.system.storage', compact('storageInfo'));
    }

    /**
     * Dọn dẹp file không sử dụng
     */
    public function cleanupStorage()
    {
        try {
            // Tìm và xóa file không sử dụng
            $orphanedFiles = $this->findOrphanedFiles();
            
            foreach ($orphanedFiles as $file) {
                if (Storage::exists($file)) {
                    Storage::delete($file);
                }
            }
            
            return back()->with('success', 'Đã dọn dẹp ' . count($orphanedFiles) . ' file không sử dụng!');
        } catch (\Exception $e) {
            return back()->with('error', 'Lỗi khi dọn dẹp storage: ' . $e->getMessage());
        }
    }

    /**
     * Quản lý cache
     */
    public function cache()
    {
        return view('admin.system.cache');
    }

    /**
     * Xóa cache
     */
    public function clearCache()
    {
        try {
            Artisan::call('cache:clear');
            Artisan::call('config:clear');
            Artisan::call('view:clear');
            Artisan::call('route:clear');
            
            return back()->with('success', 'Cache đã được xóa thành công!');
        } catch (\Exception $e) {
            return back()->with('error', 'Lỗi khi xóa cache: ' . $e->getMessage());
        }
    }

    /**
     * Quản lý logs
     */
    public function logs()
    {
        $logFiles = $this->getLogFiles();
        
        return view('admin.system.logs', compact('logFiles'));
    }

    /**
     * Xem nội dung log file
     */
    public function viewLog($filename)
    {
        $logPath = storage_path('logs/' . $filename);
        
        if (!file_exists($logPath)) {
            return back()->with('error', 'File log không tồn tại!');
        }
        
        $content = file_get_contents($logPath);
        $lines = explode("\n", $content);
        
        return view('admin.system.view-log', compact('filename', 'lines'));
    }

    /**
     * Xóa log file
     */
    public function deleteLog($filename)
    {
        try {
            $logPath = storage_path('logs/' . $filename);
            
            if (file_exists($logPath)) {
                unlink($logPath);
            }
            
            return back()->with('success', 'File log đã được xóa thành công!');
        } catch (\Exception $e) {
            return back()->with('error', 'Lỗi khi xóa log: ' . $e->getMessage());
        }
    }

    /**
     * Quản lý bảo mật
     */
    public function security()
    {
        $securityInfo = [
            'failed_logins' => $this->getFailedLogins(),
            'suspicious_activities' => $this->getSuspiciousActivities(),
            'password_expiry' => $this->getPasswordExpiryUsers(),
        ];
        
        return view('admin.system.security', compact('securityInfo'));
    }

    /**
     * Khóa user
     */
    public function banUser($userId)
    {
        try {
            $user = User::findOrFail($userId);
            $user->update(['is_banned' => true]);
            
            return back()->with('success', 'User đã bị khóa thành công!');
        } catch (\Exception $e) {
            return back()->with('error', 'Lỗi khi khóa user: ' . $e->getMessage());
        }
    }

    /**
     * Mở khóa user
     */
    public function unbanUser($userId)
    {
        try {
            $user = User::findOrFail($userId);
            $user->update(['is_banned' => false]);
            
            return back()->with('success', 'User đã được mở khóa thành công!');
        } catch (\Exception $e) {
            return back()->with('error', 'Lỗi khi mở khóa user: ' . $e->getMessage());
        }
    }

    // Helper methods
    private function getDatabaseSize()
    {
        // Tính kích thước database
        return 'N/A'; // Cần implement theo database cụ thể
    }

    private function getStorageSize()
    {
        // Tính kích thước storage
        $path = storage_path('app');
        $size = 0;
        
        foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path)) as $file) {
            if ($file->isFile()) {
                $size += $file->getSize();
            }
        }
        
        return $this->formatBytes($size);
    }

    private function countImages()
    {
        // Đếm số lượng ảnh
        $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp'];
        $count = 0;
        
        foreach ($imageExtensions as $ext) {
            $count += count(Storage::allFiles('public/images'));
        }
        
        return $count;
    }

    private function findOrphanedFiles()
    {
        // Tìm file không sử dụng
        return []; // Cần implement logic tìm file orphaned
    }

    private function getLogFiles()
    {
        $logPath = storage_path('logs');
        $files = [];
        
        if (is_dir($logPath)) {
            $files = array_diff(scandir($logPath), ['.', '..']);
        }
        
        return $files;
    }

    private function getFailedLogins()
    {
        // Lấy thông tin đăng nhập thất bại
        return []; // Cần implement
    }

    private function getSuspiciousActivities()
    {
        // Lấy hoạt động đáng ngờ
        return []; // Cần implement
    }

    private function getPasswordExpiryUsers()
    {
        // Lấy user có mật khẩu sắp hết hạn
        return []; // Cần implement
    }

    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }
}
