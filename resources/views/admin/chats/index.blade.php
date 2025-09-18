@extends('layouts.app')

@push('styles')
<style>
.chat-container {
    height: calc(100vh - 150px);
    min-height: 520px;
    border: 1px solid #e9ecef;
    border-radius: 15px 15px 0 0; /* no bottom rounding to avoid covering input */
    overflow: hidden;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
}

.chat-sidebar {
    background: #f8f9fa;
    border-right: 1px solid #e9ecef;
    height: 100%;
    overflow-y: auto;
}

.chat-sidebar .p-3 {
    background: white;
    border-bottom: 2px solid #e9ecef;
}

.chat-main {
    height: 100%;
    display: flex;
    flex-direction: column;
    background: #f0f2f5;
}

.chat-item {
    padding: 16px 20px;
    border-bottom: 1px solid #f0f0f0;
    cursor: pointer;
    transition: all 0.3s ease;
    position: relative;
    background: white;
    margin: 0 8px 4px 8px;
    border-radius: 12px;
}

.chat-item:hover {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.15);
    border-left: 4px solid #007bff;
}

.chat-item.active {
    background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
    border-left: 4px solid #1877f2;
    color: #1877f2;
    box-shadow: 0 4px 12px rgba(24, 119, 242, 0.2);
}

.chat-item.active .chat-name {
    color: #1877f2;
    font-weight: 700;
}

.chat-item.active .chat-last-message {
    color: #1877f2 !important;
}

.chat-name {
    font-weight: 600;
    font-size: 15px;
    margin-bottom: 4px;
}

.chat-last-message {
    font-size: 13px;
    color: #65676b;
    display: flex;
    align-items: center;
    gap: 4px;
}

.chat-time {
    font-size: 12px;
    color: #8a8d91;
    font-weight: 500;
}

.chat-avatar {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: bold;
    font-size: 18px;
}

.chat-info {
    flex: 1;
    min-width: 0;
}

.chat-name {
    font-weight: 600;
    margin-bottom: 2px;
}

.chat-last-message {
    font-size: 13px;
    color: #6c757d;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.chat-meta {
    text-align: right;
    min-width: 60px;
}

.chat-time {
    font-size: 12px;
    color: #6c757d;
    margin-bottom: 5px;
}

.unread-badge {
    background: #dc3545;
    color: white;
    border-radius: 50%;
    padding: 2px 6px;
    font-size: 11px;
    font-weight: bold;
    min-width: 18px;
    text-align: center;
}

.chat-messages-container {
    height: 420px; /* fixed, short chat window */
    padding: 16px 20px; /* keep gap above input */
    overflow-y: auto; /* only inner scroll */
    background: linear-gradient(135deg, #f0f2f5 0%, #e4e6ea 100%);
    background-image: 
        radial-gradient(circle at 20% 80%, rgba(120, 119, 198, 0.1) 0%, transparent 50%),
        radial-gradient(circle at 80% 20%, rgba(255, 119, 198, 0.1) 0%, transparent 50%),
        radial-gradient(circle at 40% 40%, rgba(120, 200, 255, 0.1) 0%, transparent 50%);
    max-width: 900px; /* center and narrow the chat width */
    margin: 0 auto;
    width: 100%;
}

.chat-input-container {
    padding: 20px;
    border-top: 1px solid #dee2e6;
    background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
    box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.05);
    max-width: 900px; /* align with messages width */
    margin: 0 auto;
    width: 100%;
}

/* Message Layout - Improved separation */
.message-wrapper {
    margin-bottom: 20px;
    display: flex;
    align-items: flex-end;
    clear: both;
    animation: messageSlideIn 0.4s ease-out;
}

.message-wrapper.sent {
    justify-content: flex-end;
    margin-left: 0;
    margin-right: 10px;
}

.message-wrapper.received {
    justify-content: flex-start;
    margin-right: 10px;
    margin-left: 10px;
}

.message-content {
    display: flex;
    align-items: flex-end;
    gap: 8px;
    max-width: 100%;
    position: relative;
}

.message-wrapper.sent .message-content {
    flex-direction: row-reverse;
}

