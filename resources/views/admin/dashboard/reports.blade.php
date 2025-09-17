@extends('layouts.app')

@section('title', 'Báo Cáo Hệ Thống - Admin Dashboard')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">
                    <i class="fas fa-file-alt"></i> 
                    Báo Cáo Hệ Thống
                </h1>
                <div>
                    <button onclick="window.print()" class="btn btn-secondary me-2">
                        <i class="fas fa-print"></i> In Báo Cáo
                    </button>
                    <a href="{{ route('admin.dashboard.index') }}" class="btn btn-primary">
                        <i class="fas fa-tachometer-alt"></i> Dashboard
                    </a>
                </div>
            </div>

            <!-- Báo cáo doanh thu theo tháng -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-line"></i> Báo Cáo Doanh Thu Theo Tháng ({{ now()->year }})
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-8">
                            <canvas id="revenueChart" width="400" height="200"></canvas>
                        </div>
                        <div class="col-md-4">
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Tháng</th>
                                            <th class="text-end">Doanh Thu</th>
                                            <th class="text-end">Đơn Hàng</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($monthlyRevenue as $revenue)
                                            <tr>
                                                <td>{{ $revenue->month }}/{{ $revenue->year }}</td>
                                                <td class="text-end text-success fw-bold">
                                                    ${{ number_format($revenue->revenue, 0, ',', '.') }}
                                                </td>
                                                <td class="text-end">
                                                    <span class="badge bg-primary">{{ $revenue->orders_count }}</span>
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

            <!-- Biểu đồ doanh thu theo danh mục và năm -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-chart-pie"></i> Doanh Thu Theo Danh Mục ({{ now()->year }})
                            </h5>
                        </div>
                        <div class="card-body">
                            <canvas id="categoryChart" width="400" height="200"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-chart-bar"></i> Doanh Thu Theo Năm
                            </h5>
                        </div>
                        <div class="card-body">
                            <canvas id="yearlyChart" width="400" height="200"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bảng chi tiết danh mục -->
            <div class="row mb-4">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-boxes"></i> Chi Tiết Doanh Thu Danh Mục
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>Danh Mục</th>
                                            <th class="text-end">Doanh Thu</th>
                                            <th class="text-center">Đơn Hàng</th>
                                            <th class="text-center">SP Bán</th>
                                            <th class="text-end">TB/Đơn</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($categoryRevenue as $stat)
                                            <tr>
                                                <td>
                                                    <strong>{{ $stat->category_name }}</strong>
                                                </td>
                                                <td class="text-end text-success fw-bold">
                                                    ${{ number_format($stat->revenue, 0, ',', '.') }}
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge bg-info">{{ $stat->orders_count }}</span>
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge bg-warning text-dark">{{ $stat->products_sold }}</span>
                                                </td>
                                                <td class="text-end">
                                                    ${{ number_format($stat->revenue / $stat->orders_count, 0, ',', '.') }}
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

            <!-- Biểu đồ theo thời gian -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-calendar-day"></i> Doanh Thu Theo Ngày (Tháng {{ now()->month }})
                            </h5>
                        </div>
                        <div class="card-body">
                            <canvas id="dailyChart" width="400" height="200"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-clock"></i> Doanh Thu Theo Giờ (Hôm Nay)
                            </h5>
                        </div>
                        <div class="card-body">
                            <canvas id="hourlyChart" width="400" height="200"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Báo cáo người dùng -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-users"></i> Báo Cáo Người Dùng
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>Vai Trò</th>
                                            <th class="text-center">Tổng Số</th>
                                            <th class="text-center">Mới Tháng Này</th>
                                            <th class="text-center">Tỷ Lệ</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            $totalUsers = $userStats->sum('count');
                                        @endphp
                                        @foreach($userStats as $stat)
                                            <tr>
                                                <td>
                                                    <strong>
                                                        @if($stat->role == 'admin')
                                                            <i class="fas fa-user-shield text-danger"></i> Admin
                                                        @else
                                                            <i class="fas fa-user text-primary"></i> Khách Hàng
                                                        @endif
                                                    </strong>
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge bg-primary fs-6">{{ $stat->count }}</span>
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge bg-success">{{ $stat->new_this_month }}</span>
                                                </td>
                                                <td class="text-center">
                                                    <span class="text-muted">
                                                        {{ $totalUsers > 0 ? round(($stat->count / $totalUsers) * 100, 1) : 0 }}%
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

            <!-- Tổng kết báo cáo -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-clipboard-check"></i> Tổng Kết Báo Cáo
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="text-center p-3 bg-light rounded">
                                <h4 class="text-primary">
                                    ${{ number_format($monthlyRevenue->sum('revenue'), 0, ',', '.') }}
                                </h4>
                                <small class="text-muted">Tổng Doanh Thu {{ now()->year }}</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center p-3 bg-light rounded">
                                <h4 class="text-success">
                                    {{ $monthlyRevenue->sum('orders_count') }}
                                </h4>
                                <small class="text-muted">Tổng Đơn Hàng {{ now()->year }}</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center p-3 bg-light rounded">
                                <h4 class="text-info">
                                    {{ $productStats->sum('product_count') }}
                                </h4>
                                <small class="text-muted">Tổng Sản Phẩm</small>
                            </div>
                        </div>
                    </div>
                    
                    <hr class="my-4">
                    
                    <div class="row">
                        <div class="col-12">
                            <h6>Ghi Chú Báo Cáo:</h6>
                            <ul class="list-unstyled">
                                <li><i class="fas fa-check text-success"></i> Báo cáo được tạo tự động vào {{ now()->format('d/m/Y H:i') }}</li>
                                <li><i class="fas fa-check text-success"></i> Dữ liệu doanh thu chỉ tính các đơn hàng đã hoàn thành</li>
                                <li><i class="fas fa-check text-success"></i> Thống kê người dùng mới tính trong 30 ngày gần nhất</li>
                                <li><i class="fas fa-check text-success"></i> Giá trung bình được tính theo từng danh mục sản phẩm</li>
                            </ul>
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
// Biểu đồ doanh thu
const ctx = document.getElementById('revenueChart').getContext('2d');
const revenueData = @json($monthlyRevenue);

