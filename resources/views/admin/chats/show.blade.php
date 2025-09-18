@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4>Chat #{{ $chat->id }} - {{ $chat->user->name ?? 'Khách' }}</h4>
            <small class="text-muted">
                @if($chat->assignedAdmin)
                    Admin xử lý: {{ $chat->assignedAdmin->name }}
                @else
                    Chưa có admin xử lý
                @endif
            </small>
        </div>
        <div>
            <a href="{{ route('admin.chats.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Quay lại
            </a>
            <form action="{{ route('admin.chats.close', $chat) }}" method="POST" class="d-inline">
                @csrf
                <button class="btn btn-outline-danger" onclick="return confirm('Đóng chat này?')">
                    <i class="fas fa-times"></i> Đóng chat
                </button>
            </form>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <!-- Chat Box -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <i class="fas fa-comments"></i>
                        Cuộc trò chuyện
                    </div>
                    <div>
                        <span id="user-online-status" class="badge bg-secondary">Đang kiểm tra...</span>
                    </div>
                </div>

                <div id="admin-chat-messages" class="card-body" style="height: 450px; overflow-y: auto; background: #f8f9fa;">
                    <div class="text-center text-muted">
                        <i class="fas fa-spinner fa-spin"></i>
                        Đang tải tin nhắn...
                    </div>
                </div>

                <div class="card-footer">
                    <div id="admin-typing-indicator" class="text-muted small mb-2" style="display:none;">
                        <i class="fas fa-ellipsis-h"></i>
                        {{ $chat->user->name ?? 'Khách' }} đang nhập...
                    </div>
                    
                    <form id="admin-chat-form" enctype="multipart/form-data">
                        @csrf
                        <div class="row g-2">
                            <div class="col">
                                <input type="text" id="admin-msg-input" class="form-control" 
                                       placeholder="Nhập tin nhắn..." autocomplete="off">
                            </div>
                            <div class="col-auto">
                                <input type="file" id="admin-file-input" name="files[]" multiple 
                                       class="form-control" style="max-width: 200px;" 
                                       accept="image/*,.pdf,.doc,.docx">
                            </div>
                            <div class="col-auto">
                                <button class="btn btn-primary" type="submit">
                                    <i class="fas fa-paper-plane"></i> Gửi
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <!-- Chat Info -->
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-info-circle"></i>
                    Thông tin chat
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <strong>Khách hàng:</strong><br>
                        {{ $chat->user->name ?? 'Khách' }}<br>
                        <small class="text-muted">ID: {{ $chat->user_id }}</small>
                    </div>
                    
                    <div class="mb-3">
                        <strong>Trạng thái:</strong><br>
                        <span class="badge bg-{{ $chat->status === 'open' ? 'success' : 'secondary' }}">
                            {{ $chat->status }}
                        </span>
                    </div>
                    
                    <div class="mb-3">
                        <strong>Tạo lúc:</strong><br>
                        {{ $chat->created_at->format('d/m/Y H:i') }}
                    </div>
                    
                    <div class="mb-3">
                        <strong>Cập nhật cuối:</strong><br>
                        {{ $chat->last_message_at?->format('d/m/Y H:i') ?? 'Chưa có tin nhắn' }}
                    </div>

                    @if($chat->assignedAdmin && $chat->assignedAdmin->id !== auth()->id())
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i>
                            Chat này đang được xử lý bởi {{ $chat->assignedAdmin->name }}
                        </div>
                    @endif
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card mt-3">
                <div class="card-header">
                    <i class="fas fa-bolt"></i>
                    Tin nhắn nhanh
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button class="btn btn-outline-primary btn-sm quick-message" 
                                data-message="Xin chào! Tôi có thể giúp gì cho bạn?">
                            Chào hỏi
                        </button>
                        <button class="btn btn-outline-primary btn-sm quick-message" 
                                data-message="Cảm ơn bạn đã liên hệ. Chúng tôi sẽ hỗ trợ bạn ngay.">
                            Cảm ơn
                        </button>
                        <button class="btn btn-outline-primary btn-sm quick-message" 
                                data-message="Bạn có thể cung cấp thêm thông tin không?">
                            Yêu cầu thông tin
                        </button>
                        <button class="btn btn-outline-success btn-sm quick-message" 
                                data-message="Vấn đề đã được giải quyết. Cảm ơn bạn!">
                            Hoàn thành
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Scripts --}}
<script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
<script src="{{ asset('js/echo-setup.js') }}"></script>
<script src="{{ asset('js/chat-realtime.js') }}"></script>
<script>
const chatId = {{ $chat->id }};
const adminId = {{ auth()->id() }};
const messagesEl = document.getElementById('admin-chat-messages');
const inputEl = document.getElementById('admin-msg-input');
const fileEl = document.getElementById('admin-file-input');
const typingEl = document.getElementById('admin-typing-indicator');
const userOnlineStatus = document.getElementById('user-online-status');