.message-wrapper.received .message-content {
    flex-direction: row;
}

/* Message Avatars - Enhanced */
.message-avatar {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
    font-weight: bold;
    color: white;
    flex-shrink: 0;
    border: 3px solid rgba(255, 255, 255, 0.2);
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
}

.admin-avatar {
    background: linear-gradient(135deg, #007bff, #0056b3);
    border-color: rgba(0, 123, 255, 0.3);
}

.user-avatar {
    background: linear-gradient(135deg, #28a745, #20c997);
    border-color: rgba(40, 167, 69, 0.3);
}

/* Message Bubbles */
.message-bubble {
    padding: 12px 16px;
    border-radius: 18px;
    word-wrap: break-word;
    position: relative;
    max-width: 62%;
}

.admin-bubble {
    background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
    color: white;
    border-radius: 20px 20px 5px 20px;
    box-shadow: 0 4px 20px rgba(0, 123, 255, 0.3);
    position: relative;
    border: 2px solid rgba(255, 255, 255, 0.1);
}

.admin-bubble::before {
    content: '';
    position: absolute;
    bottom: 0;
    right: -10px;
    width: 0;
    height: 0;
    border: 10px solid transparent;
    border-top-color: #0056b3;
    border-right: 0;
    border-bottom: 0;
}

.user-bubble {
    background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
    border: 2px solid #e9ecef;
    color: #333333;
    border-radius: 20px 20px 20px 5px;
    box-shadow: 0 3px 15px rgba(0, 0, 0, 0.1);
    position: relative;
}

.user-bubble::before {
    content: '';
    position: absolute;
    bottom: 0;
    left: -10px;
    width: 0;
    height: 0;
    border: 10px solid transparent;
    border-top-color: #ffffff;
    border-left: 0;
    border-bottom: 0;
}

/* Message Content */
.sender-name {
    font-size: 12px;
    font-weight: 600;
    color: #1877f2;
    margin-bottom: 4px;
}

.message-text {
    font-size: 15px;
    line-height: 1.4;
    word-break: break-word;
}

.message-time {
    font-size: 11px;
    opacity: 0.7;
    margin-top: 8px;
    text-align: right;
    font-weight: 500;
    letter-spacing: 0.3px;
}

.user-bubble .message-time {
    color: #65676b;
}

.admin-bubble .message-time {
    color: rgba(255, 255, 255, 0.8);
}

/* Message Attachments */
.message-attachments {
    margin-top: 8px;
}

.attachment-link {
    display: inline-block;
    padding: 6px 12px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 12px;
    color: inherit;
    text-decoration: none;
    font-size: 13px;
    transition: all 0.2s ease;
}

.attachment-link:hover {
    background: rgba(255, 255, 255, 0.3);
    transform: translateY(-1px);
}

.user-bubble .attachment-link {
    background: #f0f2f5;
    color: #1877f2;
}

.user-bubble .attachment-link:hover {
    background: #e4e6ea;
}

/* Message Animations - Enhanced */
@keyframes messageSlideIn {
    from {
        opacity: 0;
        transform: translateY(30px) scale(0.95);
    }
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

@keyframes messageBounceIn {
    0% {
        opacity: 0;
        transform: scale(0.3);
    }
    50% {
        opacity: 1;
        transform: scale(1.05);
    }
    70% {
        transform: scale(0.9);
    }
    100% {
        opacity: 1;
        transform: scale(1);
    }
}

.message-bubble:hover {
    transform: translateY(-3px);
    transition: all 0.3s ease;
}

.admin-bubble:hover {
    box-shadow: 0 6px 25px rgba(0, 123, 255, 0.4);
}

.user-bubble:hover {
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.15);
    border-color: #007bff;
}

.empty-chat {
    display: flex;
    align-items: center;
    justify-content: center;
    height: 100%;
    color: #6c757d;
    flex-direction: column;
}

.typing-indicator {
    padding: 12px 20px;
    font-size: 13px;
    color: #65676b;
    font-style: italic;
    background: linear-gradient(135deg, rgba(0,123,255,0.1) 0%, rgba(255,255,255,0.9) 100%);
    border-top: 1px solid #e4e6ea;
    border-radius: 0 0 20px 20px;
    animation: typingPulse 1.5s ease-in-out infinite;
}

@keyframes typingPulse {
    0%, 100% { opacity: 0.7; }
    50% { opacity: 1; }
}

.typing-indicator i {
    animation: typing-dots 1.5s infinite;
}

@keyframes typing-dots {
    0%, 60%, 100% { transform: translateY(0); }
    30% { transform: translateY(-10px); }
}

.input-group:focus-within {
    border-color: #007bff !important;
    box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.2);
    transform: translateY(-1px);
}

.chat-input-container {
    padding: 15px 20px;
    border-top: 1px solid #e4e6ea;
    background: white;
}

.message-bubble:hover {
    transform: translateY(-1px);
    transition: all 0.2s ease;
}

.online-status {
    position: absolute;
    bottom: 2px;
    right: 2px;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    border: 2px solid white;
}

.online-status.online {
    background: #28a745;
}

.online-status.offline {
    background: #6c757d;
}

/* Header Styles - Enhanced */
.chat-admin-header {
    background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
    border-radius: 20px;
    padding: 25px 30px;
    color: white;
    box-shadow: 0 10px 30px rgba(0, 123, 255, 0.3);
    position: relative;
    overflow: hidden;
}

.chat-admin-header::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(45deg, transparent 30%, rgba(255, 255, 255, 0.1) 50%, transparent 70%);
    transform: translateX(-100%);
    transition: transform 0.6s;
}

