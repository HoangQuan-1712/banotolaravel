<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Báo Cáo Đơn Hàng - {{ now()->format('d/m/Y') }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            margin: 0;
            padding: 20px;
            font-size: 12px;
            line-height: 1.4;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
        }
        .company-name {
            font-size: 24px;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 5px;
        }
        .report-title {
            font-size: 18px;
            color: #34495e;
            margin-bottom: 10px;
        }
        .report-date {
            font-size: 14px;
            color: #7f8c8d;
        }
        .summary-stats {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
            gap: 15px;
        }
        .stat-card {
            flex: 1;
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            text-align: center;
            border: 1px solid #dee2e6;
        }
        .stat-number {
            font-size: 20px;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 5px;
        }
        .stat-label {
            font-size: 11px;
            color: #6c757d;
            text-transform: uppercase;
        }
        .orders-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .orders-table th,
        .orders-table td {
            border: 1px solid #dee2e6;
            padding: 8px;
            text-align: left;
            font-size: 10px;
        }
        .orders-table th {
            background-color: #343a40;
            color: white;
            font-weight: bold;
            text-align: center;
        }
        .orders-table tbody tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        .status-badge {
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .status-pending { background-color: #ffc107; color: #212529; }
        .status-processing { background-color: #17a2b8; color: white; }
        .status-completed { background-color: #28a745; color: white; }
        .status-cancelled { background-color: #dc3545; color: white; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #6c757d;
            border-top: 1px solid #dee2e6;
            padding-top: 15px;
        }
        .page-break {
            page-break-before: always;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="company-name">AutoDealer</div>
        <div class="report-title">BÁO CÁO QUẢN LÝ ĐỚN HÀNG</div>
        <div class="report-date">Ngày xuất báo cáo: {{ now()->format('d/m/Y H:i') }}</div>
    </div>

    <!-- Thống kê tổng quan -->
    <div class="summary-stats">
        <div class="stat-card">
            <div class="stat-number">{{ $totalOrders }}</div>
            <div class="stat-label">Tổng Đơn Hàng</div>
        </div>
        <div class="stat-card">
            <div class="stat-number">{{ $completedOrders }}</div>
            <div class="stat-label">Đã Hoàn Thành</div>
        </div>
        <div class="stat-card">
            <div class="stat-number">{{ $pendingOrders }}</div>
            <div class="stat-label">Chờ Xử Lý</div>
        </div>
        <div class="stat-card">
            <div class="stat-number">${{ number_format($totalRevenue, 0, ',', '.') }}</div>
            <div class="stat-label">Tổng Doanh Thu</div>
        </div>
    </div>

    <!-- Bảng đơn hàng -->
    <h3 style="color: #2c3e50; margin-bottom: 15px;">Chi Tiết Đơn Hàng</h3>
    <table class="orders-table">
        <thead>
            <tr>
                <th style="width: 8%;">Mã Đơn</th>
                <th style="width: 20%;">Khách Hàng</th>
                <th style="width: 25%;">Sản Phẩm</th>
                <th style="width: 12%;">Tổng Tiền</th>
                <th style="width: 10%;">Trạng Thái</th>
                <th style="width: 12%;">Ngày Đặt</th>
                <th style="width: 8%;">SL SP</th>
            </tr>
        </thead>
        <tbody>
            @foreach($orders as $index => $order)
                <tr>
                    <td class="text-center"><strong>#{{ $order->id }}</strong></td>
                    <td>
                        <strong>{{ $order->user->name }}</strong><br>
                        <small style="color: #6c757d;">{{ $order->user->email }}</small>
                        @if($order->user->phone)
                            <br><small style="color: #6c757d;">{{ $order->user->phone }}</small>
                        @endif
                    </td>
                    <td>
                        @foreach($order->orderItems->take(3) as $item)
                            <div style="margin-bottom: 2px;">
                                • {{ $item->product->name }} (x{{ $item->quantity }})
                            </div>
                        @endforeach
                        @if($order->orderItems->count() > 3)
                            <small style="color: #6c757d;">+{{ $order->orderItems->count() - 3 }} sản phẩm khác</small>
                        @endif
                    </td>
                    <td class="text-right">
                        <strong>${{ number_format($order->total_price, 0, ',', '.') }}</strong>
                    </td>
                    <td class="text-center">
                        @php
                            $statusClass = [
                                'pending' => 'status-pending',
                                'processing' => 'status-processing', 
                                'completed' => 'status-completed',
                                'cancelled' => 'status-cancelled'
                            ][$order->status] ?? 'status-pending';
                            
                            $statusLabel = [
                                'pending' => 'Chờ xử lý',
                                'processing' => 'Đang xử lý',
                                'completed' => 'Hoàn thành', 
                                'cancelled' => 'Đã hủy'
                            ][$order->status] ?? $order->status;
                        @endphp
                        <span class="status-badge {{ $statusClass }}">{{ $statusLabel }}</span>
                    </td>
                    <td class="text-center">{{ $order->created_at->format('d/m/Y') }}</td>
                    <td class="text-center">{{ $order->orderItems->sum('quantity') }}</td>
                </tr>
                
                @if(($index + 1) % 20 == 0 && $index + 1 < $orders->count())
                    </tbody>
                    </table>
                    <div class="page-break"></div>
                    <table class="orders-table">
                        <thead>
                            <tr>
                                <th style="width: 8%;">Mã Đơn</th>
                                <th style="width: 20%;">Khách Hàng</th>
                                <th style="width: 25%;">Sản Phẩm</th>
                                <th style="width: 12%;">Tổng Tiền</th>
                                <th style="width: 10%;">Trạng Thái</th>
                                <th style="width: 12%;">Ngày Đặt</th>
                                <th style="width: 8%;">SL SP</th>
                            </tr>
                        </thead>
                        <tbody>
                @endif
            @endforeach
        </tbody>
    </table>

    <!-- Tổng kết -->
    <div style="margin-top: 30px; padding: 15px; background-color: #f8f9fa; border-radius: 5px;">
        <h4 style="color: #2c3e50; margin-bottom: 10px;">Tổng Kết Báo Cáo</h4>
        <div style="display: flex; justify-content: space-between;">
            <div>
                <strong>Tổng số đơn hàng:</strong> {{ $orders->count() }}<br>
                <strong>Đơn hàng hoàn thành:</strong> {{ $orders->where('status', 'completed')->count() }}<br>
                <strong>Tỷ lệ hoàn thành:</strong> {{ $totalOrders > 0 ? round(($completedOrders / $totalOrders) * 100, 1) : 0 }}%
            </div>
            <div style="text-align: right;">
                <strong>Tổng doanh thu:</strong> ${{ number_format($totalRevenue, 0, ',', '.') }}<br>
                <strong>Doanh thu trung bình/đơn:</strong> ${{ $completedOrders > 0 ? number_format($totalRevenue / $completedOrders, 0, ',', '.') : 0 }}<br>
                <strong>Tổng sản phẩm đã bán:</strong> {{ $orders->sum(function($order) { return $order->orderItems->sum('quantity'); }) }}
            </div>
        </div>
    </div>

    <div class="footer">
        <p>Báo cáo được tạo tự động bởi hệ thống AutoDealer | © {{ now()->year }} AutoDealer. All rights reserved.</p>
        <p>Liên hệ: support@autodealer.com | Hotline: 1900-xxxx</p>
    </div>
</body>
</html>
