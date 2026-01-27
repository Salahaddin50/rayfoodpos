const ENV = {
    API_URL: import.meta.env.VITE_HOST,
    API_KEY: import.meta.env.VITE_API_KEY,
    GOOGLE_MAP_KEY: import.meta.env.VITE_GOOGLE_MAP_KEY,
    DEMO: import.meta.env.VITE_DEMO,
    TURNSTILE_ENABLED: import.meta.env.VITE_TURNSTILE_ENABLED,
    TURNSTILE_SITE_KEY: import.meta.env.VITE_TURNSTILE_SITE_KEY,
};
export default ENV;