.chat-admin-header:hover::before {
    transform: translateX(100%);
}

.chat-admin-header h3 {
    color: white;
}

.chat-admin-header .text-primary {
    color: #ffd700 !important;
}

.pulse-dot {
    animation: pulse-glow 2s infinite;
}

@keyframes pulse-glow {
    0% { opacity: 1; }
    50% { opacity: 0.5; }
    100% { opacity: 1; }
}

.admin-status .badge {
    font-size: 13px;
    border-radius: 25px;
}

/* Cải thiện chat container */
.chat-container {
    background: white;
    border-radius: 20px 20px 0 0; /* remove bottom rounding */
    overflow: hidden;
    box-shadow: 0 15px 40px rgba(0,0,0,0.12);
    border: 1px solid #e9ecef;
    transition: all 0.3s ease;
}

.chat-container:hover {
    box-shadow: 0 20px 50px rgba(0,0,0,0.15);
}

/* Responsive improvements */
@media (max-width: 768px) {
    .chat-messages-container, .chat-input-container { max-width: 680px; }
    .message-wrapper.sent { margin-left: 0; margin-right: 5px; }
    .message-wrapper.received { margin-right: 5px; margin-left: 5px; }
    
    .chat-messages-container {
        padding: 15px;
    }
    
    .message-avatar {
        width: 30px;
        height: 30px;
        font-size: 12px;
    }
    
    .message-bubble {
        padding: 10px 14px;
    }
}
</style>
@endpush

