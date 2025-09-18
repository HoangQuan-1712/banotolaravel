@extends('layouts.app')

@section('content')
<!-- Load Pusher and Echo for testing -->
<script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
<script src="{{ asset('js/echo-setup.js') }}"></script>
<script src="{{ asset('js/chat-realtime.js') }}"></script>
<div class="container py-4">
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-flask"></i>
                        Test Chat System
                    </h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <strong>Hướng dẫn test:</strong>
                        <ol class="mb-0 mt-2">
                            <li>Mở 2 tab browser khác nhau (một tab user, một tab admin)</li>
                            <li>Đăng nhập user ở tab 1, đăng nhập admin ở tab 2</li>
                            <li>User gửi tin nhắn từ widget chat hoặc trang chat chính</li>
                            <li>Admin vào <code>/admin/chats</code> để xem và trả lời</li>
                            <li>Kiểm tra tin nhắn có tự động cập nhật không cần refresh</li>
                        </ol>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="card border-primary">
                                <div class="card-header bg-primary text-white">
                                    <i class="fas fa-user"></i>
                                    User Test
                                </div>
                                <div class="card-body">
                                    <p><strong>Các tính năng cần test:</strong></p>
                                    <ul>
                                        <li>Chat widget ở góc phải màn hình</li>
                                        <li>Gửi tin nhắn từ widget</li>
                                        <li>Nhận tin nhắn real-time từ admin</li>
                                        <li>Notification khi có tin nhắn mới</li>
                                        <li>Trạng thái admin online/offline</li>
                                    </ul>
                                    
                                    <div class="d-grid gap-2">
                                        <a href="{{ route('chat.open') }}" class="btn btn-primary">
                                            <i class="fas fa-comments"></i>
                                            Mở Chat Chính
                                        </a>
                                        <button class="btn btn-outline-primary" onclick="testChatWidget()">
                                            <i class="fas fa-robot"></i>
                                            Test Chat Widget
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card border-success">
                                <div class="card-header bg-success text-white">
                                    <i class="fas fa-user-shield"></i>
                                    Admin Test
                                </div>
                                <div class="card-body">
                                    <p><strong>Các tính năng cần test:</strong></p>
                                    <ul>
                                        <li>Xem danh sách chat đang chờ</li>
                                        <li>Trả lời tin nhắn real-time</li>
                                        <li>Tin nhắn nhanh (Quick messages)</li>
                                        <li>Upload file đính kèm</li>
                                        <li>Notification khi có chat mới</li>
                                    </ul>
                                    
                                    <div class="d-grid gap-2">
                                        @if(auth()->user()->is_admin)
                                            <a href="{{ route('admin.chats.index') }}" class="btn btn-success">
                                                <i class="fas fa-headset"></i>
                                                Admin Chat Console
                                            </a>
                                        @else
                                            <div class="alert alert-warning small mb-2">
                                                Cần đăng nhập với tài khoản admin
                                            </div>
                                        @endif
                                        
                                        <button class="btn btn-outline-success" onclick="testAdminFeatures()">
                                            <i class="fas fa-cogs"></i>
                                            Test Admin Features
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4">
                        <div class="card border-warning">
                            <div class="card-header bg-warning text-dark">
                                <i class="fas fa-chart-line"></i>
                                System Status
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-2">
                                        <div class="text-center">
                                            <div class="h4" id="pusher-status">
                                                <i class="fas fa-question-circle text-warning"></i>
                                            </div>
                                            <small>Pusher</small>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="text-center">
                                            <div class="h4" id="echo-status">
                                                <i class="fas fa-question-circle text-warning"></i>
                                            </div>
                                            <small>Laravel Echo</small>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="text-center">
                                            <div class="h4 text-success" id="polling-status">
                                                <i class="fas fa-check-circle"></i>
                                            </div>
                                            <small>Polling Fallback</small>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="text-center">
                                            <div class="h4 text-success" id="api-status">
                                                <i class="fas fa-check-circle"></i>
                                            </div>
                                            <small>API Endpoints</small>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="text-center">
                                            <div class="h4" id="notification-status">
                                                <i class="fas fa-question-circle text-warning"></i>
                                            </div>
                                            <small>Notifications</small>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="text-center">
                                            <div class="h4 text-info" id="widget-status">
                                                <i class="fas fa-check-circle"></i>
                                            </div>
                                            <small>Chat Widget</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4">
                        <div class="card">
                            <div class="card-header">
                                <i class="fas fa-terminal"></i>
                                Test Console
                            </div>
                            <div class="card-body">
                                <div id="test-console" class="bg-dark text-light p-3 rounded" style="height: 200px; overflow-y: auto; font-family: monospace; font-size: 12px;">
                                    <div class="text-success">[INFO] Chat test page loaded</div>
                                    <div class="text-info">[INFO] Polling system initialized</div>
                                </div>
                                <div class="mt-2">
                                    <button class="btn btn-sm btn-outline-primary" onclick="clearConsole()">
                                        <i class="fas fa-trash"></i> Clear
                                    </button>
                                    <button class="btn btn-sm btn-outline-success" onclick="runSystemTest()">
                                        <i class="fas fa-play"></i> Run System Test
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function testChatWidget() {
    const widget = document.getElementById('chat-widget');
    if (widget) {
        const toggleBtn = document.getElementById('chat-toggle-btn');
        if (toggleBtn) {
            toggleBtn.click();
            logToConsole('[TEST] Chat widget opened', 'success');
        } else {
            logToConsole('[ERROR] Chat widget button not found', 'error');
        }
    } else {
        logToConsole('[ERROR] Chat widget not found', 'error');
    }
}

