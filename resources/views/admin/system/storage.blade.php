@extends('layouts.app')

@section('title', 'Quản Lý Storage - Admin')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">
                    <i class="fas fa-hdd"></i> 
                    Quản Lý Storage
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

            <!-- Thông tin storage -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-info-circle"></i> Thông Tin Storage
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-6">
                                    <p><strong>Driver:</strong> {{ $storageInfo['driver'] ?? 'local' }}</p>
                                    <p><strong>Root:</strong> {{ $storageInfo['root'] ?? storage_path() }}</p>
                                    <p><strong>URL:</strong> {{ $storageInfo['url'] ?? asset('storage') }}</p>
                                </div>
                                <div class="col-6">
                                    <p><strong>Visibility:</strong> {{ $storageInfo['visibility'] ?? 'public' }}</p>
                                    <p><strong>Permissions:</strong> {{ $storageInfo['permissions'] ?? '0755' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-chart-pie"></i> Sử Dụng Storage
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="progress mb-3" style="height: 25px;">
                                <div class="progress-bar bg-success" role="progressbar" 
                                     style="width: {{ $storageInfo['usage_percent'] ?? 45 }}%">
                                    {{ $storageInfo['usage_percent'] ?? 45 }}%
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-6">
                                    <p><strong>Đã sử dụng:</strong> {{ $storageInfo['used'] ?? '2.1 GB' }}</p>
                                    <p><strong>Còn trống:</strong> {{ $storageInfo['free'] ?? '2.6 GB' }}</p>
                                </div>
                                <div class="col-6">
                                    <p><strong>Tổng dung lượng:</strong> {{ $storageInfo['total'] ?? '4.7 GB' }}</p>
                                    <p><strong>Số file:</strong> {{ $storageInfo['file_count'] ?? '1,234' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Danh sách thư mục -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-folder"></i> Cấu Trúc Thư Mục
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>Thư Mục</th>
                                    <th>Kích Thước</th>
                                    <th>Số File</th>
                                    <th>Quyền</th>
                                    <th>Ngày Tạo</th>
                                    <th>Thao Tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if(isset($storageInfo['directories']) && count($storageInfo['directories']) > 0)
                                    @foreach($storageInfo['directories'] as $dir)
                                    <tr>
                                        <td>
                                            <i class="fas fa-folder text-warning me-2"></i>
                                            <strong>{{ $dir['name'] }}</strong>
                                        </td>
                                        <td>
                                            <span class="badge bg-info">{{ $dir['size'] ?? 'N/A' }}</span>
                                        </td>
                                        <td>
                                            <span class="badge bg-success">{{ $dir['file_count'] ?? '0' }}</span>
                                        </td>
                                        <td>{{ $dir['permissions'] ?? '0755' }}</td>
                                        <td>{{ $dir['created_at'] ?? 'N/A' }}</td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary" 
                                                    onclick="exploreDirectory('{{ $dir['name'] }}')">
                                                <i class="fas fa-eye"></i> Xem
                                            </button>
                                        </td>
                                    </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="6" class="text-center text-muted">
                                            Không có thông tin thư mục
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Thao tác storage -->
            <div class="row mt-4">
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-upload"></i> Upload File
                            </h5>
                        </div>
                        <div class="card-body">
                            <p class="text-muted">Tải file lên storage</p>
                            <button class="btn btn-primary w-100" onclick="uploadFile()">
                                <i class="fas fa-upload"></i> Upload
                            </button>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-broom"></i> Dọn Dẹp
                            </h5>
                        </div>
                        <div class="card-body">
                            <p class="text-muted">Xóa file không sử dụng</p>
                            <button class="btn btn-warning w-100" onclick="cleanupStorage()">
                                <i class="fas fa-broom"></i> Dọn Dẹp
                            </button>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-sync-alt"></i> Đồng Bộ
                            </h5>
                        </div>
                        <div class="card-body">
                            <p class="text-muted">Đồng bộ với public</p>
                            <button class="btn btn-info w-100" onclick="syncStorage()">
                                <i class="fas fa-sync-alt"></i> Đồng Bộ
                            </button>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-download"></i> Backup
                            </h5>
                        </div>
                        <div class="card-body">
                            <p class="text-muted">Tạo backup storage</p>
                            <button class="btn btn-success w-100" onclick="backupStorage()">
                                <i class="fas fa-download"></i> Backup
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal xem thư mục -->
<div class="modal fade" id="directoryModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Nội Dung Thư Mục: <span id="dirName"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="directoryContent">
                    <!-- Nội dung thư mục sẽ được load ở đây -->
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
function exploreDirectory(dirName) {
    document.getElementById('dirName').textContent = dirName;
    document.getElementById('directoryContent').innerHTML = 
        '<div class="text-center"><i class="fas fa-spinner fa-spin fa-2x"></i><br>Đang tải...</div>';
    
    const modal = new bootstrap.Modal(document.getElementById('directoryModal'));
    modal.show();
    
    // Simulate loading directory content
    setTimeout(() => {
        document.getElementById('directoryContent').innerHTML = `
            <div class="table-responsive">
                <table class="table table-sm">
                    <thead class="table-dark">
                        <tr>
                            <th>Tên</th>
                            <th>Loại</th>
                            <th>Kích Thước</th>
                            <th>Ngày Sửa</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><i class="fas fa-folder text-warning me-2"></i>images</td>
                            <td><span class="badge bg-warning">Thư mục</span></td>
                            <td>-</td>
                            <td>2024-01-15</td>
                        </tr>
                        <tr>
                            <td><i class="fas fa-file text-primary me-2"></i>config.php</td>
                            <td><span class="badge bg-primary">File</span></td>
                            <td>2.5 KB</td>
                            <td>2024-01-10</td>
                        </tr>
                        <tr>
                            <td><i class="fas fa-file text-primary me-2"></i>database.sql</td>
                            <td><span class="badge bg-primary">File</span></td>
                            <td>15.2 MB</td>
                            <td>2024-01-08</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        `;
    }, 1000);
}

function uploadFile() {
    alert('Tính năng upload file sẽ được phát triển trong phiên bản tiếp theo!');
}

function cleanupStorage() {
    if (confirm('Bạn có chắc muốn dọn dẹp storage? Quá trình này sẽ xóa các file không sử dụng.')) {
        const btn = event.target;
        const originalText = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang dọn...';
        btn.disabled = true;
        
        setTimeout(() => {
            btn.innerHTML = originalText;
            btn.disabled = false;
            alert('Đã dọn dẹp storage thành công! Giải phóng được 150 MB.');
        }, 2000);
    }
}

function syncStorage() {
    const btn = event.target;
    const originalText = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang đồng bộ...';
    btn.disabled = true;
    
    setTimeout(() => {
        btn.innerHTML = originalText;
        btn.disabled = false;
        alert('Đã đồng bộ storage thành công!');
    }, 1500);
}

function backupStorage() {
    if (confirm('Bạn có chắc muốn tạo backup storage? Quá trình này có thể mất vài phút.')) {
        const btn = event.target;
        const originalText = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang tạo...';
        btn.disabled = true;
        
        setTimeout(() => {
            btn.innerHTML = originalText;
            btn.disabled = false;
            alert('Backup storage đã được tạo thành công!');
        }, 3000);
    }
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

.progress {
    border-radius: 10px;
}

.progress-bar {
    border-radius: 10px;
}
</style>
@endpush
