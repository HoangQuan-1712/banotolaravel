@extends('layouts.app')

@section('content')
<div class="container py-4 chat-page">
  <h5 class="mb-3">Hỗ trợ trực tuyến</h5>

  <div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
      <div>Chat #{{ $chat->id }} @if($chat->assignedAdmin) • Admin: {{ $chat->assignedAdmin->name }} @endif</div>
      <div>
        <span id="connection-status" class="badge bg-warning me-2">Đang kết nối...</span>
        <span id="admin-online-badge" class="badge bg-secondary">Admin: offline</span>
      </div>
    </div>

    <div id="messages" class="card-body" style="height: 420px; overflow-y: auto;">
      <div class="text-muted">Đang tải tin nhắn...</div>
    </div>

    <div class="card-footer">
      <form id="chat-form" enctype="multipart/form-data">
        @csrf
        <div class="mb-2 d-flex gap-2">
          <input type="text" id="msg-input" class="form-control" placeholder="Nhập tin nhắn..." autocomplete="off">
          <input type="file" id="file-input" name="files[]" multiple class="form-control" style="max-width: 260px;">
          <button class="btn btn-primary" type="submit">Gửi</button>
        </div>
        <div id="typing-indicator" class="text-muted small" style="display:none">Admin đang nhập...</div>
      </form>
    </div>
  </div>
</div>