@section('content')
<div class="container-fluid py-3">
    <!-- Header -->
    <div class="chat-admin-header mb-4">
        <div class="d-flex justify-content-between align-items-center">
            <div class="header-left">
                <h3 class="mb-1">
                    <i class="fas fa-comments text-primary"></i> 
                    <span class="fw-bold">Live Chat Admin</span>
                </h3>
                <p class="text-muted mb-0">Quản lý cuộc trò chuyện với khách hàng</p>
            </div>
            <div class="header-right d-flex align-items-center gap-3">
                <div class="admin-status">
                    <span class="badge bg-success px-3 py-2">
                        <i class="fas fa-circle pulse-dot"></i>
                        Admin Online: <span id="admin-count" class="fw-bold">—</span>
                    </span>
                </div>
                <button class="btn btn-outline-primary" onclick="refreshChats()">
                    <i class="fas fa-sync-alt"></i> Làm mới
                </button>
            </div>
        </div>
    </div>

    <div class="row chat-container">
        <!-- Sidebar - Danh sách chat -->
        <div class="col-md-4 p-0">
            <div class="chat-sidebar">
                <div class="p-3 border-bottom">
                    <div class="input-group">
                        <input type="text" class="form-control" placeholder="Tìm kiếm cuộc trò chuyện..." id="search-chats">
                        <button class="btn btn-outline-secondary" type="button">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
                
                <div id="chat-list">
                    @forelse($chats as $chat)
                        <div class="chat-item" data-chat-id="{{ $chat->id }}" onclick="selectChat({{ $chat->id }})">
                            <div class="d-flex align-items-center">
                                <div class="chat-avatar position-relative">
                                    {{ strtoupper(substr($chat->user->name ?? 'U', 0, 1)) }}
                                    <div class="online-status offline"></div>
                                </div>
                                <div class="chat-info ms-3">
                                    <div class="chat-name">{{ $chat->user->name ?? 'Khách' }}</div>
                                    <div class="chat-last-message">
                                        @php
                                            $last = $chat->messages->first();
                                            $preview = $last && $last->body ? \Illuminate\Support\Str::limit($last->body, 40) : null;
                                        @endphp
                                        @if($preview)
                                            <i class="fas fa-comment-dots"></i> {{ $preview }}
                                        @else
                                            <i class="fas fa-user"></i> Khách đang chat
                                        @endif
                                    </div>
                                </div>
                                <div class="chat-meta">
                                    <div class="chat-time">
                                        @if($chat->last_message_at)
                                            @php
                                                $lastMessageTime = \Carbon\Carbon::parse($chat->last_message_at);
                                                $now = \Carbon\Carbon::now();
                                                
                                                if ($lastMessageTime->isToday()) {
                                                    echo $lastMessageTime->format('H:i');
                                                } elseif ($lastMessageTime->isYesterday()) {
                                                    echo 'Hôm qua';
                                                } else {
                                                    echo $lastMessageTime->format('d/m');
                                                }
                                            @endphp
                                        @else
                                            <span class="text-muted">Chưa có</span>
                                        @endif
                                    </div>
                                    <div class="unread-badge" id="unread-{{ $chat->id }}" style="display: none;">0</div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="p-4 text-center text-muted">
                            <i class="fas fa-comments fa-3x mb-3"></i>
                            <p>Chưa có cuộc trò chuyện nào</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Main Chat Area -->
        <div class="col-md-8 p-0">
            <div class="chat-main">
                <!-- Chat Header -->
                <div id="chat-header" class="p-3 border-bottom bg-white" style="display: none; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center">
                            <div class="chat-avatar me-3 position-relative">
                                <span id="selected-user-avatar">U</span>
                                <div class="online-status online"></div>
                            </div>
                            <div>
                                <h6 class="mb-0 fw-bold" id="selected-user-name">Chọn cuộc trò chuyện</h6>
                                <small class="text-success" id="selected-user-status">
                                    <i class="fas fa-circle" style="font-size: 8px;"></i> Online
                                </small>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <button class="btn btn-light btn-sm rounded-circle" title="Thông tin">
                                <i class="fas fa-info"></i>
                            </button>
                            <div class="dropdown">
                                <button class="btn btn-light btn-sm rounded-circle dropdown-toggle" data-bs-toggle="dropdown" title="Tùy chọn">
                                    <i class="fas fa-ellipsis-v"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li><a class="dropdown-item" href="#" onclick="assignChat()">
                                        <i class="fas fa-user-check text-primary"></i> Nhận chat
                                    </a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item text-danger" href="#" onclick="closeChat()">
                                        <i class="fas fa-times"></i> Đóng chat
                                    </a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Messages Area -->
                <div id="chat-messages" class="chat-messages-container">
                    <div class="empty-chat">
                        <i class="fas fa-comments fa-4x mb-3 text-primary"></i>
                        <h5 class="text-dark">Chọn một cuộc trò chuyện để bắt đầu</h5>
                        <p class="text-muted">Chọn cuộc trò chuyện từ danh sách bên trái để xem tin nhắn</p>
                        <div class="mt-4">
                            <div class="d-flex justify-content-center gap-3">
                                <div class="text-center">
                                    <div class="message-avatar admin-avatar mx-auto mb-2">
                                        <span>A</span>
                                    </div>
                                    <small class="text-muted">Admin</small>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-exchange-alt text-primary fa-2x"></i>
                                </div>
                                <div class="text-center">
                                    <div class="message-avatar user-avatar mx-auto mb-2">
                                        <span>K</span>
                                    </div>
                                    <small class="text-muted">Khách hàng</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Typing Indicator -->
                <div id="typing-indicator" class="typing-indicator" style="display: none;">
                    <i class="fas fa-ellipsis-h"></i>
                    <span id="typing-user">Khách</span> đang nhập...
                </div>

                <!-- Message Input -->
                <div id="chat-input" class="chat-input-container" style="display: none;">
                    <form id="message-form">
                        @csrf
                        <div class="d-flex align-items-end gap-3">
                            <div class="flex-grow-1">
                                <div class="input-group" style="border-radius: 30px; overflow: hidden; border: 2px solid #e4e6ea; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                                    <input type="text" class="form-control border-0" 
                                           placeholder="Nhập tin nhắn..." 
                                           id="message-input" 
                                           autocomplete="off"
                                           style="padding: 15px 20px; font-size: 15px; background: white;">
                                    <input type="file" id="file-input" multiple style="display: none;" accept="image/*,.pdf,.doc,.docx">
                                    <button type="button" class="btn border-0 px-3" onclick="document.getElementById('file-input').click()" title="Đính kèm file">
                                        <i class="fas fa-paperclip text-primary"></i>
                                    </button>
                                    <button type="button" class="btn border-0 px-3" title="Emoji">
                                        <i class="fas fa-smile text-warning"></i>
                                    </button>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary rounded-circle p-2" style="width: 45px; height: 45px; background: linear-gradient(135deg, #007bff, #0056b3); border: none; box-shadow: 0 4px 15px rgba(0,123,255,0.3);" title="Gửi">
                                <i class="fas fa-paper-plane"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<!-- Realtime disabled on admin page to avoid conflicts; using polling-only -->
<script>
let currentChatId = null;
let currentChatData = null;
let typingTimer = null;
let loadedMessageIds = new Set(); // avoid duplicates per session
let infiniteScrollInitialized = false;
let pollingTimer = null;
let lastMessageTime = null;

// Chọn chat
function selectChat(chatId) {
    // Remove active class from all chat items
    document.querySelectorAll('.chat-item').forEach(item => {
        item.classList.remove('active');
    });
    
    // Add active class to selected chat
    document.querySelector(`[data-chat-id="${chatId}"]`).classList.add('active');
    
    currentChatId = chatId;
    loadedMessageIds.clear();
    loadChatData(chatId);
    const messagesContainer = document.getElementById('chat-messages');
    messagesContainer.innerHTML = '<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Đang tải...</div>';
    // Initial load
    loadMessages(chatId);
    
    // Show chat interface
    document.getElementById('chat-header').style.display = 'block';
    document.getElementById('chat-input').style.display = 'block';
    
    // Clear unread count
    const unreadBadge = document.getElementById(`unread-${chatId}`);
    if (unreadBadge) {
        unreadBadge.style.display = 'none';
        unreadBadge.textContent = '0';
    }

    // Init infinite scroll once
    if (!infiniteScrollInitialized) {
        setupInfiniteScroll(chatId);
        infiniteScrollInitialized = true;
    }
}

// Load chat data
function loadChatData(chatId) {
    // Find chat data from the list
    const chatItem = document.querySelector(`[data-chat-id="${chatId}"]`);
    const userName = chatItem.querySelector('.chat-name').textContent;
    const userAvatar = chatItem.querySelector('.chat-avatar').textContent;
    
    document.getElementById('selected-user-name').textContent = userName;
    document.getElementById('selected-user-avatar').textContent = userAvatar;
    document.getElementById('selected-user-status').textContent = 'Online'; // Will be updated by presence
}

// Load messages
function loadMessages(chatId) {
    const messagesContainer = document.getElementById('chat-messages');
    // keep spinner from selectChat
    
    axios.get(`/chat/${chatId}/messages`, {
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        }
    })
        .then(response => {
            messagesContainer.innerHTML = '';
            if (response.data && response.data.length > 0) {
                response.data.forEach(message => {
                    appendMessage(message);
                });
                const last = response.data[response.data.length - 1];
                lastMessageTime = last.created_at_iso;
            } else {
                messagesContainer.innerHTML = `<div class="text-center text-muted py-4">
                    <i class="fas fa-comments fa-3x mb-3"></i>
                    <h5>Chưa có tin nhắn</h5>
                    <p>Hãy bắt đầu cuộc trò chuyện!</p>
                </div>`;
            }
            // Always jump to the newest (bottom) on open
            scrollToBottom();
            // Extra ensure after DOM paint
            setTimeout(scrollToBottom, 50);
            // Start polling for new messages
            startPolling();
        })
        .catch(error => {
            console.error('Error loading messages:', error);
            let errorMsg = 'Không thể tải tin nhắn';
            if (error.response) {
                if (error.response.status === 401) {
                    errorMsg = 'Phiên đăng nhập đã hết hạn. Vui lòng đăng nhập lại.';
                } else if (error.response.status === 403) {
                    errorMsg = 'Không có quyền truy cập chat này.';
                } else {
                    errorMsg = error.response.data?.message || 'Lỗi server';
                }
            }
            messagesContainer.innerHTML = `<div class="text-center text-danger py-4">
                <i class="fas fa-exclamation-triangle fa-2x mb-3"></i>
                <h5>Lỗi tải tin nhắn</h5>
                <p>${errorMsg}</p>
                <button class="btn btn-primary btn-sm" onclick="loadMessages(${chatId})">Thử lại</button>
            </div>`;
        });
}

