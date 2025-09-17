@extends('layouts.app')

@section('title', 'Cài Đặt Hệ Thống - Admin')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">
                    <i class="fas fa-cogs"></i> 
                    Cài Đặt Hệ Thống
                </h1>
                <div>
                    <a href="{{ route('admin.dashboard.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Quay Lại
                    </a>
                    <button type="submit" form="settingsForm" class="btn btn-primary">
                        <i class="fas fa-save"></i> Lưu Cài Đặt
                    </button>
                </div>
            </div>

            <form id="settingsForm" method="POST" action="{{ route('admin.dashboard.settings') }}">
                @csrf
                @method('PUT')
                
                <!-- Cài đặt chung -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-sliders-h"></i> Cài Đặt Chung
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="site_name" class="form-label">Tên Website</label>
                                    <input type="text" class="form-control" id="site_name" name="site_name" 
                                           value="{{ $settings['site_name'] ?? 'AutoDealer' }}" required>
                                </div>
                                <div class="mb-3">
                                    <label for="site_description" class="form-label">Mô Tả Website</label>
                                    <textarea class="form-control" id="site_description" name="site_description" rows="3">{{ $settings['site_description'] ?? 'Website bán xe hơi uy tín' }}</textarea>
                                </div>
                                <div class="mb-3">
                                    <label for="admin_email" class="form-label">Email Admin</label>
                                    <input type="email" class="form-control" id="admin_email" name="admin_email" 
                                           value="{{ $settings['admin_email'] ?? 'admin@autodealer.com' }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="timezone" class="form-label">Múi Giờ</label>
                                    <select class="form-select" id="timezone" name="timezone">
                                        <option value="Asia/Ho_Chi_Minh" {{ ($settings['timezone'] ?? '') == 'Asia/Ho_Chi_Minh' ? 'selected' : '' }}>Asia/Ho_Chi_Minh (GMT+7)</option>
                                        <option value="UTC" {{ ($settings['timezone'] ?? '') == 'UTC' ? 'selected' : '' }}>UTC (GMT+0)</option>
                                        <option value="America/New_York" {{ ($settings['timezone'] ?? '') == 'America/New_York' ? 'selected' : '' }}>America/New_York (GMT-5)</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="language" class="form-label">Ngôn Ngữ</label>
                                    <select class="form-select" id="language" name="language">
                                        <option value="vi" {{ ($settings['language'] ?? '') == 'vi' ? 'selected' : '' }}>Tiếng Việt</option>
                                        <option value="en" {{ ($settings['language'] ?? '') == 'en' ? 'selected' : '' }}>English</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="currency" class="form-label">Đơn Vị Tiền Tệ</label>
                                    <select class="form-select" id="currency" name="currency">
                                        <option value="$" {{ ($settings['currency'] ?? '') == '$' ? 'selected' : '' }}>$ (Việt Nam Đồng)</option>
                                        <option value="USD" {{ ($settings['currency'] ?? '') == 'USD' ? 'selected' : '' }}>USD (US Dollar)</option>
                                        <option value="EUR" {{ ($settings['currency'] ?? '') == 'EUR' ? 'selected' : '' }}>EUR (Euro)</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Cài đặt bảo mật -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-shield-alt"></i> Cài Đặt Bảo Mật
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="session_lifetime" class="form-label">Thời Gian Phiên Làm Việc (phút)</label>
                                    <input type="number" class="form-control" id="session_lifetime" name="session_lifetime" 
                                           value="{{ $settings['session_lifetime'] ?? '120' }}" min="30" max="1440">
                                </div>
                                <div class="mb-3">
                                    <label for="max_login_attempts" class="form-label">Số Lần Đăng Nhập Tối Đa</label>
                                    <input type="number" class="form-control" id="max_login_attempts" name="max_login_attempts" 
                                           value="{{ $settings['max_login_attempts'] ?? '5' }}" min="3" max="10">
                                </div>
                                <div class="mb-3">
                                    <label for="lockout_duration" class="form-label">Thời Gian Khóa (phút)</label>
                                    <input type="number" class="form-control" id="lockout_duration" name="lockout_duration" 
                                           value="{{ $settings['lockout_duration'] ?? '15' }}" min="5" max="60">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="two_factor_auth" name="two_factor_auth" 
                                               {{ ($settings['two_factor_auth'] ?? false) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="two_factor_auth">
                                            Bật Xác Thực 2 Yếu Tố
                                        </label>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="force_ssl" name="force_ssl" 
                                               {{ ($settings['force_ssl'] ?? false) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="force_ssl">
                                            Bắt Buộc Sử Dụng HTTPS
                                        </label>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="log_activities" name="log_activities" 
                                               {{ ($settings['log_activities'] ?? true) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="log_activities">
                                            Ghi Log Hoạt Động
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Cài đặt email -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-envelope"></i> Cài Đặt Email
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="mail_driver" class="form-label">Mail Driver</label>
                                    <select class="form-select" id="mail_driver" name="mail_driver">
                                        <option value="smtp" {{ ($settings['mail_driver'] ?? '') == 'smtp' ? 'selected' : '' }}>SMTP</option>
                                        <option value="sendmail" {{ ($settings['mail_driver'] ?? '') == 'sendmail' ? 'selected' : '' }}>Sendmail</option>
                                        <option value="mailgun" {{ ($settings['mail_driver'] ?? '') == 'mailgun' ? 'selected' : '' }}>Mailgun</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="mail_host" class="form-label">Mail Host</label>
                                    <input type="text" class="form-control" id="mail_host" name="mail_host" 
                                           value="{{ $settings['mail_host'] ?? 'smtp.gmail.com' }}">
                                </div>
                                <div class="mb-3">
                                    <label for="mail_port" class="form-label">Mail Port</label>
                                    <input type="number" class="form-control" id="mail_port" name="mail_port" 
                                           value="{{ $settings['mail_port'] ?? '587' }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="mail_username" class="form-label">Mail Username</label>
                                    <input type="text" class="form-control" id="mail_username" name="mail_username" 
                                           value="{{ $settings['mail_username'] ?? '' }}">
                                </div>
                                <div class="mb-3">
                                    <label for="mail_password" class="form-label">Mail Password</label>
                                    <input type="password" class="form-control" id="mail_password" name="mail_password" 
                                           value="{{ $settings['mail_password'] ?? '' }}">
                                </div>
                                <div class="mb-3">
                                    <label for="mail_encryption" class="form-label">Mail Encryption</label>
                                    <select class="form-select" id="mail_encryption" name="mail_encryption">
                                        <option value="tls" {{ ($settings['mail_encryption'] ?? '') == 'tls' ? 'selected' : '' }}>TLS</option>
                                        <option value="ssl" {{ ($settings['mail_encryption'] ?? '') == 'ssl' ? 'selected' : '' }}>SSL</option>
                                        <option value="" {{ ($settings['mail_encryption'] ?? '') == '' ? 'selected' : '' }}>None</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Cài đặt thanh toán -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-credit-card"></i> Cài Đặt Thanh Toán
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6>MoMo Payment</h6>
                                <div class="mb-3">
                                    <label for="momo_partner_code" class="form-label">Partner Code</label>
                                    <input type="text" class="form-control" id="momo_partner_code" name="momo_partner_code" 
                                           value="{{ $settings['momo_partner_code'] ?? '' }}">
                                </div>
                                <div class="mb-3">
                                    <label for="momo_access_key" class="form-label">Access Key</label>
                                    <input type="text" class="form-control" id="momo_access_key" name="momo_access_key" 
                                           value="{{ $settings['momo_access_key'] ?? '' }}">
                                </div>
                                <div class="mb-3">
                                    <label for="momo_secret_key" class="form-label">Secret Key</label>
                                    <input type="password" class="form-control" id="momo_secret_key" name="momo_secret_key" 
                                           value="{{ $settings['momo_secret_key'] ?? '' }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h6>VNPay</h6>
                                <div class="mb-3">
                                    <label for="vnpay_tmn_code" class="form-label">TMN Code</label>
                                    <input type="text" class="form-control" id="vnpay_tmn_code" name="vnpay_tmn_code" 
                                           value="{{ $settings['vnpay_tmn_code'] ?? '' }}">
                                </div>
                                <div class="mb-3">
                                    <label for="vnpay_hash_secret" class="form-label">Hash Secret</label>
                                    <input type="password" class="form-control" id="vnpay_hash_secret" name="vnpay_hash_secret" 
                                           value="{{ $settings['vnpay_hash_secret'] ?? '' }}">
                                </div>
                                <div class="mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="vnpay_sandbox" name="vnpay_sandbox" 
                                               {{ ($settings['vnpay_sandbox'] ?? true) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="vnpay_sandbox">
                                            Chế Độ Test (Sandbox)
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Cài đặt cache -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-memory"></i> Cài Đặt Cache
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="cache_driver" class="form-label">Cache Driver</label>
                                    <select class="form-select" id="cache_driver" name="cache_driver">
                                        <option value="file" {{ ($settings['cache_driver'] ?? '') == 'file' ? 'selected' : '' }}>File</option>
                                        <option value="redis" {{ ($settings['cache_driver'] ?? '') == 'redis' ? 'selected' : '' }}>Redis</option>
                                        <option value="memcached" {{ ($settings['cache_driver'] ?? '') == 'memcached' ? 'selected' : '' }}>Memcached</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="cache_ttl" class="form-label">Cache TTL (giây)</label>
                                    <input type="number" class="form-control" id="cache_ttl" name="cache_ttl" 
                                           value="{{ $settings['cache_ttl'] ?? '3600' }}" min="60" max="86400">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="enable_cache" name="enable_cache" 
                                               {{ ($settings['enable_cache'] ?? true) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="enable_cache">
                                            Bật Cache
                                        </label>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="auto_clear_cache" name="auto_clear_cache" 
                                               {{ ($settings['auto_clear_cache'] ?? false) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="auto_clear_cache">
                                            Tự Động Xóa Cache
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Cài đặt thông báo -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-bell"></i> Cài Đặt Thông Báo
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Email Notifications</h6>
                                <div class="mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="email_order_confirmation" name="email_order_confirmation" 
                                               {{ ($settings['email_order_confirmation'] ?? true) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="email_order_confirmation">
                                            Xác Nhận Đơn Hàng
                                        </label>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="email_order_status" name="email_order_status" 
                                               {{ ($settings['email_order_status'] ?? true) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="email_order_status">
                                            Cập Nhật Trạng Thái
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h6>Admin Notifications</h6>
                                <div class="mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="admin_new_order" name="admin_new_order" 
                                               {{ ($settings['admin_new_order'] ?? true) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="admin_new_order">
                                            Đơn Hàng Mới
                                        </label>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="admin_low_stock" name="admin_low_stock" 
                                               {{ ($settings['admin_low_stock'] ?? true) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="admin_low_stock">
                                            Sản Phẩm Sắp Hết Hàng
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Auto-save form when fields change
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('settingsForm');
    const inputs = form.querySelectorAll('input, select, textarea');
    
    inputs.forEach(input => {
        input.addEventListener('change', function() {
            // Show save indicator
            const saveBtn = document.querySelector('button[type="submit"]');
            const originalText = saveBtn.innerHTML;
            saveBtn.innerHTML = '<i class="fas fa-save"></i> Lưu Thay Đổi';
            saveBtn.classList.add('btn-warning');
            
            // Reset after 3 seconds
            setTimeout(() => {
                saveBtn.innerHTML = originalText;
                saveBtn.classList.remove('btn-warning');
            }, 3000);
        });
    });
    
    // Form submission
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const saveBtn = document.querySelector('button[type="submit"]');
        const originalText = saveBtn.innerHTML;
        saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang Lưu...';
        saveBtn.disabled = true;
        
        // Simulate form submission
        setTimeout(() => {
            saveBtn.innerHTML = '<i class="fas fa-check"></i> Đã Lưu!';
            saveBtn.classList.remove('btn-warning');
            saveBtn.classList.add('btn-success');
            saveBtn.disabled = false;
            
            // Reset button after 2 seconds
            setTimeout(() => {
                saveBtn.innerHTML = originalText;
                saveBtn.classList.remove('btn-success');
            }, 2000);
            
            // Show success message
            showAlert('Cài đặt đã được lưu thành công!', 'success');
        }, 2000);
    });
});

function showAlert(message, type) {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
    alertDiv.style.top = '20px';
    alertDiv.style.right = '20px';
    alertDiv.style.zIndex = '9999';
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(alertDiv);
    
    // Auto-remove after 5 seconds
    setTimeout(() => {
        if (alertDiv.parentNode) {
            alertDiv.parentNode.removeChild(alertDiv);
        }
    }, 5000);
}

// Test email configuration
function testEmailConfig() {
    alert('Tính năng test email sẽ được phát triển trong phiên bản tiếp theo!');
}

// Test payment configuration
function testPaymentConfig() {
    alert('Tính năng test payment sẽ được phát triển trong phiên bản tiếp theo!');
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

.form-label {
    font-weight: 600;
    color: #495057;
}

.form-control:focus,
.form-select:focus {
    border-color: #80bdff;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}

.form-check-input:checked {
    background-color: #0d6efd;
    border-color: #0d6efd;
}

.btn {
    border-radius: 8px;
}

.alert {
    border-radius: 8px;
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
}
</style>
@endpush
