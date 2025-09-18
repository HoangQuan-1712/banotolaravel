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
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script src="{{ asset('js/echo.pusher.min.js') }}"></script>
<script>
const chatId = {{ $chat->id }};
const adminId = {{ auth()->id() }};
const messagesEl = document.getElementById('admin-chat-messages');
const inputEl = document.getElementById('admin-msg-input');
const fileEl = document.getElementById('admin-file-input');
const typingEl = document.getElementById('admin-typing-indicator');
const userOnlineStatus = document.getElementById('user-online-status');

function appendMessage(m) {
    const isAdmin = m.sender.is_admin;
    const side = isAdmin ? 'end' : 'start';
    const bgColor = isAdmin ? 'primary' : 'light';
    const textColor = isAdmin ? 'white' : 'dark';
    
    const wrapper = document.createElement('div');
    wrapper.className = `mb-3 d-flex justify-content-${side}`;
    
    let files = '';
    if (m.attachments && m.attachments.length) {
        files = '<div class="mt-2 d-flex flex-wrap gap-1">' +
            m.attachments.map(a => `<a target="_blank" href="${a.url}" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-paperclip"></i> File
            </a>`).join('') + '</div>';
    }
    
    wrapper.innerHTML = `
        <div class="border rounded p-3 bg-${bgColor} text-${textColor}" style="max-width:75%">
            <div class="small opacity-75 mb-1">
                ${isAdmin ? 'Admin' : (m.sender.name || 'Khách')} • 
                ${new Date(m.created_at).toLocaleString()}
            </div>
            <div>${m.body ? m.body.replace(/</g,'&lt;') : ''}</div>
            ${files}
        </div>
    `;
    messagesEl.appendChild(wrapper);
    messagesEl.scrollTop = messagesEl.scrollHeight;
}

// Load messages (oldest to newest)
axios.get(`{{ route('chat.messages.index', $chat) }}`).then(res => {
    messagesEl.innerHTML = '';
    (Array.isArray(res.data) ? res.data : []).forEach(appendMessage);
    messagesEl.scrollTop = messagesEl.scrollHeight;
});

// Echo setup
window.Echo.private(`chat.${chatId}`)
    .listen('.message.sent', (e) => { appendMessage(e); })
    .listen('.chat.typing', (e) => {
        if (!e.is_admin) {
            typingEl.style.display = e.typing ? 'block' : 'none';
        }
    });

// Send typing
let typingTimer;
inputEl.addEventListener('input', () => {
    axios.post(`{{ route('chat.typing', $chat) }}`, { typing: true });
    clearTimeout(typingTimer);
    typingTimer = setTimeout(() => {
        axios.post(`{{ route('chat.typing', $chat) }}`, { typing: false });
    }, 1200);
});

// Send message
document.getElementById('admin-chat-form').addEventListener('submit', function(e) {
    e.preventDefault();
    const text = inputEl.value.trim();
    const form = new FormData();
    
    if (text.length) form.append('body', text);
    if (fileEl.files.length) {
        for (const f of fileEl.files) form.append('files[]', f);
    }
    
    if (!text.length && !fileEl.files.length) return;
    
    axios.post(`{{ route('chat.messages.store', $chat) }}`, form, {
        headers: { 'Content-Type': 'multipart/form-data' }
    }).then(res => {
        inputEl.value = '';
        fileEl.value = null;
        // Message sẽ được hiển thị qua Echo broadcast
    }).catch(err => {
        alert('Lỗi gửi tin nhắn: ' + (err.response?.data?.message || 'Không xác định'));
    });
});

// Quick messages
document.querySelectorAll('.quick-message').forEach(btn => {
    btn.addEventListener('click', function() {
        inputEl.value = this.dataset.message;
        inputEl.focus();
    });
});
</script>
@endsection
