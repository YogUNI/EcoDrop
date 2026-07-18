import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

// Pusher/Reverb debug — matikan di production
Pusher.logToConsole = false;

window.Echo = new Echo({
    broadcaster        : 'reverb',
    key                : import.meta.env.VITE_REVERB_APP_KEY,
    wsHost             : import.meta.env.VITE_REVERB_HOST,
    wsPort             : import.meta.env.VITE_REVERB_PORT   ?? 8080,
    wssPort            : import.meta.env.VITE_REVERB_PORT   ?? 443,
    forceTLS           : (import.meta.env.VITE_REVERB_SCHEME ?? 'http') === 'https',
    enabledTransports  : ['ws', 'wss'],
    disableStats       : true,
    activityTimeout    : 30000,
    pongTimeout        : 10000,
});

// Expose connection state globally so views can react
window.Echo.connector.pusher.connection.bind('connected', () => {
    window.__echoConnected = true;
    document.dispatchEvent(new CustomEvent('echo:connected'));
});
window.Echo.connector.pusher.connection.bind('disconnected', () => {
    window.__echoConnected = false;
    document.dispatchEvent(new CustomEvent('echo:disconnected'));
});
window.Echo.connector.pusher.connection.bind('error', (err) => {
    console.warn('[Echo] WebSocket error:', err);
    window.__echoConnected = false;
    document.dispatchEvent(new CustomEvent('echo:disconnected'));
});
