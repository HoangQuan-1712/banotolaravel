/**
 * Hybrid Chat System - Pusher + Polling Fallback
 * Sử dụng Pusher cho real-time, polling làm fallback
 */

class ChatRealtime {
    constructor(chatId, messagesContainer, options = {}) {
        this.chatId = chatId;
        this.messagesContainer = messagesContainer;
        this.options = {
            usePusher: true,
            pollingInterval: 5000, // Slower polling as fallback
            maxRetries: 3,
            ...options
        };
        
        this.messageIds = new Set();
        this.lastMessageTime = null;
        this.firstMessageTime = null;
        this.isConnected = false;
        this.retryCount = 0;
        this.pollingTimer = null;
        this.pusherChannel = null;
        this.initialized = false;
        
        // Callbacks
        this.onNewMessage = options.onNewMessage || (() => {});
        this.onError = options.onError || ((error) => console.error('Chat error:', error));
        this.onConnectionChange = options.onConnectionChange || (() => {});
        
        this.initialize();
    }

    async initialize() {
        console.log('[CHAT] Initializing hybrid chat system...');
        
        // Try Pusher first
        if (this.options.usePusher && window.Echo) {
            try {
                await this.initializePusher();
            } catch (error) {
                console.warn('[CHAT] Pusher failed, falling back to polling:', error);
                this.initializePolling();
            }
        } else {
            console.log('[CHAT] Using polling system');
            this.initializePolling();
        }
    }

    async initializePusher() {
        console.log('[CHAT] Setting up Pusher connection...');
        
        return new Promise((resolve, reject) => {
            try {
                // Wait for Echo to be ready
                if (!window.Echo || !window.Echo.connector) {
                    console.log('[CHAT] Echo not ready, falling back to polling');
                    reject(new Error('Echo not ready'));
                    return;
                }

                // Subscribe to private channel
                this.pusherChannel = window.Echo.private(`chat.${this.chatId}`)
                    // Use broadcastAs names with dot-prefix
                    .listen('.message.sent', (e) => {
                        console.log('[PUSHER] New message received:', e);
                        this.handleNewMessage(e);
                    })
                    .listen('.chat.typing', (e) => {
                        console.log('[PUSHER] Typing event:', e);
                        this.handleTypingEvent(e);
                    })
                    .error((error) => {
                        console.error('[PUSHER] Channel error:', error);
                        this.handlePusherError(error);
                        reject(error);
                    });

                // Listen for connection events
                window.Echo.connector.pusher.connection.bind('connected', () => {
                    console.log('[PUSHER] Connection established');
                    this.isConnected = true;
                    this.onConnectionChange(true, 'pusher');
                    resolve();
                });

                window.Echo.connector.pusher.connection.bind('disconnected', () => {
                    console.log('[PUSHER] Connection lost');
                    this.isConnected = false;
                    this.onConnectionChange(false, 'pusher');
                });

                window.Echo.connector.pusher.connection.bind('error', (error) => {
                    console.error('[PUSHER] Connection error:', error);
                    reject(error);
                });

                // Timeout fallback
                setTimeout(() => {
                    if (!this.isConnected) {
                        console.log('[PUSHER] Connection timeout, falling back to polling');
                        reject(new Error('Pusher connection timeout'));
                    }
                }, 3000);

            } catch (error) {
                console.error('[PUSHER] Setup error:', error);
                reject(error);
            }
        });
    }

    initializePolling() {
        console.log('[CHAT] Setting up polling fallback...');
        this.isConnected = true;
        this.onConnectionChange(true, 'polling');
        this.startPolling();
    }

    handlePusherError(error) {
        console.error('[PUSHER] Error occurred:', error);
        this.retryCount++;
        
        if (this.retryCount < this.options.maxRetries) {
            console.log(`[PUSHER] Retrying connection (${this.retryCount}/${this.options.maxRetries})...`);
            setTimeout(() => this.initializePusher(), 3000);
        } else {
            console.log('[PUSHER] Max retries reached, falling back to polling');
            this.initializePolling();
        }
    }

    startPolling() {
        if (this.pollingTimer) return;
        
        this.poll();
    }