function testAdminFeatures() {
    logToConsole('[TEST] Testing admin features...', 'info');
    
    // Test API endpoints
    fetch('/admin/chats', {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    }).then(response => {
        if (response.ok) {
            logToConsole('[TEST] Admin chat endpoint accessible', 'success');
        } else {
            logToConsole('[TEST] Admin chat endpoint failed: ' + response.status, 'error');
        }
    }).catch(error => {
        logToConsole('[TEST] Admin chat endpoint error: ' + error.message, 'error');
    });
}

function runSystemTest() {
    logToConsole('[TEST] Running system test...', 'info');
    
    // Test Pusher
    if (window.Pusher) {
        document.getElementById('pusher-status').innerHTML = '<i class="fas fa-check-circle text-success"></i>';
        logToConsole('[TEST] Pusher library loaded', 'success');
    } else {
        document.getElementById('pusher-status').innerHTML = '<i class="fas fa-times-circle text-danger"></i>';
        logToConsole('[ERROR] Pusher library not found', 'error');
    }
    
    // Test Laravel Echo
    if (window.Echo) {
        document.getElementById('echo-status').innerHTML = '<i class="fas fa-check-circle text-success"></i>';
        logToConsole('[TEST] Laravel Echo initialized', 'success');
        
        // Test Echo connection
        if (window.Echo.connector && window.Echo.connector.pusher) {
            const connectionState = window.Echo.connector.pusher.connection.state;
            logToConsole(`[TEST] Pusher connection state: ${connectionState}`, connectionState === 'connected' ? 'success' : 'warning');
        }
    } else {
        document.getElementById('echo-status').innerHTML = '<i class="fas fa-times-circle text-danger"></i>';
        logToConsole('[ERROR] Laravel Echo not found', 'error');
    }
    
    // Test ChatRealtime class
    if (window.ChatRealtime) {
        logToConsole('[TEST] ChatRealtime class available', 'success');
    } else {
        logToConsole('[ERROR] ChatRealtime class not found', 'error');
    }
    
    // Test notification permission
    if ('Notification' in window) {
        if (Notification.permission === 'granted') {
            document.getElementById('notification-status').innerHTML = '<i class="fas fa-check-circle text-success"></i>';
            logToConsole('[TEST] Notifications enabled', 'success');
        } else if (Notification.permission === 'denied') {
            document.getElementById('notification-status').innerHTML = '<i class="fas fa-times-circle text-danger"></i>';
            logToConsole('[TEST] Notifications denied', 'error');
        } else {
            document.getElementById('notification-status').innerHTML = '<i class="fas fa-question-circle text-warning"></i>';
            logToConsole('[TEST] Notifications permission not requested', 'warning');
        }
    }
    
    // Test CSRF token
    const csrfToken = document.querySelector('meta[name="csrf-token"]');
    if (csrfToken) {
        logToConsole('[TEST] CSRF token found', 'success');
    } else {
        logToConsole('[ERROR] CSRF token not found', 'error');
    }
    
    // Test Pusher config
    const pusherKey = document.querySelector('meta[name="pusher-key"]');
    const pusherCluster = document.querySelector('meta[name="pusher-cluster"]');
    if (pusherKey && pusherCluster) {
        logToConsole(`[TEST] Pusher config found - Key: ${pusherKey.content}, Cluster: ${pusherCluster.content}`, 'success');
    } else {
        logToConsole('[ERROR] Pusher config meta tags not found', 'error');
    }
    
    logToConsole('[TEST] System test completed', 'info');
}

function logToConsole(message, type = 'info') {
    const console = document.getElementById('test-console');
    const timestamp = new Date().toLocaleTimeString();
    const colorClass = {
        'info': 'text-info',
        'success': 'text-success',
        'warning': 'text-warning',
        'error': 'text-danger'
    }[type] || 'text-light';
    
    const logEntry = document.createElement('div');
    logEntry.className = colorClass;
    logEntry.textContent = `[${timestamp}] ${message}`;
    
    console.appendChild(logEntry);
    console.scrollTop = console.scrollHeight;
}

function clearConsole() {
    document.getElementById('test-console').innerHTML = '';
    logToConsole('Console cleared', 'info');
}

// Auto-run basic tests on page load
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(runSystemTest, 1000);
});
</script>
@endsection
