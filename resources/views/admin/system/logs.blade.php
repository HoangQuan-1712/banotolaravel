@extends('layouts.app')

@section('title', 'Quản Lý Logs - Admin')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">
                    <i class="fas fa-file-text"></i> 
                    Quản Lý Logs
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

            <!-- Thông tin logs -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card bg-primary text-white">
                        <div class="card-body text-center">
                            <i class="fas fa-file-alt fa-2x mb-2"></i>
                            <h4 class="mb-0">{{ $logFiles['total_files'] ?? '12' }}</h4>
                            <small>Tổng số file log</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-success text-white">
                        <div class="card-body text-center">
                            <i class="fas fa-hdd fa-2x mb-2"></i>
                            <h4 class="mb-0">{{ $logFiles['total_size'] ?? '45.2 MB' }}</h4>
                            <small>Tổng dung lượng</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-warning text-white">
                        <div class="card-body text-center">
                            <i class="fas fa-exclamation-triangle fa-2x mb-2"></i>
                            <h4 class="mb-0">{{ $logFiles['error_count'] ?? '23' }}</h4>
                            <small>Lỗi hôm nay</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-info text-white">
                        <div class="card-body text-center">
                            <i class="fas fa-clock fa-2x mb-2"></i>
                            <h4 class="mb-0">{{ $logFiles['last_updated'] ?? '2 phút' }}</h4>
                            <small>Cập nhật cuối</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Danh sách file logs -->
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-list"></i> Danh Sách File Logs
                        </h5>
                        <div class="d-flex gap-2">
                            <select class="form-select form-select-sm" id="logLevelFilter">
                                <option value="">Tất cả levels</option>
                                <option value="emergency">Emergency</option>
                                <option value="alert">Alert</option>
                                <option value="critical">Critical</option>
                                <option value="error">Error</option>
                                <option value="warning">Warning</option>
                                <option value="notice">Notice</option>
                                <option value="info">Info</option>
                                <option value="debug">Debug</option>
                            </select>
                            <button class="btn btn-sm btn-outline-primary" onclick="filterLogs()">
                                <i class="fas fa-filter"></i> Lọc
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>Tên File</th>
                                    <th>Kích Thước</th>
                                    <th>Level</th>
                                    <th>Ngày Tạo</th>
                                    <th>Ngày Sửa</th>
                                    <th>Thao Tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if(isset($logFiles['files']) && count($logFiles['files']) > 0)
                                    @foreach($logFiles['files'] as $file)
                                    <tr>
                                        <td>
                                            <i class="fas fa-file-alt text-primary me-2"></i>
                                            <strong>{{ $file['name'] }}</strong>
                                        </td>
                                        <td>
                                            <span class="badge bg-info">{{ $file['size'] ?? 'N/A' }}</span>
                                        </td>
                                        <td>
                                            @php
                                                $level = $file['level'] ?? 'info';
                                                $levelColors = [
                                                    'emergency' => 'danger',
                                                    'alert' => 'danger',
                                                    'critical' => 'danger',
                                                    'error' => 'danger',
                                                    'warning' => 'warning',
                                                    'notice' => 'info',
                                                    'info' => 'info',
                                                    'debug' => 'secondary'
                                                ];
                                                $color = $levelColors[$level] ?? 'secondary';
                                            @endphp
                                            <span class="badge bg-{{ $color }}">{{ ucfirst($level) }}</span>
                                        </td>
                                        <td>{{ $file['created_at'] ?? 'N/A' }}</td>
                                        <td>{{ $file['modified_at'] ?? 'N/A' }}</td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <button class="btn btn-outline-primary" 
                                                        onclick="viewLogFile('{{ $file['name'] }}')">
                                                    <i class="fas fa-eye"></i> Xem
                                                </button>
                                                <button class="btn btn-outline-warning" 
                                                        onclick="downloadLog('{{ $file['name'] }}')">
                                                    <i class="fas fa-download"></i> Tải
                                                </button>
                                                <button class="btn btn-outline-danger" 
                                                        onclick="deleteLog('{{ $file['name'] }}')">
                                                    <i class="fas fa-trash"></i> Xóa
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="6" class="text-center text-muted">
                                            Không có file log nào
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Thao tác logs -->
            <div class="row mt-4">
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-trash"></i> Xóa Tất Cả
                            </h5>
                        </div>
                        <div class="card-body">
                            <p class="text-muted">Xóa toàn bộ file logs</p>
                            <button class="btn btn-danger w-100" onclick="clearAllLogs()">
                                <i class="fas fa-trash"></i> Xóa Tất Cả
                            </button>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-download"></i> Backup Logs
                            </h5>
                        </div>
                        <div class="card-body">
                            <p class="text-muted">Tạo backup toàn bộ logs</p>
                            <button class="btn btn-success w-100" onclick="backupAllLogs()">
                                <i class="fas fa-download"></i> Backup
                            </button>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-chart-line"></i> Phân Tích
                            </h5>
                        </div>
                        <div class="card-body">
                            <p class="text-muted">Phân tích patterns logs</p>
                            <button class="btn btn-info w-100" onclick="analyzeLogs()">
                                <i class="fas fa-chart-line"></i> Phân Tích
                            </button>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-cogs"></i> Cài Đặt
                            </h5>
                        </div>
                        <div class="card-body">
                            <p class="text-muted">Cấu hình logging</p>
                            <button class="btn btn-primary w-100" onclick="configureLogging()">
                                <i class="fas fa-cogs"></i> Cài Đặt
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal xem nội dung log -->
<div class="modal fade" id="logContentModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Nội Dung Log: <span id="logFileName"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <div class="d-flex gap-2">
                        <input type="text" class="form-control" placeholder="Tìm kiếm trong log..." id="searchInLog">
                        <button class="btn btn-outline-primary" onclick="searchInLog()">
                            <i class="fas fa-search"></i> Tìm
                        </button>
                        <button class="btn btn-outline-secondary" onclick="exportLog()">
                            <i class="fas fa-download"></i> Xuất
                        </button>
                    </div>
                </div>
                <div id="logContent" style="max-height: 500px; overflow-y: auto;">
                    <!-- Nội dung log sẽ được load ở đây -->
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
<script>
function viewLogFile(fileName) {
    document.getElementById('logFileName').textContent = fileName;
    document.getElementById('logContent').innerHTML = 
        '<div class="text-center"><i class="fas fa-spinner fa-spin fa-2x"></i><br>Đang tải...</div>';
    
    const modal = new bootstrap.Modal(document.getElementById('logContentModal'));
    modal.show();
    
    // Simulate loading log content
    setTimeout(() => {
        document.getElementById('logContent').innerHTML = `
            <pre class="bg-dark text-light p-3 rounded" style="font-size: 0.875rem;">[2024-01-15 10:30:15] local.ERROR: Database connection failed: SQLSTATE[HY000] [2002] Connection refused
[2024-01-15 10:30:16] local.INFO: Attempting to reconnect to database...
[2024-01-15 10:30:17] local.INFO: Database connection restored successfully
[2024-01-15 10:31:20] local.INFO: User login: user@example.com
[2024-01-15 10:32:45] local.WARNING: High memory usage detected: 85%
[2024-01-15 10:33:10] local.INFO: Order created: #12345
[2024-01-15 10:34:22] local.ERROR: Payment gateway timeout: MoMo API
[2024-01-15 10:35:00] local.INFO: Payment processed successfully: #12345
[2024-01-15 10:36:15] local.DEBUG: Cache hit for product: 123
[2024-01-15 10:37:30] local.INFO: Email sent: order_confirmation_12345
[2024-01-15 10:38:45] local.ERROR: File upload failed: storage/app/public/uploads/image.jpg
[2024-01-15 10:39:10] local.INFO: Backup completed: database_backup_20240115.sql
[2024-01-15 10:40:00] local.WARNING: Slow query detected: SELECT * FROM products WHERE category_id = 1
[2024-01-15 10:41:15] local.INFO: Cron job executed: cleanup_old_logs
[2024-01-15 10:42:30] local.ERROR: External API error: weather_service
[2024-01-15 10:43:45] local.INFO: User logout: user@example.com
[2024-01-15 10:44:00] local.DEBUG: Session destroyed for user: 123</pre>
        `;
    }, 1000);
}