const months = ['T1', 'T2', 'T3', 'T4', 'T5', 'T6', 'T7', 'T8', 'T9', 'T10', 'T11', 'T12'];
const revenues = new Array(12).fill(0);
const orders = new Array(12).fill(0);

if (revenueData && revenueData.length > 0) {
    revenueData.forEach(item => {
        const monthIndex = item.month - 1;
        if (monthIndex >= 0 && monthIndex < 12) {
            revenues[monthIndex] = parseFloat(item.revenue || 0);
            orders[monthIndex] = parseInt(item.orders_count || 0);
        }
    });
}

new Chart(ctx, {
    type: 'line',
    data: {
        labels: months,
        datasets: [{
            label: 'Doanh Thu ($)',
            data: revenues,
            borderColor: 'rgb(75, 192, 192)',
            backgroundColor: 'rgba(75, 192, 192, 0.2)',
            tension: 0.1,
            fill: true
        }, {
            label: 'Số Đơn Hàng',
            data: orders,
            borderColor: 'rgb(255, 99, 132)',
            backgroundColor: 'rgba(255, 99, 132, 0.2)',
            tension: 0.1,
            yAxisID: 'y1'
        }]
    },
    options: {
        responsive: true,
        interaction: {
            mode: 'index',
            intersect: false,
        },
        scales: {
            y: {
                type: 'linear',
                display: true,
                position: 'left',
                title: {
                    display: true,
                    text: 'Doanh Thu ($)'
                }
            },
            y1: {
                type: 'linear',
                display: true,
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

// Biểu đồ doanh thu theo danh mục
const categoryCtx = document.getElementById('categoryChart').getContext('2d');
const categoryData = @json($categoryRevenue);

const categoryLabels = categoryData.map(item => item.category_name || 'N/A');
const categoryRevenues = categoryData.map(item => parseFloat(item.revenue || 0));
const categoryColors = [
    '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF', 
    '#FF9F40', '#FF6384', '#C9CBCF', '#4BC0C0', '#FF6384'
];

new Chart(categoryCtx, {
    type: 'doughnut',
    data: {
        labels: categoryLabels.length > 0 ? categoryLabels : ['Chưa có dữ liệu'],
        datasets: [{
            data: categoryRevenues.length > 0 ? categoryRevenues : [1],
            backgroundColor: categoryColors,
            borderWidth: 2,
            borderColor: '#fff'
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'bottom',
                labels: {
                    padding: 20,
                    usePointStyle: true
                }
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        if (categoryRevenues.length === 0) return 'Chưa có dữ liệu';
                        const value = context.parsed;
                        const total = categoryRevenues.reduce((a, b) => a + b, 0);
                        const percentage = ((value / total) * 100).toFixed(1);
                        return context.label + ': $' + value.toLocaleString() + ' (' + percentage + '%)';
                    }
                }
            }
        }
    }
});

// Biểu đồ doanh thu theo năm
const yearlyCtx = document.getElementById('yearlyChart').getContext('2d');
const yearlyData = @json($yearlyRevenue);

const yearLabels = yearlyData.map(item => item.year.toString());
const yearRevenues = yearlyData.map(item => parseFloat(item.revenue || 0));
const yearOrders = yearlyData.map(item => parseInt(item.orders_count || 0));

new Chart(yearlyCtx, {
    type: 'bar',
    data: {
        labels: yearLabels.length > 0 ? yearLabels : ['Chưa có dữ liệu'],
        datasets: [{
            label: 'Doanh Thu ($)',
            data: yearRevenues.length > 0 ? yearRevenues : [0],
            backgroundColor: 'rgba(75, 192, 192, 0.6)',
            borderColor: 'rgba(75, 192, 192, 1)',
            borderWidth: 2,
            yAxisID: 'y'
        }, {
            label: 'Số Đơn Hàng',
            data: yearOrders.length > 0 ? yearOrders : [0],
            backgroundColor: 'rgba(255, 99, 132, 0.6)',
            borderColor: 'rgba(255, 99, 132, 1)',
            borderWidth: 2,
            yAxisID: 'y1'
        }]
    },
    options: {
        responsive: true,
        interaction: {
            mode: 'index',
            intersect: false,
        },
        scales: {
            y: {
                type: 'linear',
                display: true,
                position: 'left',
                beginAtZero: true,
                title: {
                    display: true,
                    text: 'Doanh Thu ($)'
                }
            },
            y1: {
                type: 'linear',
                display: true,
                position: 'right',
                beginAtZero: true,
                title: {
                    display: true,
                    text: 'Số Đơn Hàng'
                },
                grid: {
                    drawOnChartArea: false,
                },
            }
        },
        plugins: {
            tooltip: {
                callbacks: {
                    label: function(context) {
                        if (context.datasetIndex === 0) {
                            return 'Doanh thu: $' + context.parsed.y.toLocaleString();
                        } else {
                            return 'Đơn hàng: ' + context.parsed.y.toLocaleString();
                        }
                    }
                }
            }
        }
    }
});

// Biểu đồ doanh thu theo ngày
const dailyCtx = document.getElementById('dailyChart').getContext('2d');
const dailyData = @json($dailyRevenue);

const daysInMonth = new Date(new Date().getFullYear(), new Date().getMonth() + 1, 0).getDate();
const dailyLabels = Array.from({length: daysInMonth}, (_, i) => i + 1);
const dailyRevenues = new Array(daysInMonth).fill(0);

if (dailyData && dailyData.length > 0) {
    dailyData.forEach(item => {
        const dayIndex = item.day - 1;
        if (dayIndex >= 0 && dayIndex < daysInMonth) {
            dailyRevenues[dayIndex] = parseFloat(item.revenue || 0);
        }
    });
}

new Chart(dailyCtx, {
    type: 'bar',
    data: {
        labels: dailyLabels,
        datasets: [{
            label: 'Doanh Thu ($)',
            data: dailyRevenues,
            backgroundColor: 'rgba(54, 162, 235, 0.6)',
            borderColor: 'rgba(54, 162, 235, 1)',
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: true,
                title: {
                    display: true,
                    text: 'Doanh Thu ($)'
                }
            },
            x: {
                title: {
                    display: true,
                    text: 'Ngày'
                }
            }
        },
        plugins: {
            tooltip: {
                callbacks: {
                    label: function(context) {
                        return 'Doanh thu: $' + context.parsed.y.toLocaleString();
                    }
                }
            }
        }
    }
});

// Biểu đồ doanh thu theo giờ
const hourlyCtx = document.getElementById('hourlyChart').getContext('2d');
const hourlyData = @json($hourlyRevenue);

const hourlyLabels = Array.from({length: 24}, (_, i) => i + ':00');
const hourlyRevenues = new Array(24).fill(0);

if (hourlyData && hourlyData.length > 0) {
    hourlyData.forEach(item => {
        const hour = parseInt(item.hour);
        if (hour >= 0 && hour < 24) {
            hourlyRevenues[hour] = parseFloat(item.revenue || 0);
        }
    });
}

new Chart(hourlyCtx, {
    type: 'line',
    data: {
        labels: hourlyLabels,
        datasets: [{
            label: 'Doanh Thu ($)',
            data: hourlyRevenues,
            borderColor: 'rgb(255, 99, 132)',
            backgroundColor: 'rgba(255, 99, 132, 0.1)',
            tension: 0.4,
            fill: true,
            pointBackgroundColor: 'rgb(255, 99, 132)',
            pointBorderColor: '#fff',
            pointBorderWidth: 2,
            pointRadius: 4
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: true,
                title: {
                    display: true,
                    text: 'Doanh Thu ($)'
                }
            },
            x: {
                title: {
                    display: true,
                    text: 'Giờ'
                }
            }
        },
        plugins: {
            tooltip: {
                callbacks: {
                    label: function(context) {
                        return 'Doanh thu: $' + context.parsed.y.toLocaleString();
                    }
                }
            }
        }
    }
});
</script>
@endpush

@push('styles')
<style>
@media print {
    .btn, .card-header {
        -webkit-print-color-adjust: exact;
    }
    
    .no-print {
        display: none !important;
    }
}

.card {
    border: none;
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    margin-bottom: 1.5rem;
}

.card-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
    font-weight: 500;
}

.badge {
    font-size: 0.875rem;
}

canvas {
    max-height: 300px;
}
</style>
@endpush
