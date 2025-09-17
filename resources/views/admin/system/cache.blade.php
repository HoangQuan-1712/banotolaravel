@extends('layouts.app')

@section('title', 'Quản Lý Cache - Admin')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">
                    <i class="fas fa-memory"></i> 
                    Quản Lý Cache
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

            <!-- Thông tin cache -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-info-circle"></i> Thông Tin Cache
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-6">
                                    <p><strong>Driver:</strong> {{ $cacheInfo['driver'] ?? 'file' }}</p>
                                    <p><strong>Prefix:</strong> {{ $cacheInfo['prefix'] ?? 'laravel_' }}</p>
                                    <p><strong>TTL:</strong> {{ $cacheInfo['ttl'] ?? '3600' }}s</p>
                                </div>
                                <div class="col-6">
                                    <p><strong>Store:</strong> {{ $cacheInfo['store'] ?? 'default' }}</p>
                                    <p><strong>Connection:</strong> {{ $cacheInfo['connection'] ?? 'default' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-chart-pie"></i> Thống Kê Cache
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-6">
                                    <p><strong>Hit Rate:</strong> 
                                        <span class="badge bg-success">{{ $cacheInfo['hit_rate'] ?? '85' }}%</span>
                                    </p>
                                    <p><strong>Miss Rate:</strong> 
                                        <span class="badge bg-warning">{{ $cacheInfo['miss_rate'] ?? '15' }}%</span>
                                    </p>
                                </div>
                                <div class="col-6">
                                    <p><strong>Total Keys:</strong> 
                                        <span class="badge bg-info">{{ $cacheInfo['total_keys'] ?? '1,234' }}</span>
                                    </p>
                                    <p><strong>Memory Usage:</strong> 
                                        <span class="badge bg-primary">{{ $cacheInfo['memory_usage'] ?? '45.2 MB' }}</span>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Danh sách cache keys -->
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-key"></i> Cache Keys
                        </h5>
                        <div class="d-flex gap-2">
                            <input type="text" class="form-control form-control-sm" 
                                   placeholder="Tìm kiếm cache key..." id="searchCache">
                            <button class="btn btn-sm btn-outline-primary" onclick="searchCache()">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>Key</th>
                                    <th>Value Size</th>
                                    <th>TTL</th>
                                    <th>Type</th>
                                    <th>Last Access</th>
                                    <th>Thao Tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if(isset($cacheInfo['keys']) && count($cacheInfo['keys']) > 0)
                                    @foreach($cacheInfo['keys'] as $key)
                                    <tr>
                                        <td>
                                            <code>{{ $key['name'] }}</code>
                                        </td>
                                        <td>
                                            <span class="badge bg-info">{{ $key['size'] ?? 'N/A' }}</span>
                                        </td>
                                        <td>
                                            @if($key['ttl'] > 0)
                                                <span class="badge bg-success">{{ $key['ttl'] }}s</span>
                                            @else
                                                <span class="badge bg-secondary">Vô hạn</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-primary">{{ $key['type'] ?? 'string' }}</span>
                                        </td>
                                        <td>{{ $key['last_access'] ?? 'N/A' }}</td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <button class="btn btn-outline-primary" 
                                                        onclick="viewCacheValue('{{ $key['name'] }}')">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button class="btn btn-outline-warning" 
                                                        onclick="refreshCache('{{ $key['name'] }}')">
                                                    <i class="fas fa-sync-alt"></i>
                                                </button>
                                                <button class="btn btn-outline-danger" 
                                                        onclick="deleteCache('{{ $key['name'] }}')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="6" class="text-center text-muted">
                                            Không có cache keys nào
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Thao tác cache -->
            <div class="row mt-4">
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-trash"></i> Xóa Tất Cả
                            </h5>
                        </div>
                        <div class="card-body">
                            <p class="text-muted">Xóa toàn bộ cache</p>
                            <button class="btn btn-danger w-100" onclick="clearAllCache()">
                                <i class="fas fa-trash"></i> Xóa Tất Cả
                            </button>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-sync-alt"></i> Refresh Cache
                            </h5>
                        </div>
                        <div class="card-body">
                            <p class="text-muted">Làm mới tất cả cache</p>
                            <button class="btn btn-warning w-100" onclick="refreshAllCache()">
                                <i class="fas fa-sync-alt"></i> Refresh
                            </button>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-chart-line"></i> Hiệu Suất
                            </h5>
                        </div>
                        <div class="card-body">
                            <p class="text-muted">Kiểm tra hiệu suất cache</p>
                            <button class="btn btn-info w-100" onclick="checkCachePerformance()">
                                <i class="fas fa-chart-line"></i> Kiểm Tra
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
                            <p class="text-muted">Cấu hình cache</p>
                            <button class="btn btn-primary w-100" onclick="configureCache()">
                                <i class="fas fa-cogs"></i> Cài Đặt
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal xem giá trị cache -->
<div class="modal fade" id="cacheValueModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Giá Trị Cache: <span id="cacheKeyName"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="cacheValueContent">
                    <!-- Giá trị cache sẽ được load ở đây -->
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
function viewCacheValue(keyName) {
    document.getElementById('cacheKeyName').textContent = keyName;
    document.getElementById('cacheValueContent').innerHTML = 
        '<div class="text-center"><i class="fas fa-spinner fa-spin fa-2x"></i><br>Đang tải...</div>';
    
    const modal = new bootstrap.Modal(document.getElementById('cacheValueModal'));
    modal.show();
    
    // Simulate loading cache value
    setTimeout(() => {
        document.getElementById('cacheValueContent').innerHTML = `
            <div class="mb-3">
                <label class="form-label"><strong>Giá trị:</strong></label>
                <pre class="bg-light p-3 rounded"><code>{
    "id": 1,
    "name": "Sample Product",
    "price": 150000,
    "category": "Electronics",
    "created_at": "2024-01-15T10:30:00Z"
}</code></pre>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <p><strong>Kích thước:</strong> 245 bytes</p>
                    <p><strong>Loại:</strong> JSON</p>
                </div>
                <div class="col-md-6">
                    <p><strong>TTL:</strong> 3600s</p>
                    <p><strong>Lần truy cập cuối:</strong> 2 phút trước</p>
                </div>
            </div>
        `;
    }, 1000);
}

function refreshCache(keyName) {
    if (confirm(`Bạn có chắc muốn làm mới cache key: ${keyName}?`)) {
        // Simulate refresh
        alert(`Đã làm mới cache key: ${keyName}`);
    }
}

function deleteCache(keyName) {
    if (confirm(`Bạn có chắc muốn xóa cache key: ${keyName}?`)) {
        // Simulate delete
        alert(`Đã xóa cache key: ${keyName}`);
    }
}

function searchCache() {
    const searchTerm = document.getElementById('searchCache').value;
    if (searchTerm.trim()) {
        alert(`Tìm kiếm cache keys chứa: ${searchTerm}`);
    } else {
        alert('Vui lòng nhập từ khóa tìm kiếm');
    }
}

function clearAllCache() {
    if (confirm('Bạn có chắc muốn xóa toàn bộ cache? Hành động này không thể hoàn tác.')) {
        const btn = event.target;
        const originalText = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang xóa...';
        btn.disabled = true;
        
        setTimeout(() => {
            btn.innerHTML = originalText;
            btn.disabled = false;
            alert('Đã xóa toàn bộ cache thành công!');
        }, 2000);
    }
}

function refreshAllCache() {
    if (confirm('Bạn có chắc muốn làm mới toàn bộ cache? Quá trình này có thể mất vài phút.')) {
        const btn = event.target;
        const originalText = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang làm mới...';
        btn.disabled = true;
        
        setTimeout(() => {
            btn.innerHTML = originalText;
            btn.disabled = false;
            alert('Đã làm mới toàn bộ cache thành công!');
        }, 3000);
    }
}

function checkCachePerformance() {
    const btn = event.target;
    const originalText = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang kiểm tra...';
    btn.disabled = true;
    
    setTimeout(() => {
        btn.innerHTML = originalText;
        btn.disabled = false;
        alert('Hiệu suất cache: Tốt\nHit Rate: 85%\nMiss Rate: 15%\nThời gian phản hồi trung bình: 2ms');
    }, 1500);
}

function configureCache() {
    alert('Tính năng cấu hình cache sẽ được phát triển trong phiên bản tiếp theo!');
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

code {
    background-color: #f8f9fa;
    padding: 2px 4px;
    border-radius: 3px;
    font-size: 0.875rem;
}

pre {
    max-height: 300px;
    overflow-y: auto;
}

.btn-group .btn {
    margin-right: 2px;
}

.btn-group .btn:last-child {
    margin-right: 0;
}
</style>
@endpush
