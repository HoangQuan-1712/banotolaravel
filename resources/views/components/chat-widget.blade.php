{{-- Chat Widget Component --}}
@auth
<div id="chat-widget" class="position-fixed" style="bottom: 20px; right: 20px; z-index: 1000;">
    <!-- Chat Button -->
    <button id="chat-toggle-btn" class="btn btn-primary rounded-circle shadow-lg" 
            style="width: 60px; height: 60px; position: relative;">
        <i class="fas fa-comments fa-lg"></i>
        <span id="chat-notification-badge" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" 
              style="display: none;">
            <span id="chat-notification-count">0</span>
        </span>
    </button>

    <!-- Chat Window -->
    <div id="chat-window" class="card shadow-lg" 
         style="width: 350px; height: 500px; position: absolute; bottom: 70px; right: 0; display: none;">
        
        <!-- Chat Header -->
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <div>
                <i class="fas fa-headset"></i>
                <strong>Hỗ trợ trực tuyến</strong>
            </div>
            <div>
                <button id="chat-minimize-btn" class="btn btn-sm btn-outline-light me-1">
                    <i class="fas fa-minus"></i>
                </button>
                <button id="chat-close-btn" class="btn btn-sm btn-outline-light">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>

        <!-- Chat Messages -->
        <div id="widget-messages" class="card-body p-2" 
             style="height: 350px; overflow-y: auto; background: #f8f9fa;">
            <div class="text-center text-muted">
                <i class="fas fa-spinner fa-spin"></i>
                Đang kết nối...
            </div>
        </div>

        <!-- Chat Input -->
        <div class="card-footer p-2">
            <form id="widget-chat-form" class="d-flex gap-2">
                @csrf
                <input type="text" id="widget-msg-input" class="form-control form-control-sm" 
                       placeholder="Nhập tin nhắn..." autocomplete="off">
                <button type="submit" class="btn btn-primary btn-sm">
                    <i class="fas fa-paper-plane"></i>
                </button>
            </form>
            <div class="small text-muted mt-1">
                <span id="widget-admin-status">Đang kiểm tra trạng thái...</span>
            </div>
        </div>
    </div>
</div>

{{-- Chat Widget Styles --}}
<style>
#chat-widget {
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}

#chat-toggle-btn {
    transition: all 0.3s ease;
    border: none;
}

#chat-toggle-btn:hover {
    transform: scale(1.1);
    box-shadow: 0 8px 25px rgba(0,123,255,0.3) !important;
}

#chat-window {
    transition: all 0.3s ease;
    border: none;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 20px 45px rgba(0,0,0,0.18);
    backdrop-filter: blur(2px);
}

/* Header gradient + rounded top corners */
#chat-window .card-header {
    background: linear-gradient(135deg, #7b5cff 0%, #6a6dfd 40%, #5b8def 100%) !important;
    border: none;
    color: #fff;
    padding: 10px 12px;
}

#chat-window .card-header .btn {
    border: none;
    color: rgba(255,255,255,0.9);
}

#chat-window .card-header .btn:hover {
    background: rgba(255,255,255,0.12);
}

#widget-messages {
    background: #f5f7fb;
    scrollbar-width: thin;
    scrollbar-color: #dee2e6 transparent;
}

#widget-messages::-webkit-scrollbar {
    width: 6px;
}

#widget-messages::-webkit-scrollbar-track {
    background: transparent;
}

#widget-messages::-webkit-scrollbar-thumb {
    background-color: #dee2e6;
    border-radius: 3px;
}

.widget-message {
    margin-bottom: 10px;
    animation: slideIn 0.3s ease;
}

@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.widget-message-bubble {
    max-width: 80%;
    padding: 10px 14px;
    border-radius: 18px;
    font-size: 14px;
    line-height: 1.4;
    box-shadow: 0 8px 18px rgba(0,0,0,0.06);
}

.widget-message-user {
    background: linear-gradient(135deg, #7b5cff 0%, #5b8def 100%);
    color: #fff;
    margin-left: auto;
    border: none;
}

.widget-message-admin {
    background: #ffffff;
    color: #333;
    margin-right: auto;
    border: 1px solid #eef0f6;
}

.widget-message-time {
    font-size: 11px;
    opacity: 0.7;
    margin-top: 2px;
}

/* Footer/input area */
#chat-window .card-footer {
    background: #ffffff;
    border-top: 1px solid #eef0f6;
}