// Append message to chat
function appendMessage(message) {
    const messagesContainer = document.getElementById('chat-messages');
    // Safety: ignore messages from other chats if payload has chat_id
    if (message.chat_id && Number(message.chat_id) !== Number(currentChatId)) {
        return;
    }
    // Avoid duplicates
    if (message.id && loadedMessageIds.has(message.id)) {
        return;
    }
    
    // Kiểm tra nhiều cách để xác định admin
    let isAdmin = false;
    if (message.sender?.is_admin === true || message.sender?.is_admin === 1) {
        isAdmin = true;
    } else if (message.sender_is_admin === true || message.sender_is_admin === 1) {
        isAdmin = true;
    } else if (message.sender?.name && message.sender.name.toLowerCase().includes('admin')) {
        isAdmin = true;
    }
    
    const senderName = message.sender?.name || (isAdmin ? 'Admin' : 'Khách');
    const senderAvatar = senderName.charAt(0).toUpperCase();
    
    console.log('Appending message - DEBUG:', {
        message: message,
        isAdmin: isAdmin,
        senderName: senderName,
        'sender.is_admin': message.sender?.is_admin,
        'sender_is_admin': message.sender_is_admin,
        'sender object': message.sender
    });
    
    const messageDiv = document.createElement('div');
    messageDiv.className = `message-wrapper ${isAdmin ? 'sent' : 'received'}`;
    messageDiv.setAttribute('data-created-at', message.created_at_iso || message.created_at);
    if (message.id) loadedMessageIds.add(message.id);
    
    let attachmentsHtml = '';
    if (message.attachments && message.attachments.length > 0) {
        attachmentsHtml = '<div class="message-attachments">' +
            message.attachments.map(att => 
                `<a href="${att.url}" target="_blank" class="attachment-link">
                    <i class="fas fa-paperclip"></i> File đính kèm
                </a>`
            ).join('') + '</div>';
    }
    
    if (isAdmin) {
        // Admin messages (right side - blue)
        messageDiv.innerHTML = `
            <div class="message-content">
                <div class="message-bubble admin-bubble">
                    <div class="sender-name" style="color: rgba(255,255,255,0.9); font-size: 11px; margin-bottom: 4px;">${senderName}</div>
                    <div class="message-text">${message.body || ''}</div>
                    ${attachmentsHtml}
                    <div class="message-time">${new Date(message.created_at).toLocaleTimeString('vi-VN', {hour: '2-digit', minute: '2-digit'})}</div>
                </div>
                <div class="message-avatar admin-avatar">
                    <span>${senderAvatar}</span>
                </div>
            </div>
        `;
    } else {
        // User messages (left side - green)
        messageDiv.innerHTML = `
            <div class="message-content">
                <div class="message-avatar user-avatar">
                    <span>${senderAvatar}</span>
                </div>
                <div class="message-bubble user-bubble">
                    <div class="sender-name">${senderName}</div>
                    <div class="message-text">${message.body || ''}</div>
                    ${attachmentsHtml}
                    <div class="message-time">${new Date(message.created_at).toLocaleTimeString('vi-VN', {hour: '2-digit', minute: '2-digit'})}</div>
                </div>
            </div>
        `;
    }
    
    messagesContainer.appendChild(messageDiv);
    // Do not force scroll here to respect user reading unless message belongs to current chat and near bottom
    const nearBottom = (messagesContainer.scrollHeight - messagesContainer.scrollTop - messagesContainer.clientHeight) < 80;
    if (nearBottom) scrollToBottom();
}