// Custom message formatter for admin view
function formatAdminMessage(message, shouldScroll = true) {
    const isAdmin = message.sender.is_admin;
    const side = isAdmin ? 'end' : 'start';
    const bgColor = isAdmin ? 'primary' : 'light';
    const textColor = isAdmin ? 'white' : 'dark';
    
    const wrapper = document.createElement('div');
    wrapper.className = `mb-3 d-flex justify-content-${side}`;
    wrapper.setAttribute('data-message-id', message.id);
    
    let files = '';
    if (message.attachments && message.attachments.length) {
        files = '<div class="mt-2 d-flex flex-wrap gap-1">' +
            message.attachments.map(a => `<a target="_blank" href="${a.url}" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-paperclip"></i> File
            </a>`).join('') + '</div>';
    }
    
    const messageTime = new Date(message.created_at).toLocaleString('vi-VN', {
        hour: '2-digit',
        minute: '2-digit',
        day: '2-digit',
        month: '2-digit'
    });
    
    wrapper.innerHTML = `
        <div class="border rounded p-3 bg-${bgColor} text-${textColor}" style="max-width:75%">
            <div class="small opacity-75 mb-1">
                ${isAdmin ? 'Admin' : (message.sender.name || 'Khách')} • ${messageTime}
            </div>
            <div>${message.body ? message.body.replace(/</g,'&lt;').replace(/\n/g, '<br>') : ''}</div>
            ${files}
        </div>
    `;
    
    return wrapper;
}

// Initialize admin hybrid chat system
const adminChatSystem = new ChatRealtime(chatId, messagesEl, {
    pollingInterval: 2000, // Faster polling for admin
    onNewMessage: (message, shouldScroll = true) => {
        const messageElement = formatAdminMessage(message, shouldScroll);
        messagesEl.appendChild(messageElement);
        
        if (shouldScroll) {
            // Force scroll to bottom with a small delay
            setTimeout(() => {
                messagesEl.scrollTop = messagesEl.scrollHeight;
            }, 50);
        }
        
        // Show notification for user messages
        if (!message.sender.is_admin) {
            showAdminNotification('Tin nhắn mới từ khách hàng', message.body);
            // Update page title to show new message
            updatePageTitle(true);
        }
    },
    onError: (error) => {
        console.error('Admin chat system error:', error);
        showErrorToast('Lỗi kết nối chat. Đang thử kết nối lại...');
    },
    onConnectionChange: (connected, type) => {
        console.log(`[ADMIN CHAT] Connection: ${connected ? 'connected' : 'disconnected'} via ${type}`);
    },
    onTyping: (event) => {
        if (!event.is_admin) {
            typingEl.style.display = event.typing ? 'block' : 'none';
        }
    }
});

// Load initial messages
messagesEl.innerHTML = '<div class="text-center text-muted"><i class="fas fa-spinner fa-spin"></i> Đang tải tin nhắn...</div>';
adminChatSystem.loadInitialMessages();

