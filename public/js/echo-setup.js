/**
 * Laravel Echo Setup with Pusher
 * Real-time WebSocket connection for live chat
 */

// Import Pusher from CDN (loaded in HTML)
window.Pusher = Pusher;

// Get Pusher config from Laravel (passed via meta tags)
const pusherConfig = {
    key: document.querySelector('meta[name="pusher-key"]')?.getAttribute('content'),
    cluster: document.querySelector('meta[name="pusher-cluster"]')?.getAttribute('content') || 'mt1',
    scheme: document.querySelector('meta[name="pusher-scheme"]')?.getAttribute('content') || 'https'
};

console.log('[ECHO] Pusher config:', pusherConfig);

// Configure Laravel Echo (Pusher SaaS defaults)
window.Echo = new Echo({
    broadcaster: 'pusher',
    key: pusherConfig.key,
    cluster: pusherConfig.cluster,
    forceTLS: pusherConfig.scheme === 'https',
    // Auth endpoint for private channels
    authEndpoint: '/broadcasting/auth',
    // CSRF token for authentication
    auth: {
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            'Accept': 'application/json'
        }
    }
});

// Connection event listeners
window.Echo.connector.pusher.connection.bind('connected', function() {
    console.log('[ECHO] Connected to Pusher');
});

window.Echo.connector.pusher.connection.bind('disconnected', function() {
    console.log('[ECHO] Disconnected from Pusher');
});

window.Echo.connector.pusher.connection.bind('error', function(err) {
    console.error('[ECHO] Connection error:', err);
});

// Global error handler
window.Echo.connector.pusher.bind('pusher:error', function(err) {
    console.error('[ECHO] Pusher error:', err);
});

console.log('[ECHO] Laravel Echo initialized with Pusher');