// Scroll to bottom
function scrollToBottom() {
    const messagesContainer = document.getElementById('chat-messages');
    messagesContainer.scrollTop = messagesContainer.scrollHeight;
}

// Infinite scroll: load older messages when reaching top (delegate to realtime)
function setupInfiniteScroll(chatId) {
    const container = document.getElementById('chat-messages');
    let loadingOlder = false;
    let noMoreOlder = false;
    container.onscroll = async function() {
        if (container.scrollTop <= 0 && !loadingOlder && !noMoreOlder) {
            loadingOlder = true;
            const first = container.firstElementChild;
            const oldestTime = first?.getAttribute('data-created-at');
            try {
                const resp = await axios.get(`/chat/${chatId}/messages`, {
                    params: { before: oldestTime, limit: 50 },
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    }
                });
                if (Array.isArray(resp.data) && resp.data.length) {
                    const prevHeight = container.scrollHeight;
                    resp.data.forEach(m => {
                        const el = buildMessageElement(m);
                        container.insertBefore(el, container.firstChild);
                        if (m.id) loadedMessageIds.add(m.id);
                    });
                    const newHeight = container.scrollHeight;
                    container.scrollTop = newHeight - prevHeight;
                } else {
                    noMoreOlder = true;
                }
            } catch (e) {
                console.error('Load older failed', e);
            } finally {
                loadingOlder = false;
            }
        }
    };
}

