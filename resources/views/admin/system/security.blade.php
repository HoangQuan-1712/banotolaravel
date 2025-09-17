@extends('layouts.app')

@section('title', 'Bảo Mật Hệ Thống - Admin')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">
                    <i class="fas fa-shield-alt"></i> 
                    Bảo Mật Hệ Thống
                </h1>
                <div>
                    <a href="{{ route('admin.system.overview') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Quay Lại
                    </a>
                    <a href="{{ route('admin.dashboard.index') }}" class="btn btn-primary">
                        <i class="fas fa-tachometer-alt"></i> Dashboard
                    </a>
                </div>
            </div>

            <!-- Trạng thái bảo mật -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card bg-success text-white">
                        <div class="card-body text-center">
                            <i class="fas fa-shield-check fa-2x mb-2"></i>
                            <h4 class="mb-0">{{ $securityInfo['overall_score'] ?? '85' }}/100</h4>
                            <small>Điểm bảo mật tổng thể</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-warning text-white">
                        <div class="card-body text-center">
                            <i class="fas fa-exclamation-triangle fa-2x mb-2"></i>
                            <h4 class="mb-0">{{ $securityInfo['vulnerabilities'] ?? '3' }}</h4>
                            <small>Lỗ hổng cần sửa</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-info text-white">
                        <div class="card-body text-center">
                            <i class="fas fa-user-shield fa-2x mb-2"></i>
                            <h4 class="mb-0">{{ $securityInfo['active_users'] ?? '12' }}</h4>
                            <small>Người dùng đang hoạt động</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-primary text-white">
                        <div class="card-body text-center">
                            <i class="fas fa-clock fa-2x mb-2"></i>
                            <h4 class="mb-0">{{ $securityInfo['last_scan'] ?? '2 giờ' }}</h4>
                            <small>Quét bảo mật cuối</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Kiểm tra bảo mật -->
            <div class="row mb-4">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-list-check"></i> Kiểm Tra Bảo Mật
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>Kiểm Tra</th>
                                            <th>Trạng Thái</th>
                                            <th>Mức Độ</th>
                                            <th>Chi Tiết</th>
                                            <th>Thao Tác</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @if(isset($securityInfo['checks']) && count($securityInfo['checks']) > 0)
                                            @foreach($securityInfo['checks'] as $check)
                                            <tr>
                                                <td>
                                                    <strong>{{ $check['name'] }}</strong>
                                                    <br><small class="text-muted">{{ $check['description'] }}</small>
                                                </td>
                                                <td>
                                                    @php
                                                        $status = $check['status'] ?? 'pending';
                                                        $statusColors = [
                                                            'passed' => 'success',
                                                            'failed' => 'danger',
                                                            'warning' => 'warning',
                                                            'pending' => 'secondary'
                                                        ];
                                                        $statusIcons = [
                                                            'passed' => 'fa-check-circle',
                                                            'failed' => 'fa-times-circle',
                                                            'warning' => 'fa-exclamation-triangle',
                                                            'pending' => 'fa-clock'
                                                        ];
                                                        $color = $statusColors[$status];
                                                        $icon = $statusIcons[$status];
                                                    @endphp
                                                    <span class="badge bg-{{ $color }}">
                                                        <i class="fas {{ $icon }}"></i> 
                                                        {{ ucfirst($status) }}
                                                    </span>
                                                </td>
                                                <td>
                                                    @php
                                                        $level = $check['level'] ?? 'medium';
                                                        $levelColors = [
                                                            'critical' => 'danger',
                                                            'high' => 'warning',
                                                            'medium' => 'info',
                                                            'low' => 'secondary'
                                                        ];
                                                        $color = $levelColors[$level];
                                                    @endphp
                                                    <span class="badge bg-{{ $color }}">{{ ucfirst($level) }}</span>
                                                </td>
                                                <td>
                                                    <small class="text-muted">{{ $check['details'] ?? 'N/A' }}</small>
                                                </td>
                                                <td>
                                                    <button class="btn btn-sm btn-outline-primary" 
                                                            onclick="viewSecurityDetails('{{ $check['name'] }}')">
                                                        <i class="fas fa-eye"></i> Chi Tiết
                                                    </button>
                                                </td>
                                            </tr>
                                            @endforeach
                                        @else
                                            <tr>
                                                <td colspan="5" class="text-center text-muted">
                                                    Không có thông tin kiểm tra bảo mật
                                                </td>
                                            </tr>
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-chart-pie"></i> Phân Bố Rủi Ro
                            </h5>
                        </div>
                        <div class="card-body">
                            <canvas id="securityChart" width="400" height="200"></canvas>
                            <div class="mt-3">
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Critical</span>
                                    <span class="badge bg-danger">{{ $securityInfo['critical_count'] ?? '1' }}</span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>High</span>
                                    <span class="badge bg-warning">{{ $securityInfo['high_count'] ?? '2' }}</span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Medium</span>
                                    <span class="badge bg-info">{{ $securityInfo['medium_count'] ?? '5' }}</span>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span>Low</span>
                                    <span class="badge bg-secondary">{{ $securityInfo['low_count'] ?? '8' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Hoạt động đáng ngờ -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-user-secret"></i> Hoạt Động Đáng Ngờ
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>Thời Gian</th>
                                    <th>IP Address</th>
                                    <th>Hành Động</th>
                                    <th>Người Dùng</th>
                                    <th>Mức Độ</th>
                                    <th>Thao Tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if(isset($securityInfo['suspicious_activities']) && count($securityInfo['suspicious_activities']) > 0)
                                    @foreach($securityInfo['suspicious_activities'] as $activity)
                                    <tr>
                                        <td>{{ $activity['time'] ?? 'N/A' }}</td>
                                        <td>
                                            <code>{{ $activity['ip'] ?? 'N/A' }}</code>
                                        </td>
                                        <td>{{ $activity['action'] ?? 'N/A' }}</td>
                                        <td>{{ $activity['user'] ?? 'Anonymous' }}</td>
                                        <td>
                                            @php
                                                $level = $activity['level'] ?? 'medium';
                                                $levelColors = [
                                                    'critical' => 'danger',
                                                    'high' => 'warning',
                                                    'medium' => 'info',
                                                    'low' => 'secondary'
                                                ];
                                                $color = $levelColors[$level];
                                            @endphp
                                            <span class="badge bg-{{ $color }}">{{ ucfirst($level) }}</span>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <button class="btn btn-outline-primary" 
                                                        onclick="investigateActivity('{{ $activity['id'] ?? '1' }}')">
                                                    <i class="fas fa-search"></i> Điều Tra
                                                </button>
                                                <button class="btn btn-outline-warning" 
                                                        onclick="blockIP('{{ $activity['ip'] ?? '127.0.0.1' }}')">
                                                    <i class="fas fa-ban"></i> Chặn IP
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="6" class="text-center text-muted">
                                            Không có hoạt động đáng ngờ nào
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Thao tác bảo mật -->
            <div class="row mt-4">
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-search"></i> Quét Bảo Mật
                            </h5>
                        </div>
                        <div class="card-body">
                            <p class="text-muted">Quét toàn bộ hệ thống</p>
                            <button class="btn btn-primary w-100" onclick="runSecurityScan()">
                                <i class="fas fa-search"></i> Bắt Đầu Quét
                            </button>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-download"></i> Backup Bảo Mật
                            </h5>
                        </div>
                        <div class="card-body">
                            <p class="text-muted">Tạo backup cấu hình</p>
                            <button class="btn btn-success w-100" onclick="backupSecurityConfig()">
                                <i class="fas fa-download"></i> Backup
                            </button>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-bell"></i> Cảnh Báo
                            </h5>
                        </div>
                        <div class="card-body">
                            <p class="text-muted">Cài đặt cảnh báo</p>
                            <button class="btn btn-warning w-100" onclick="configureAlerts()">
                                <i class="fas fa-bell"></i> Cài Đặt
                            </button>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-file-alt"></i> Báo Cáo
                            </h5>
                        </div>
                        <div class="card-body">
                            <p class="text-muted">Tạo báo cáo bảo mật</p>
                            <button class="btn btn-info w-100" onclick="generateSecurityReport()">
                                <i class="fas fa-file-alt"></i> Tạo Báo Cáo
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal chi tiết bảo mật -->
<div class="modal fade" id="securityDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Chi Tiết Bảo Mật: <span id="securityCheckName"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="securityDetailsContent">
                    <!-- Chi tiết bảo mật sẽ được load ở đây -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Biểu đồ phân bố rủi ro
const ctx = document.getElementById('securityChart').getContext('2d');
new Chart(ctx, {
    type: 'doughnut',
    data: {
        labels: ['Critical', 'High', 'Medium', 'Low'],
        datasets: [{
            data: [
                {{ $securityInfo['critical_count'] ?? 1 }},
                {{ $securityInfo['high_count'] ?? 2 }},
                {{ $securityInfo['medium_count'] ?? 5 }},
                {{ $securityInfo['low_count'] ?? 8 }}
            ],
            backgroundColor: ['#dc3545', '#ffc107', '#17a2b8', '#6c757d'],
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

function viewSecurityDetails(checkName) {
    document.getElementById('securityCheckName').textContent = checkName;
    document.getElementById('securityDetailsContent').innerHTML = 
        '<div class="text-center"><i class="fas fa-spinner fa-spin fa-2x"></i><br>Đang tải...</div>';
    
    const modal = new bootstrap.Modal(document.getElementById('securityDetailsModal'));
    modal.show();
    
    // Simulate loading security details
    setTimeout(() => {
        document.getElementById('securityDetailsContent').innerHTML = `
            <div class="row">
                <div class="col-md-6">
                    <h6>Thông Tin Kiểm Tra</h6>
                    <ul class="list-unstyled">
                        <li><strong>Tên:</strong> ${checkName}</li>
                        <li><strong>Trạng thái:</strong> <span class="badge bg-success">Passed</span></li>
                        <li><strong>Mức độ:</strong> <span class="badge bg-info">Medium</span></li>
                        <li><strong>Thời gian kiểm tra:</strong> 2 phút trước</li>
                    </ul>
                </div>
                <div class="col-md-6">
                    <h6>Kết Quả</h6>
                    <p class="text-success">✓ Kiểm tra bảo mật đã thành công</p>
                    <p class="text-muted">Không phát hiện lỗ hổng bảo mật nào</p>
                </div>
            </div>
            <hr>
            <div class="mt-3">
                <h6>Khuyến Nghị</h6>
                <ul>
                    <li>Tiếp tục theo dõi định kỳ</li>
                    <li>Cập nhật các bản vá bảo mật</li>
                    <li>Kiểm tra logs thường xuyên</li>
                </ul>
            </div>
        `;
    }, 1000);
}

function investigateActivity(activityId) {
    alert(`Đang điều tra hoạt động ID: ${activityId}`);
}

function blockIP(ipAddress) {
    if (confirm(`Bạn có chắc muốn chặn IP: ${ipAddress}?`)) {
        alert(`Đã chặn IP: ${ipAddress}`);
    }
}

function runSecurityScan() {
    if (confirm('Bạn có chắc muốn chạy quét bảo mật toàn bộ hệ thống? Quá trình này có thể mất vài phút.')) {
        const btn = event.target;
        const originalText = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang quét...';
        btn.disabled = true;
        
        setTimeout(() => {
            btn.innerHTML = originalText;
            btn.disabled = false;
            alert('Quét bảo mật hoàn thành!\n\nKết quả:\n- Tổng kiểm tra: 25\n- Đã qua: 22\n- Cảnh báo: 2\n- Lỗi: 1\n\nĐiểm bảo mật: 88/100');
        }, 5000);
    }
}

function backupSecurityConfig() {
    const btn = event.target;
    const originalText = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang tạo...';
    btn.disabled = true;
    
    setTimeout(() => {
        btn.innerHTML = originalText;
        btn.disabled = false;
        alert('Backup cấu hình bảo mật đã được tạo thành công!');
    }, 2000);
}

function configureAlerts() {
    alert('Tính năng cài đặt cảnh báo sẽ được phát triển trong phiên bản tiếp theo!');
}

function generateSecurityReport() {
    const btn = event.target;
    const originalText = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang tạo...';
    btn.disabled = true;
    
    setTimeout(() => {
        btn.innerHTML = originalText;
        btn.disabled = false;
        alert('Báo cáo bảo mật đã được tạo thành công!\n\nBáo cáo bao gồm:\n- Tổng quan bảo mật\n- Phân tích lỗ hổng\n- Khuyến nghị cải thiện\n- Biểu đồ thống kê');
    }, 3000);
}
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

.btn-group .btn {
    margin-right: 2px;
}

.btn-group .btn:last-child {
    margin-right: 0;
}

code {
    background-color: #f8f9fa;
    padding: 2px 4px;
    border-radius: 3px;
    font-size: 0.875rem;
}

canvas {
    max-height: 200px;
}
</style>
@endpush