{{-- Scripts --}}
<script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
<script src="{{ asset('js/echo-setup.js') }}"></script>
<script src="{{ asset('js/chat-realtime.js') }}"></script>
<script>
  const chatId = {{ $chat->id }};
  const messagesEl = document.getElementById('messages');
  const inputEl = document.getElementById('msg-input');
  const fileEl = document.getElementById('file-input');
  const typingEl = document.getElementById('typing-indicator');
  const adminOnlineBadge = document.getElementById('admin-online-badge');

  // Initialize hybrid chat system (Pusher + Polling fallback)
  console.log('[CHAT] Initializing hybrid chat system for chat ID:', chatId);
  const chatSystem = new ChatRealtime(chatId, messagesEl, {
    onNewMessage: (message, shouldScroll = true) => {
      console.log('[CHAT] New message received:', message);
      const messageElement = formatMessage(message, shouldScroll);
      messagesEl.appendChild(messageElement);
      
      if (shouldScroll) {
        // Force scroll to bottom with a small delay
        setTimeout(() => {
          messagesEl.scrollTop = messagesEl.scrollHeight;
        }, 50);
      }
      
      // Show notification for admin messages (if user is not admin)
      @if(!auth()->user()->is_admin)
      if (message.sender.is_admin) {
        showNotification('Tin nhắn mới từ Admin', message.body);
      }
      @endif
    },
    onError: (error) => {
      console.error('Chat system error:', error);
      showErrorMessage('Lỗi kết nối chat. Đang thử kết nối lại...');
    },
    onConnectionChange: (connected, type) => {
      const statusBadge = document.getElementById('connection-status');
      if (statusBadge) {
        statusBadge.className = `badge bg-${connected ? 'success' : 'danger'}`;
        statusBadge.textContent = connected ? `Kết nối (${type})` : 'Mất kết nối';
      }
      console.log(`[CHAT] Connection status: ${connected ? 'connected' : 'disconnected'} via ${type}`);
    },
    onTyping: (event) => {
      if (event.is_admin && !{{ auth()->user()->is_admin ? 'true' : 'false' }}) {
        typingEl.style.display = event.typing ? 'block' : 'none';
      }
    }
  });

  // Load initial messages
  messagesEl.innerHTML = '<div class="text-muted text-center">Đang tải tin nhắn...</div>';
  chatSystem.loadInitialMessages();

  // Infinite scroll: load older messages when scrolling to top (no page reload)
  messagesEl.addEventListener('scroll', function() {
    if (messagesEl.scrollTop <= 0) {
      chatSystem.loadOlderMessages();
    }
  });

  // Debug: Log connection status
  setInterval(() => {
    if (window.Echo && window.Echo.connector && window.Echo.connector.pusher) {
      const state = window.Echo.connector.pusher.connection.state;
      console.log('[DEBUG] Pusher connection state:', state);
    }
  }, 10000); // Every 10 seconds

  // Simple admin online status (you can enhance this with a separate endpoint)
  function updateAdminStatus() {
    // For now, just show as online during business hours (8 AM - 10 PM)
    const now = new Date();
    const hour = now.getHours();
    const isBusinessHours = hour >= 8 && hour <= 22;
    
    adminOnlineBadge.className = 'badge bg-' + (isBusinessHours ? 'success' : 'secondary');
    adminOnlineBadge.textContent = 'Admin: ' + (isBusinessHours ? 'online' : 'offline');
  }
  
  updateAdminStatus();
  setInterval(updateAdminStatus, 60000); // Update every minute

  // Typing indicator
  let typingTimer;
  inputEl.addEventListener('input', () => {
    // Send typing start
    chatSystem.sendTyping(true);
    
    clearTimeout(typingTimer);
    typingTimer = setTimeout(() => {
      // Send typing stop
      chatSystem.sendTyping(false);
    }, 1200);
  });

  // Send message
  document.getElementById('chat-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const text = inputEl.value.trim();
    const files = fileEl.files;
    
    if (!text && files.length === 0) {
      showErrorMessage('Vui lòng nhập tin nhắn hoặc chọn file');
      return;
    }
    
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.textContent;
    
    try {
      // Disable submit button
      submitBtn.disabled = true;
      submitBtn.textContent = 'Đang gửi...';
      
      // Prepare form data
      const formData = new FormData();
      if (text) formData.append('body', text);
      
      for (const file of files) {
        formData.append('files[]', file);
      }
      
      // Send message using hybrid chat system
      await chatSystem.sendMessage(formData);
      
      // Clear form
      inputEl.value = '';
      fileEl.value = '';
      
      showSuccessMessage('Tin nhắn đã được gửi');
      
    } catch (error) {
      console.error('Error sending message:', error);
      showErrorMessage('Lỗi gửi tin nhắn. Vui lòng thử lại.');
    } finally {
      // Re-enable submit button
      submitBtn.disabled = false;
      submitBtn.textContent = originalText;
    }
  });

  // Utility functions for notifications
  function showNotification(title, body) {
    if ('Notification' in window && Notification.permission === 'granted') {
      new Notification(title, { body: body.substring(0, 100) });
    }
  }

  function showSuccessMessage(message) {
    showToast(message, 'success');
  }

  function showErrorMessage(message) {
    showToast(message, 'error');
  }

  function showToast(message, type = 'info') {
    // Create toast element
    const toast = document.createElement('div');
    toast.className = `alert alert-${type === 'error' ? 'danger' : type === 'success' ? 'success' : 'info'} position-fixed`;
    toast.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    toast.innerHTML = `
      <div class="d-flex justify-content-between align-items-center">
        <span>${message}</span>
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

  // Format message for display
  function formatMessage(message, shouldScroll = true) {
    const messageDiv = document.createElement('div');
    messageDiv.className = 'message mb-3';
    
    const isCurrentUser = {{ auth()->id() }} === message.sender.id;
    const alignClass = isCurrentUser ? 'text-end' : 'text-start';
    
    let attachmentsHtml = '';
    if (message.attachments && message.attachments.length > 0) {
      attachmentsHtml = message.attachments.map(attachment => {
        if (attachment.mime && attachment.mime.startsWith('image/')) {
          return `<div class="mt-2"><img src="${attachment.url}" class="img-thumbnail" style="max-width: 200px;"></div>`;
        } else {
          return `<div class="mt-2"><a href="${attachment.url}" target="_blank" class="btn btn-sm btn-outline-primary"><i class="fas fa-download"></i> ${attachment.path}</a></div>`;
        }
      }).join('');
    }
    
    const ts = message.created_at_iso || message.created_at;
    const timeStr = new Date(ts).toLocaleTimeString('vi-VN', {
      hour: '2-digit',
      minute: '2-digit'
    });
    
    messageDiv.innerHTML = `
      <div class="${alignClass}">
        <div class="d-inline-block p-2 rounded ${isCurrentUser ? 'bg-primary text-white' : 'bg-light'}" style="max-width: 70%;">
          <div><strong>${message.sender.name}:</strong></div>
          ${message.body ? `<div>${message.body}</div>` : ''}
          ${attachmentsHtml}
          <small class="d-block mt-1 ${isCurrentUser ? 'text-white-50' : 'text-muted'}">${timeStr}</small>
        </div>
      </div>
    `;
    
    return messageDiv;
  }

  // Request notification permission
  if ('Notification' in window && Notification.permission === 'default') {
    Notification.requestPermission();
  }

  // Handle page visibility change to adjust polling frequency
  document.addEventListener('visibilitychange', function() {
    if (document.hidden) {
      // Page is hidden, reduce polling frequency if using polling
      if (chatSystem.options.pollingInterval) {
        chatSystem.options.pollingInterval = 10000; // 10 seconds
      }
    } else {
      // Page is visible, normal polling frequency
      if (chatSystem.options.pollingInterval) {
        chatSystem.options.pollingInterval = 5000; // 5 seconds
      }
    }
  });

  // Clean up when page unloads
  window.addEventListener('beforeunload', function() {
    chatSystem.disconnect();
  });
</script>
@endsection