// Helper to build DOM element (used for prepend)
function buildMessageElement(message) {
    const temp = document.createElement('div');
    appendMessage(message); // appends at bottom to create structure
    const appended = document.getElementById('chat-messages').lastElementChild;
    const clone = appended.cloneNode(true);
    document.getElementById('chat-messages').removeChild(appended);
    return clone;
}

// Send message
document.getElementById('message-form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    if (!currentChatId) return;
    
    const messageInput = document.getElementById('message-input');
    const fileInput = document.getElementById('file-input');
    const message = messageInput.value.trim();
    
    if (!message && !fileInput.files.length) return;
    
    // Disable form để tránh gửi nhiều lần
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalBtnContent = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    
    const formData = new FormData();
    if (message) formData.append('body', message);
    
    for (let file of fileInput.files) {
        formData.append('files[]', file);
    }
    
    axios.post(`/chat/${currentChatId}/messages`, formData, {
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'multipart/form-data'
        }
    })
        .then(response => {
            console.log('Full response:', response.data);
            messageInput.value = '';
            fileInput.value = '';
            
            // Hiển thị tin nhắn ngay lập tức thay vì chờ Echo broadcast
            if (response.data && response.data.message) {
                console.log('Message data received:', response.data.message);
                appendMessage(response.data.message);
                console.log('Message sent and displayed immediately');
            } else {
                console.error('No message data in response:', response.data);
            }
            
            // Re-enable form
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalBtnContent;
        })
        .catch(error => {
            console.error('Error sending message:', error);
            alert('Lỗi gửi tin nhắn: ' + (error.response?.data?.message || 'Không xác định'));
            
            // Re-enable form
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalBtnContent;
        });
});

