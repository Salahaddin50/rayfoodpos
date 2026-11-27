// Prefer same-origin so www vs apex never cross-origin the API (localStorage is per-host).
const runtimeAppUrl = (typeof APP_URL !== 'undefined' && APP_URL) ? String(APP_URL).replace(/\/$/, '') : '';
const originUrl = (typeof window !== 'undefined' && window.location?.origin) ? window.location.origin : '';

const ENV = {
    API_URL: originUrl || runtimeAppUrl || import.meta.env.VITE_HOST,
    API_KEY: import.meta.env.VITE_API_KEY,
    GOOGLE_MAP_KEY: import.meta.env.VITE_GOOGLE_MAP_KEY,
    DEMO: import.meta.env.VITE_DEMO,
    TURNSTILE_ENABLED: import.meta.env.VITE_TURNSTILE_ENABLED,
    TURNSTILE_SITE_KEY: import.meta.env.VITE_TURNSTILE_SITE_KEY,
};
export default ENV;