    async poll() {
        if (!this.isConnected) return;

        try {
            const url = this.buildPollUrl();
            const response = await fetch(url, {
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
            
            if (messages.length > 0) {
                messages.forEach(message => this.handleNewMessage(message));
                const lastMessage = messages[messages.length - 1];
                this.lastMessageTime = lastMessage.created_at_iso;
            }

        } catch (error) {
            console.error('[POLLING] Error:', error);
            this.onError(error);
        }

        // Schedule next poll
        if (this.isConnected) {
            this.pollingTimer = setTimeout(() => this.poll(), this.options.pollingInterval);
        }
    }

    buildPollUrl() {
        const baseUrl = `/chat/${this.chatId}/messages`;
        const params = new URLSearchParams();
        
        if (this.lastMessageTime) {
            params.append('after', this.lastMessageTime);
        }
        
        return params.toString() ? `${baseUrl}?${params.toString()}` : baseUrl;
    }

    handleNewMessage(message) {
        // Prevent duplicate messages
        if (this.messageIds.has(message.id)) {
            return;
        }
        // Ignore messages from other chats (safety for admin multi-chat context)
        if (message.chat_id && message.chat_id !== this.chatId) {
            console.warn('[CHAT] Ignored message for different chat:', message.chat_id, 'current:', this.chatId);
            return;
        }
        
        this.messageIds.add(message.id);
        this.onNewMessage(message, true); // true = auto scroll for new messages
        
        // Update last message time for polling fallback
        if (message.created_at_iso) {
            this.lastMessageTime = message.created_at_iso;
        }
    }

    handleTypingEvent(event) {
        // Handle typing indicators
        if (this.options.onTyping) {
            this.options.onTyping(event);
        }
    }

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
            
            // If using polling, add message immediately
            if (!this.pusherChannel && result.ok && result.message) {
                this.handleNewMessage(result.message);
            }

            return result;

        } catch (error) {
            this.onError(error);
            throw error;
        }
    }

    async sendTyping(isTyping) {
        try {
            await fetch(`/chat/${this.chatId}/typing`, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                },
                body: JSON.stringify({ typing: isTyping }),
                credentials: 'same-origin'
            });
        } catch (error) {
            console.warn('[CHAT] Typing indicator failed:', error);
        }
    }

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
            
            // Add all messages in correct order (oldest first)
            messages.forEach(message => {
                this.messageIds.add(message.id);
                this.onNewMessage(message, false); // false = don't scroll for initial load
            });

            // Set last message time
            if (messages.length > 0) {
                const lastMessage = messages[messages.length - 1];
                this.lastMessageTime = lastMessage.created_at_iso;
                // Track first message time for loading older
                const firstMessage = messages[0];
                this.firstMessageTime = firstMessage.created_at_iso || firstMessage.created_at;
            }

            // Force scroll to bottom after a short delay to ensure DOM is updated
            setTimeout(() => {
                this.scrollToBottom();
                this.initialized = true;
            }, 100);

        } catch (error) {
            this.onError(error);
        }
    }

    scrollToBottom() {
        this.messagesContainer.scrollTop = this.messagesContainer.scrollHeight;
    }

    // Load older messages (for infinite scroll up)
    async loadOlderMessages() {
        if (!this.initialized) return; // avoid firing during initial mount
        if (this.loadingOlder) return;
        if (!this.firstMessageTime) return;
        if (this.noMoreOlder) return;
        this.loadingOlder = true;
        try {
            const url = `/chat/${this.chatId}/messages?before=${encodeURIComponent(this.firstMessageTime)}`;
            const prevScrollHeight = this.messagesContainer.scrollHeight;
            const response = await fetch(url, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                },
                credentials: 'same-origin'
            });
            if (!response.ok) throw new Error(`HTTP ${response.status}`);
            const older = await response.json();
            if (older.length === 0) {
                // No more
                this.noMoreOlder = true;
                return;
            }
            // Prepend older messages (keep order ascending)
            const fragment = document.createDocumentFragment();
            older.forEach(msg => {
                if (this.messageIds.has(msg.id)) return; // safety
                this.messageIds.add(msg.id);
                const tempContainer = document.createElement('div');
                // Render without scrolling
                this.onNewMessage(msg, false);
                // last appended element is the one we need to move; but onNewMessage directly appended to messagesContainer
                // To keep control, we will instead manually create element by calling a provided formatter is not available here.
                // As a workaround, we will move the last child into fragment.
                const lastEl = this.messagesContainer.lastElementChild;
                if (lastEl) fragment.appendChild(lastEl);
            });
            // Insert at top
            this.messagesContainer.insertBefore(fragment, this.messagesContainer.firstChild);
            // Update firstMessageTime
            const first = older[0];
            this.firstMessageTime = first.created_at_iso || first.created_at;
            // Maintain scroll position
            const newScrollHeight = this.messagesContainer.scrollHeight;
            this.messagesContainer.scrollTop = newScrollHeight - prevScrollHeight;
        } catch (e) {
            console.error('[CHAT] loadOlderMessages error:', e);
        } finally {
            this.loadingOlder = false;
        }
    }

    disconnect() {
        this.isConnected = false;
        
        if (this.pollingTimer) {
            clearTimeout(this.pollingTimer);
            this.pollingTimer = null;
        }
        
        if (this.pusherChannel) {
            // Leave both common naming variants to be safe
            try { window.Echo.leave(`chat.${this.chatId}`); } catch(e) {}
            try { window.Echo.leave(`private-chat.${this.chatId}`); } catch(e) {}
            this.pusherChannel = null;
        }
        
        console.log('[CHAT] Disconnected');
    }

    // Get connection status
    getConnectionStatus() {
        if (this.pusherChannel && window.Echo.connector.pusher.connection.state === 'connected') {
            return { connected: true, type: 'pusher' };
        } else if (this.isConnected) {
            return { connected: true, type: 'polling' };
        } else {
            return { connected: false, type: 'none' };
        }
    }
}

// Export for global use
window.ChatRealtime = ChatRealtime;