// Update user online status (simplified)
function updateUserOnlineStatus() {
    // Simple logic - you can enhance this with actual user activity tracking
    userOnlineStatus.className = 'badge bg-success';
    userOnlineStatus.textContent = 'Đang hoạt động';
}

updateUserOnlineStatus();
setInterval(updateUserOnlineStatus, 30000); // Update every 30 seconds

// Send message
document.getElementById('admin-chat-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const text = inputEl.value.trim();
    const files = fileEl.files;
    
    if (!text && files.length === 0) {
        showErrorToast('Vui lòng nhập tin nhắn hoặc chọn file');
        return;
    }
    
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalHtml = submitBtn.innerHTML;
    
    try {
        // Disable submit button
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang gửi...';
        
        // Prepare form data
        const formData = new FormData();
        if (text) formData.append('body', text);
        
        for (const file of files) {
            formData.append('files[]', file);
        }
        
        // Send message using hybrid chat system
        await adminChatSystem.sendMessage(formData);
        
        // Clear form
        inputEl.value = '';
        fileEl.value = '';
        
        showSuccessToast('Tin nhắn đã được gửi');
        
    } catch (error) {
        console.error('Error sending message:', error);
        showErrorToast('Lỗi gửi tin nhắn. Vui lòng thử lại.');
    } finally {
        // Re-enable submit button
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalHtml;
    }
});

// Quick messages
document.querySelectorAll('.quick-message').forEach(btn => {
    btn.addEventListener('click', function() {
        inputEl.value = this.dataset.message;
        inputEl.focus();
    });
});

// Utility functions for admin notifications
function showAdminNotification(title, body) {
    if ('Notification' in window && Notification.permission === 'granted') {
        new Notification(title, { 
            body: body.substring(0, 100),
            icon: '/favicon.ico'
        });
    }
}

function showSuccessToast(message) {
    showToast(message, 'success');
}

function showErrorToast(message) {
    showToast(message, 'error');
}

function showToast(message, type = 'info') {
    // Create toast element
    const toast = document.createElement('div');
    toast.className = `alert alert-${type === 'error' ? 'danger' : type === 'success' ? 'success' : 'info'} position-fixed`;
    toast.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    toast.innerHTML = `
        <div class="d-flex justify-content-between align-items-center">
            <span><i class="fas fa-${type === 'error' ? 'exclamation-triangle' : type === 'success' ? 'check-circle' : 'info-circle'}"></i> ${message}</span>
            <button type="button" class="btn-close" onclick="this.parentElement.parentElement.remove()"></button>
        </div>
    `;
    
    document.body.appendChild(toast);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        if (toast.parentElement) {
            toast.remove();
        }
    }, 5000);
}

// Page title notification
let originalTitle = document.title;
let hasNewMessages = false;

function updatePageTitle(newMessage = false) {
    if (newMessage) {
        hasNewMessages = true;
        document.title = '(!) ' + originalTitle;
    } else {
        hasNewMessages = false;
        document.title = originalTitle;
    }
}

// Reset title when page becomes visible
document.addEventListener('visibilitychange', function() {
    if (!document.hidden && hasNewMessages) {
        updatePageTitle(false);
    }
});

// Request notification permission
if ('Notification' in window && Notification.permission === 'default') {
    Notification.requestPermission();
}

// Handle page visibility change to adjust polling frequency
document.addEventListener('visibilitychange', function() {
    if (document.hidden) {
        // Page is hidden, reduce polling frequency
        if (adminChatSystem.options) {
            adminChatSystem.options.pollingInterval = 5000; // 5 seconds
        }
    } else {
        // Page is visible, normal polling frequency
        if (adminChatSystem.options) {
            adminChatSystem.options.pollingInterval = 2000; // 2 seconds
        }
        // Reset new message indicator
        updatePageTitle(false);
    }
});

// Clean up when page unloads
window.addEventListener('beforeunload', function() {
    adminChatSystem.disconnect();
});

// Auto-focus on message input
inputEl.focus();
</script>
@endsection