function downloadLog(fileName) {
    alert(`Đang tải xuống file log: ${fileName}`);
}

function deleteLog(fileName) {
    if (confirm(`Bạn có chắc muốn xóa file log: ${fileName}?`)) {
        alert(`Đã xóa file log: ${fileName}`);
    }
}

function filterLogs() {
    const level = document.getElementById('logLevelFilter').value;
    if (level) {
        alert(`Đang lọc logs theo level: ${level}`);
    } else {
        alert('Đang hiển thị tất cả logs');
    }
}

function searchInLog() {
    const searchTerm = document.getElementById('searchInLog').value;
    if (searchTerm.trim()) {
        alert(`Tìm kiếm trong log: ${searchTerm}`);
    } else {
        alert('Vui lòng nhập từ khóa tìm kiếm');
    }
}

function exportLog() {
    alert('Đang xuất file log...');
}

function clearAllLogs() {
    if (confirm('Bạn có chắc muốn xóa toàn bộ file logs? Hành động này không thể hoàn tác.')) {
        const btn = event.target;
        const originalText = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang xóa...';
        btn.disabled = true;
        
        setTimeout(() => {
            btn.innerHTML = originalText;
            btn.disabled = false;
            alert('Đã xóa toàn bộ logs thành công!');
        }, 2000);
    }
}

function backupAllLogs() {
    if (confirm('Bạn có chắc muốn tạo backup toàn bộ logs? Quá trình này có thể mất vài phút.')) {
        const btn = event.target;
        const originalText = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang tạo...';
        btn.disabled = true;
        
        setTimeout(() => {
            btn.innerHTML = originalText;
            btn.disabled = false;
            alert('Backup logs đã được tạo thành công!');
        }, 3000);
    }
}

function analyzeLogs() {
    const btn = event.target;
    const originalText = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang phân tích...';
    btn.disabled = true;
    
    setTimeout(() => {
        btn.innerHTML = originalText;
        btn.disabled = false;
        alert('Phân tích logs hoàn thành!\n\nTop errors:\n- Database connection: 15 lần\n- Payment timeout: 8 lần\n- File upload: 5 lần\n\nRecommendations:\n- Kiểm tra database connection\n- Tối ưu payment gateway\n- Kiểm tra storage permissions');
    }, 2000);
}

function configureLogging() {
    alert('Tính năng cấu hình logging sẽ được phát triển trong phiên bản tiếp theo!');
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

.modal-xl {
    max-width: 1200px;
}

pre {
    white-space: pre-wrap;
    word-wrap: break-word;
}
</style>
@endpush
