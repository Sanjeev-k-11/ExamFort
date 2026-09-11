const isLocal = window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1' || window.location.protocol === 'file:' || !window.location.hostname;

window.EXAMFORT_ENV = {
    API_BASE_URL: isLocal ? 'http://localhost:5000' : 'https://examfort-d6q1.onrender.com',
    DEFAULT_EXAM_CODE: 'NAT-2026-EXAM',
    SOCKET_URL: isLocal ? 'ws://localhost:5000' : 'wss://examfort-d6q1.onrender.com',
    APP_VERSION: '2.0.26',
    LOCKDOWN_ENFORCED: true
};
