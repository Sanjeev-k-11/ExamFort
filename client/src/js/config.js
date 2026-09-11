const isLocalEnv = typeof window !== 'undefined' && (
    window.location.hostname === 'localhost' ||
    window.location.hostname === '127.0.0.1' ||
    window.location.protocol === 'file:' ||
    !window.location.hostname
);

const LOCAL_BASE = 'http://localhost:5000';
const CLOUD_BASE = 'https://examfort-d6q1.onrender.com';

window.EXAMFORT_ENV = {
    API_BASE_URL: isLocalEnv ? LOCAL_BASE : CLOUD_BASE,
    DEFAULT_EXAM_CODE: 'NAT-2026-EXAM',
    SOCKET_URL: isLocalEnv ? 'ws://localhost:5000' : 'wss://examfort-d6q1.onrender.com',
    APP_VERSION: '2.0.26',
    LOCKDOWN_ENFORCED: true
};