#widget-msg-input.form-control.form-control-sm {
    height: 44px;
    border-radius: 12px;
    background: #fbfcff;
    border: 1px solid #e6e8f0;
}

#chat-window .btn-primary.btn-sm {
    height: 44px;
    border-radius: 12px;
    padding: 0 14px;
    background: linear-gradient(135deg, #1ea7ff 0%, #1273ea 100%);
    border: none;
    box-shadow: 0 10px 20px rgba(18, 115, 234, 0.2);
}

/* Subtle entrance animation */
#chat-window[style*="display: block"] { animation: widgetIn .2s ease; }
@keyframes widgetIn { from { transform: translateY(8px); opacity: .9; } to { transform: translateY(0); opacity: 1; } }
</style>

{{-- Load required scripts --}}
<script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
<script src="{{ asset('js/echo-setup.js') }}"></script>
<script src="{{ asset('js/chat-realtime.js') }}"></script>

{{-- Chat Widget Script --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    const chatWidget = document.getElementById('chat-widget');
    const toggleBtn = document.getElementById('chat-toggle-btn');
    const chatWindow = document.getElementById('chat-window');
    const minimizeBtn = document.getElementById('chat-minimize-btn');
    const closeBtn = document.getElementById('chat-close-btn');
    const messagesContainer = document.getElementById('widget-messages');
    const chatForm = document.getElementById('widget-chat-form');
    const messageInput = document.getElementById('widget-msg-input');
    const adminStatus = document.getElementById('widget-admin-status');
    const notificationBadge = document.getElementById('chat-notification-badge');
    const notificationCount = document.getElementById('chat-notification-count');

    let currentChat = null;
    let widgetPolling = null;
    let isWindowOpen = false;
    let unreadCount = 0;

    // Toggle chat window
    toggleBtn.addEventListener('click', function() {
        if (isWindowOpen) {
            closeChatWindow();
        } else {
            openChatWindow();
        }
    });

    // Minimize chat
    minimizeBtn.addEventListener('click', function() {
        closeChatWindow();
    });

    // Close chat
    closeBtn.addEventListener('click', function() {
        closeChatWindow();
    });

    function openChatWindow() {
        chatWindow.style.display = 'block';
        isWindowOpen = true;
        toggleBtn.innerHTML = '<i class="fas fa-times fa-lg"></i>';
        
        // Clear unread count
        unreadCount = 0;
        updateNotificationBadge();
        
        // Initialize chat if not already done
        if (!currentChat) {
            initializeChat();
        }
        
        // Focus input
        messageInput.focus();
    }

    function closeChatWindow() {
        chatWindow.style.display = 'none';
        isWindowOpen = false;
        toggleBtn.innerHTML = '<i class="fas fa-comments fa-lg"></i>';
        
        // Stop polling when closed
        if (widgetPolling) {
            widgetPolling.disconnect();
        }
    }

    async function initializeChat() {
        try {
            console.log('[WIDGET] Initializing chat...');
            
            // Create or get existing chat
            const response = await fetch('/chat', {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                },
                credentials: 'same-origin'
            });

            console.log('[WIDGET] Chat response status:', response.status);

            if (!response.ok) {
                throw new Error(`Failed to initialize chat: ${response.status}`);
            }

            const data = await response.json();
            console.log('[WIDGET] Chat data:', data);
            
            if (!data.success || !data.chat_id) {
                throw new Error('Invalid chat response format');
            }
            
            currentChat = data.chat_id;

            // Initialize hybrid chat system
            if (window.ChatRealtime) {
                widgetPolling = new window.ChatRealtime(currentChat, messagesContainer, {
                    pollingInterval: 4000, // Poll every 4 seconds for widget
                    onNewMessage: (message, shouldScroll = true) => {
                        appendWidgetMessage(message);
                        
                        // Show notification if window is closed
                        if (!isWindowOpen && message.sender.is_admin) {
                            unreadCount++;
                            updateNotificationBadge();
                            showDesktopNotification('Tin nhắn mới từ Admin', message.body);
                        }
                    },
                    onError: (error) => {
                        console.error('Widget chat error:', error);
                        showWidgetError('Lỗi kết nối. Đang thử kết nối lại...');
                    },
                    onConnectionChange: (connected, type) => {
                        console.log(`[WIDGET] Connection: ${connected ? 'connected' : 'disconnected'} via ${type}`);
                    }
                });

                // Load initial messages
                messagesContainer.innerHTML = '<div class="text-center text-muted small">Đang tải tin nhắn...</div>';
                await widgetPolling.loadInitialMessages();
            }

            updateAdminStatus();

        } catch (error) {
            console.error('Error initializing chat:', error);
            showWidgetError('Không thể kết nối đến hệ thống chat');
        }
    }

    function appendWidgetMessage(message) {
        const isAdmin = message.sender.is_admin;
        const messageDiv = document.createElement('div');
        messageDiv.className = `widget-message d-flex ${isAdmin ? 'justify-content-start' : 'justify-content-end'}`;
        
        const bubbleClass = isAdmin ? 'widget-message-admin' : 'widget-message-user';
        const senderName = isAdmin ? 'Admin' : 'Bạn';
        const messageTime = new Date(message.created_at).toLocaleTimeString('vi-VN', {
            hour: '2-digit',
            minute: '2-digit'
        });

        messageDiv.innerHTML = `
            <div class="widget-message-bubble ${bubbleClass}">
                <div>${message.body ? message.body.replace(/</g,'&lt;').replace(/\n/g, '<br>') : ''}</div>
                <div class="widget-message-time text-center">${senderName} • ${messageTime}</div>
            </div>
        `;

        messagesContainer.appendChild(messageDiv);
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }

    function updateNotificationBadge() {
        if (unreadCount > 0) {
            notificationCount.textContent = unreadCount > 99 ? '99+' : unreadCount;
            notificationBadge.style.display = 'block';
        } else {
            notificationBadge.style.display = 'none';
        }
    }

    function updateAdminStatus() {
        const now = new Date();
        const hour = now.getHours();
        const isBusinessHours = hour >= 8 && hour <= 22;
        
        adminStatus.innerHTML = isBusinessHours ? 
            '<i class="fas fa-circle text-success"></i> Admin đang online' :
            '<i class="fas fa-circle text-secondary"></i> Ngoài giờ làm việc';
    }

    function showWidgetError(message) {
        messagesContainer.innerHTML = `
            <div class="text-center text-danger small">
                <i class="fas fa-exclamation-triangle"></i>
                ${message}
            </div>
        `;
    }

    function showDesktopNotification(title, body) {
        if ('Notification' in window && Notification.permission === 'granted') {
            new Notification(title, {
                body: body.substring(0, 100),
                icon: '/favicon.ico',
                tag: 'chat-message'
            });
        }
    }

    // Handle form submission
    chatForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const text = messageInput.value.trim();
        if (!text || !currentChat) return;

        const submitBtn = this.querySelector('button[type="submit"]');
        const originalHtml = submitBtn.innerHTML;

        try {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

            const formData = new FormData();
            formData.append('body', text);

            if (widgetPolling) {
                await widgetPolling.sendMessage(formData);
                messageInput.value = '';
            }

        } catch (error) {
            console.error('Error sending message:', error);
            showWidgetError('Lỗi gửi tin nhắn');
        } finally {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalHtml;
        }
    });

    // Update admin status periodically
    setInterval(updateAdminStatus, 60000);

    // Request notification permission
    if ('Notification' in window && Notification.permission === 'default') {
        Notification.requestPermission();
    }

    // Handle page visibility for polling optimization
    document.addEventListener('visibilitychange', function() {
        if (widgetPolling) {
            if (document.hidden) {
                widgetPolling.pollingInterval = 8000; // Slower when hidden
            } else {
                widgetPolling.pollingInterval = 4000; // Normal speed when visible
                if (isWindowOpen) {
                    unreadCount = 0;
                    updateNotificationBadge();
                }
            }
        }
    });
});
</script>
@endauth