// Typing indicator
document.getElementById('message-input').addEventListener('input', function() {
    if (!currentChatId) return;
    
    axios.post(`/chat/${currentChatId}/typing`, { typing: true });
    
    clearTimeout(typingTimer);
    typingTimer = setTimeout(() => {
        axios.post(`/chat/${currentChatId}/typing`, { typing: false });
    }, 1000);
});

// Enter key để gửi tin nhắn
document.getElementById('message-input').addEventListener('keypress', function(e) {
    if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        document.getElementById('message-form').dispatchEvent(new Event('submit'));
    }
});

// Polling for new messages on active chat
function startPolling() {
    stopPolling();
    if (!currentChatId) return;
    const interval = 3000;
    const tick = async () => {
        try {
            const params = lastMessageTime ? { after: lastMessageTime } : {};
            const resp = await axios.get(`/chat/${currentChatId}/messages`, {
                params,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            });
            if (Array.isArray(resp.data) && resp.data.length) {
                resp.data.forEach(m => appendMessage(m));
                const last = resp.data[resp.data.length - 1];
                lastMessageTime = last.created_at_iso || last.created_at;
            }
        } catch (e) {
            console.warn('Polling error', e);
        } finally {
            pollingTimer = setTimeout(tick, interval);
        }
    };
    pollingTimer = setTimeout(tick, interval);
}

function stopPolling() {
    if (pollingTimer) {
        clearTimeout(pollingTimer);
        pollingTimer = null;
    }
}

// Refresh chats
function refreshChats() {
    location.reload();
}

// Assign chat
function assignChat() {
    if (!currentChatId) return;
    
    const formData = new FormData();
    formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);
    
    axios.post(`/admin/chats/${currentChatId}/assign`, formData)
        .then(() => {
            alert('Đã nhận chat thành công!');
            refreshChats();
        })
        .catch(error => {
            console.error('Error assigning chat:', error);
            const errorMsg = error.response?.data?.message || error.message || 'Lỗi không xác định';
            alert('Lỗi nhận chat: ' + errorMsg);
        });
}

// Close chat
function closeChat() {
    if (!currentChatId) return;
    
    if (confirm('Đóng chat này?')) {
        axios.post(`/admin/chats/${currentChatId}/close`, {}, {
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
            .then(() => {
                alert('Đã đóng chat');
                refreshChats();
            })
            .catch(error => {
                console.error('Error closing chat:', error);
                alert('Lỗi đóng chat: ' + (error.response?.data?.message || 'Không xác định'));
            });
    }
}

// Echo setup
window.Echo.join('presence.admins')
    .here((users) => document.getElementById('admin-count').textContent = users.length)
    .joining((u) => document.getElementById('admin-count').textContent = 'cập nhật…')
    .leaving((u) => document.getElementById('admin-count').textContent = 'cập nhật…');

// Auto-select first chat if available
document.addEventListener('DOMContentLoaded', function() {
    const firstChat = document.querySelector('.chat-item');
    if (firstChat && !currentChatId) {
        const chatId = firstChat.getAttribute('data-chat-id');
        selectChat(parseInt(chatId));
    }
});

// Realtime listeners removed to avoid conflicts; polling will update unread/time via sidebar refresh if needed

// Update unread count
function updateUnreadCount(chatId) {
    const unreadBadge = document.getElementById(`unread-${chatId}`);
    if (unreadBadge) {
        let count = parseInt(unreadBadge.textContent) || 0;
        count++;
        unreadBadge.textContent = count;
        unreadBadge.style.display = 'block';
    }
}

// Move chat to top and update time
function moveToTop(chatId) {
    const chatItem = document.querySelector(`[data-chat-id="${chatId}"]`);
    const chatList = document.getElementById('chat-list');
    if (chatItem && chatList) {
        // Update time to current time
        const timeElement = chatItem.querySelector('.chat-time');
        if (timeElement) {
            const now = new Date();
            timeElement.textContent = now.toLocaleTimeString('vi-VN', {hour: '2-digit', minute: '2-digit'});
        }
        
        // Move to top
        chatList.insertBefore(chatItem, chatList.firstChild);
    }
}
</script>
@endsection
