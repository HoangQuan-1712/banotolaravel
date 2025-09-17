@extends('layouts.app')

@section('title', 'Thống Kê Đơn Hàng - Admin')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">
                    <i class="fas fa-chart-line"></i> 
                    Thống Kê Đơn Hàng
                </h1>
                <div>
                    <a href="{{ route('admin.orders.export-report') }}" class="btn btn-success me-2">
                        <i class="fas fa-file-excel"></i> Xuất Báo Cáo Excel
                    </a>
                    <a href="{{ route('admin.orders.export-pdf') }}" class="btn btn-danger me-2">
                        <i class="fas fa-file-pdf"></i> Xuất Báo Cáo PDF
                    </a>
                    <a href="{{ route('admin.orders.index') }}" class="btn btn-secondary me-2">
                        <i class="fas fa-arrow-left"></i> Quay Lại
                    </a>
                    <a href="{{ route('admin.dashboard.index') }}" class="btn btn-primary">
                        <i class="fas fa-tachometer-alt"></i> Dashboard
                    </a>
                </div>
            </div>

            <!-- Bộ lọc thời gian -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-filter"></i> Bộ Lọc Thống Kê</h5>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('admin.orders.statistics') }}" class="row g-3">
                        <div class="col-md-3">
                            <label for="year" class="form-label">Năm</label>
                            <select class="form-select" id="year" name="year">
                                @for($i = now()->year; $i >= 2020; $i--)
                                    <option value="{{ $i }}" {{ request('year', now()->year) == $i ? 'selected' : '' }}>
                                        {{ $i }}
                                    </option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="month" class="form-label">Tháng</label>
                            <select class="form-select" id="month" name="month">
                                <option value="">Tất cả tháng</option>
                                @for($i = 1; $i <= 12; $i++)
                                    <option value="{{ $i }}" {{ request('month') == $i ? 'selected' : '' }}>
                                        Tháng {{ $i }}
                                    </option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="status" class="form-label">Trạng Thái</label>
                            <select class="form-select" id="status" name="status">
                                <option value="">Tất cả trạng thái</option>
                                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Chờ xử lý</option>
                                <option value="processing" {{ request('status') == 'processing' ? 'selected' : '' }}>Đang xử lý</option>
                                <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Hoàn thành</option>
                                <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Đã hủy</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">&nbsp;</label>
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search"></i> Lọc
                                </button>
                                <a href="{{ route('admin.orders.statistics') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-undo"></i> Reset
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            @if(count($monthlyStats) == 0 && count($statusStats) == 0)
                <div class="alert alert-info alert-dismissible fade show" role="alert">
                    <i class="fas fa-info-circle"></i> 
                    <strong>Chưa có dữ liệu thống kê!</strong> 
                    Hãy tạo một số đơn hàng để xem thống kê chi tiết.
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <!-- Thống kê tổng quan -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card bg-primary text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h6 class="card-title">Tổng Đơn Hàng</h6>
                                    <h3 class="mb-0">{{ $statusStats->sum('count') }}</h3>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-shopping-bag fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-success text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h6 class="card-title">Đã Hoàn Thành</h6>
                                    <h3 class="mb-0">{{ $statusStats->where('status', 'completed')->first()->count ?? 0 }}</h3>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-check-circle fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-warning text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h6 class="card-title">Chờ Xử Lý</h6>
                                    <h3 class="mb-0">{{ $statusStats->where('status', 'pending')->first()->count ?? 0 }}</h3>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-clock fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-info text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h6 class="card-title">Đang Xử Lý</h6>
                                    <h3 class="mb-0">{{ $statusStats->where('status', 'processing')->first()->count ?? 0 }}</h3>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-cogs fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Biểu đồ doanh thu theo tháng -->
            <div class="row mb-4">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-chart-bar"></i> Doanh Thu Theo Tháng 
                                @if(request('month'))
                                    (Tháng {{ request('month') }}/{{ request('year', now()->year) }})
                                @else
                                    ({{ request('year', now()->year) }})
                                @endif
                            </h5>
                        </div>
                        <div class="card-body">
                            <canvas id="monthlyRevenueChart" width="400" height="200"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-chart-pie"></i> Phân Bố Trạng Thái
                            </h5>
                        </div>
                        <div class="card-body">
                            <canvas id="statusPieChart" width="400" height="200"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Doanh thu theo ngày -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-chart-line"></i> 
                                @if(request('month'))
                                    Doanh Thu Tháng {{ request('month') }}/{{ request('year', now()->year) }}
                                @else
                                    Doanh Thu 30 Ngày Gần Nhất ({{ request('year', now()->year) }})
                                @endif
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>Ngày</th>
                                            <th>Số Đơn Hàng</th>
                                            <th>Doanh Thu</th>
                                            <th>Trung Bình/Đơn</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($dailyRevenue as $revenue)
                                        <tr>
                                            <td>{{ \Carbon\Carbon::parse($revenue->date)->format('d/m/Y') }}</td>
                                            <td>
                                                <span class="badge bg-primary">{{ $revenue->orders_count }}</span>
                                            </td>
                                            <td>
                                                <span class="text-success fw-bold">
                                                    ${{ number_format($revenue->revenue, 0, ',', '.') }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="text-muted">
                                                    ${{ number_format($revenue->revenue / $revenue->orders_count, 0, ',', '.') }}
                                                </span>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Top sản phẩm bán chạy -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-star"></i> Top 10 Sản Phẩm Bán Chạy
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>#</th>
                                            <th>Sản Phẩm</th>
                                            <th>Số Lượng Đã Bán</th>
                                            <th>Doanh Thu</th>
                                            <th>Thao Tác</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($topProducts as $index => $product)
                                        <tr>
                                            <td>
                                                <span class="badge bg-{{ $index < 3 ? 'warning' : 'secondary' }}">
                                                    {{ $index + 1 }}
                                                </span>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="me-3">
                                                        <i class="fas fa-car fa-2x text-primary"></i>
                                                    </div>
                                                    <div>
                                                        <div class="fw-bold">{{ $product->name }}</div>
                                                        <small class="text-muted">ID: {{ $product->id }}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-success fs-6">
                                                    {{ number_format($product->total_sold) }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="text-success fw-bold">
                                                    ${{ number_format($product->total_revenue, 0, ',', '.') }}
                                                </span>
                                            </td>
                                            <td>
                                                <a href="{{ route('products.show', $product->id) }}" 
                                                   class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-eye"></i> Xem
                                                </a>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Biểu đồ doanh thu theo tháng
const monthlyCtx = document.getElementById('monthlyRevenueChart').getContext('2d');
const monthlyData = @json($monthlyStats);

const months = ['T1', 'T2', 'T3', 'T4', 'T5', 'T6', 'T7', 'T8', 'T9', 'T10', 'T11', 'T12'];
const revenueData = new Array(12).fill(0);
const orderData = new Array(12).fill(0);

// Xử lý dữ liệu monthly stats
if (monthlyData && monthlyData.length > 0) {
    monthlyData.forEach(item => {
        const monthIndex = item.month - 1;
        if (monthIndex >= 0 && monthIndex < 12) {
            revenueData[monthIndex] = parseFloat(item.total_revenue || 0);
            orderData[monthIndex] = parseInt(item.total_orders || 0);
        }
    });
}

new Chart(monthlyCtx, {
    type: 'bar',
    data: {
        labels: months,
        datasets: [{
            label: 'Doanh Thu ($)',
            data: revenueData,
            backgroundColor: 'rgba(54, 162, 235, 0.2)',
            borderColor: 'rgba(54, 162, 235, 1)',
            borderWidth: 1
        }, {
            label: 'Số Đơn Hàng',
            data: orderData,
            backgroundColor: 'rgba(255, 99, 132, 0.2)',
            borderColor: 'rgba(255, 99, 132, 1)',
            borderWidth: 1,
            yAxisID: 'y1'
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: true,
                position: 'left',
                title: {
                    display: true,
                    text: 'Doanh Thu ($)'
                }
            },
            y1: {
                beginAtZero: true,
                position: 'right',
                title: {
                    display: true,
                    text: 'Số Đơn Hàng'
                },
                grid: {
                    drawOnChartArea: false,
                },
            }
        }
    }
});

// Biểu đồ tròn trạng thái
const statusCtx = document.getElementById('statusPieChart').getContext('2d');
const statusData = @json($statusStats);

const statusLabels = {
    'pending': 'Chờ Xử Lý',
    'processing': 'Đang Xử Lý',
    'completed': 'Hoàn Thành',
    'cancelled': 'Đã Hủy'
};

const colors = ['#ffc107', '#17a2b8', '#28a745', '#dc3545'];

// Xử lý dữ liệu status stats
let chartLabels = [];
let chartData = [];
let chartColors = [];

if (statusData && statusData.length > 0) {
    statusData.forEach((item, index) => {
        chartLabels.push(statusLabels[item.status] || item.status);
        chartData.push(item.count);
        chartColors.push(colors[index % colors.length]);
    });
} else {
    // Dữ liệu mặc định nếu không có data
    chartLabels = ['Không có dữ liệu'];
    chartData = [1];
    chartColors = ['#e9ecef'];
}

new Chart(statusCtx, {
    type: 'pie',
    data: {
        labels: chartLabels,
        datasets: [{
            data: chartData,
            backgroundColor: chartColors,
            borderWidth: 2,
            borderColor: '#fff'
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'bottom',
            }
        }
    }
});
</script>
@endpush

@push('styles')
<style>
.card {
    border: none;
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    margin-bottom: 1rem;
}

.card-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
}

.badge {
    font-size: 0.875rem;
}

.table th {
    white-space: nowrap;
}

canvas {
    max-height: 300px;
}
</style>
@endpush
