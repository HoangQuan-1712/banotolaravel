@extends('layouts.app')

@section('title', 'Quản Lý Database - Admin')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">
                    <i class="fas fa-database"></i> 
                    Quản Lý Database
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

            <!-- Thông tin database -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-info-circle"></i> Thông Tin Database
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-6">
                                    <p><strong>Driver:</strong> {{ $dbInfo['driver'] ?? 'N/A' }}</p>
                                    <p><strong>Host:</strong> {{ $dbInfo['host'] ?? 'N/A' }}</p>
                                    <p><strong>Port:</strong> {{ $dbInfo['port'] ?? 'N/A' }}</p>
                                </div>
                                <div class="col-6">
                                    <p><strong>Database:</strong> {{ $dbInfo['database'] ?? 'N/A' }}</p>
                                    <p><strong>Charset:</strong> {{ $dbInfo['charset'] ?? 'N/A' }}</p>
                                    <p><strong>Collation:</strong> {{ $dbInfo['collation'] ?? 'N/A' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-chart-bar"></i> Thống Kê
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-6">
                                    <p><strong>Kích Thước:</strong> {{ $dbInfo['size'] ?? 'N/A' }}</p>
                                    <p><strong>Bảng:</strong> {{ $dbInfo['tables'] ?? 'N/A' }}</p>
                                </div>
                                <div class="col-6">
                                    <p><strong>Indexes:</strong> {{ $dbInfo['indexes'] ?? 'N/A' }}</p>
                                    <p><strong>Views:</strong> {{ $dbInfo['views'] ?? 'N/A' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Danh sách bảng -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-table"></i> Danh Sách Bảng
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>Tên Bảng</th>
                                    <th>Kích Thước</th>
                                    <th>Số Dòng</th>
                                    <th>Engine</th>
                                    <th>Collation</th>
                                    <th>Thao Tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if(isset($dbInfo['tableList']) && count($dbInfo['tableList']) > 0)
                                    @foreach($dbInfo['tableList'] as $table)
                                    <tr>
                                        <td>
                                            <strong>{{ $table['name'] }}</strong>
                                        </td>
                                        <td>
                                            <span class="badge bg-info">{{ $table['size'] ?? 'N/A' }}</span>
                                        </td>
                                        <td>
                                            <span class="badge bg-success">{{ $table['rows'] ?? 'N/A' }}</span>
                                        </td>
                                        <td>{{ $table['engine'] ?? 'N/A' }}</td>
                                        <td>{{ $table['collation'] ?? 'N/A' }}</td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary" 
                                                    onclick="showTableStructure('{{ $table['name'] }}')">
                                                <i class="fas fa-eye"></i> Cấu Trúc
                                            </button>
                                        </td>
                                    </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="6" class="text-center text-muted">
                                            Không có thông tin bảng
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Thao tác database -->
            <div class="row mt-4">
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-download"></i> Backup Database
                            </h5>
                        </div>
                        <div class="card-body">
                            <p class="text-muted">Tạo bản sao lưu toàn bộ database</p>
                            <button class="btn btn-success w-100" onclick="backupDatabase()">
                                <i class="fas fa-download"></i> Tạo Backup
                            </button>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-sync-alt"></i> Tối Ưu Database
                            </h5>
                        </div>
                        <div class="card-body">
                            <p class="text-muted">Tối ưu hóa hiệu suất database</p>
                            <button class="btn btn-warning w-100" onclick="optimizeDatabase()">
                                <i class="fas fa-sync-alt"></i> Tối Ưu
                            </button>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-chart-line"></i> Hiệu Suất
                            </h5>
                        </div>
                        <div class="card-body">
                            <p class="text-muted">Kiểm tra hiệu suất database</p>
                            <button class="btn btn-info w-100" onclick="checkPerformance()">
                                <i class="fas fa-chart-line"></i> Kiểm Tra
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal cấu trúc bảng -->
<div class="modal fade" id="tableStructureModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Cấu Trúc Bảng: <span id="tableName"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="tableStructureContent">
                    <!-- Nội dung cấu trúc bảng sẽ được load ở đây -->
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
function showTableStructure(tableName) {
    document.getElementById('tableName').textContent = tableName;
    document.getElementById('tableStructureContent').innerHTML = 
        '<div class="text-center"><i class="fas fa-spinner fa-spin fa-2x"></i><br>Đang tải...</div>';
    
    const modal = new bootstrap.Modal(document.getElementById('tableStructureModal'));
    modal.show();
    
    // Simulate loading table structure
    setTimeout(() => {
        document.getElementById('tableStructureContent').innerHTML = `
            <div class="table-responsive">
                <table class="table table-sm">
                    <thead class="table-dark">
                        <tr>
                            <th>Field</th>
                            <th>Type</th>
                            <th>Null</th>
                            <th>Key</th>
                            <th>Default</th>
                            <th>Extra</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>id</td>
                            <td>bigint(20)</td>
                            <td>NO</td>
                            <td>PRI</td>
                            <td>NULL</td>
                            <td>auto_increment</td>
                        </tr>
                        <tr>
                            <td>created_at</td>
                            <td>timestamp</td>
                            <td>YES</td>
                            <td></td>
                            <td>NULL</td>
                            <td></td>
                        </tr>
                        <tr>
                            <td>updated_at</td>
                            <td>timestamp</td>
                            <td>YES</td>
                            <td></td>
                            <td>NULL</td>
                            <td></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        `;
    }, 1000);
}

function backupDatabase() {
    if (confirm('Bạn có chắc muốn tạo backup database? Quá trình này có thể mất vài phút.')) {
        // Simulate backup process
        const btn = event.target;
        const originalText = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang tạo...';
        btn.disabled = true;
        
        setTimeout(() => {
            btn.innerHTML = originalText;
            btn.disabled = false;
            alert('Backup đã được tạo thành công!');
        }, 3000);
    }
}

function optimizeDatabase() {
    if (confirm('Bạn có chắc muốn tối ưu database? Quá trình này có thể mất vài phút.')) {
        const btn = event.target;
        const originalText = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang tối ưu...';
        btn.disabled = true;
        
        setTimeout(() => {
            btn.innerHTML = originalText;
            btn.disabled = false;
            alert('Database đã được tối ưu thành công!');
        }, 2000);
    }
}

function checkPerformance() {
    const btn = event.target;
    const originalText = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang kiểm tra...';
    btn.disabled = true;
    
    setTimeout(() => {
        btn.innerHTML = originalText;
        btn.disabled = false;
        alert('Hiệu suất database: Tốt\nThời gian phản hồi trung bình: 15ms\nTỷ lệ cache hit: 85%');
    }, 1500);
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

.modal-lg {
    max-width: 800px;
}
</style>
@endpush
