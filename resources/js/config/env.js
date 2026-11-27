// Prefer runtime APP_URL from Blade, then current origin (www/apex safe), then Vite env.
const runtimeAppUrl = (typeof APP_URL !== 'undefined' && APP_URL) ? String(APP_URL).replace(/\/$/, '') : '';
const originUrl = (typeof window !== 'undefined' && window.location?.origin) ? window.location.origin : '';

const ENV = {
    API_URL: runtimeAppUrl || originUrl || import.meta.env.VITE_HOST,
    API_KEY: import.meta.env.VITE_API_KEY,
    GOOGLE_MAP_KEY: import.meta.env.VITE_GOOGLE_MAP_KEY,
    DEMO: import.meta.env.VITE_DEMO,
    TURNSTILE_ENABLED: import.meta.env.VITE_TURNSTILE_ENABLED,
    TURNSTILE_SITE_KEY: import.meta.env.VITE_TURNSTILE_SITE_KEY,
};
export default ENV;
