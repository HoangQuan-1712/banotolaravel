/**
 * Chat Polling System - Thay thế cho WebSocket/Pusher
 * Sử dụng AJAX polling để cập nhật tin nhắn real-time
 */

class ChatPolling {
    constructor(chatId, messagesContainer, options = {}) {
        this.chatId = chatId;
        this.messagesContainer = messagesContainer;
        this.lastMessageTime = null;
        this.pollingInterval = options.interval || 2000; // 2 seconds
        this.isPolling = false;
        this.pollTimer = null;
        this.messageIds = new Set(); // Track message IDs to prevent duplicates
        
        // Callbacks
        this.onNewMessage = options.onNewMessage || (() => {});
        this.onError = options.onError || ((error) => console.error('Chat polling error:', error));
        
        // Start polling
        this.startPolling();
    }

    startPolling() {
        if (this.isPolling) return;
        
        this.isPolling = true;
        this.poll();
    }

    stopPolling() {
        this.isPolling = false;
        if (this.pollTimer) {
            clearTimeout(this.pollTimer);
            this.pollTimer = null;
        }
    }

    async poll() {
        if (!this.isPolling) return;

        try {
            const url = this.buildPollUrl();
            console.log('[POLLING] Starting poll request to:', url);
            
            const response = await fetch(url, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                },
                credentials: 'same-origin'
            });

            console.log('[POLLING] Response status:', response.status);

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }

            const messages = await response.json();
            console.log('[POLLING] Received messages:', messages.length, messages);
            
            if (messages.length > 0) {
                this.processNewMessages(messages);
                // Update last message time
                const lastMessage = messages[messages.length - 1];
                this.lastMessageTime = lastMessage.created_at_iso;
                console.log('[POLLING] Updated lastMessageTime to:', this.lastMessageTime);
            }

        } catch (error) {
            console.error('[POLLING] Error:', error);
            this.onError(error);
        }

        // Schedule next poll
        if (this.isPolling) {
            this.pollTimer = setTimeout(() => this.poll(), this.pollingInterval);
        }
    }

    buildPollUrl() {
        const baseUrl = `/chat/${this.chatId}/messages`;
        const params = new URLSearchParams();
        
        if (this.lastMessageTime) {
            params.append('after', this.lastMessageTime);
        }
        
        const url = params.toString() ? `${baseUrl}?${params.toString()}` : baseUrl;
        console.log('[POLLING] Polling URL:', url);
        return url;
    }

    processNewMessages(messages) {
        messages.forEach(message => {
            // Check if we already have this message
            if (!this.messageIds.has(message.id)) {
                this.messageIds.add(message.id);
                this.onNewMessage(message);
            }
        });
    }

    // Method to load initial messages
    async loadInitialMessages() {
        try {
            const response = await fetch(`/chat/${this.chatId}/messages`, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                },
                credentials: 'same-origin'
            });

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }

            const messages = await response.json();
            
            // Clear existing messages
            this.messagesContainer.innerHTML = '';
            this.messageIds.clear();
            
            // Add all messages
            messages.forEach(message => {
                this.messageIds.add(message.id);
                this.onNewMessage(message, false); // false = don't scroll for initial load
            });

            // Set last message time for polling
            if (messages.length > 0) {
                const lastMessage = messages[messages.length - 1];
                this.lastMessageTime = lastMessage.created_at_iso;
            }

            // Scroll to bottom after initial load
            this.scrollToBottom();

        } catch (error) {
            this.onError(error);
        }
    }

    scrollToBottom() {
        this.messagesContainer.scrollTop = this.messagesContainer.scrollHeight;
    }

    // Method to send a message
    async sendMessage(formData) {
        try {
            const response = await fetch(`/chat/${this.chatId}/messages`, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                },
                body: formData,
                credentials: 'same-origin'
            });

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }

            const result = await response.json();
            
            if (result.ok && result.message) {
                // Add the sent message immediately
                if (!this.messageIds.has(result.message.id)) {
                    this.messageIds.add(result.message.id);
                    this.onNewMessage(result.message);
                    this.lastMessageTime = result.message.created_at_iso;
                }
            }

            return result;

        } catch (error) {
            this.onError(error);
            throw error;
        }
    }
}

// Helper function to format message HTML
function formatMessage(message, isScrollNeeded = true) {
    const who = message.sender.is_admin ? 'Admin' : (message.sender.name || 'Bạn');
    const side = message.sender.is_admin ? 'start' : 'end';
    const wrapper = document.createElement('div');
    wrapper.className = `mb-3 d-flex justify-content-${side}`;
    wrapper.setAttribute('data-message-id', message.id);
    
    let files = '';
    if (message.attachments && message.attachments.length) {
        files = '<div class="mt-2 d-flex flex-wrap gap-2">' +
            message.attachments.map(a => `<a target="_blank" href="${a.url}" class="btn btn-sm btn-outline-secondary">📎 File</a>`).join('') +
            '</div>';
    }
    
    const messageTime = new Date(message.created_at).toLocaleString('vi-VN', {
        hour: '2-digit',
        minute: '2-digit',
        day: '2-digit',
        month: '2-digit'
    });
    
    wrapper.innerHTML = `
        <div class="border rounded p-2" style="max-width:72%">
            <div class="small text-muted mb-1">${who} • ${messageTime}</div>
            <div>${message.body ? message.body.replace(/</g,'&lt;').replace(/\n/g, '<br>') : ''}</div>
            ${files}
        </div>
    `;
    
    return wrapper;
}

// Export for use in other scripts
window.ChatPolling = ChatPolling;
window.formatMessage = formatMessage;
