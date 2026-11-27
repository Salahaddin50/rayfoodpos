<template>
    <button 
        @click="clearCache" 
        class="db-btn py-2 px-4 text-white bg-blue-600 hover:bg-blue-700"
        title="Clear browser cache and reload">
        <i class="lab lab-refresh-line"></i>
        <span>Clear Cache</span>
    </button>
</template>

<script>
export default {
    name: "ClearCacheButton",
    methods: {
        clearCache() {
            // Clear localStorage and sessionStorage
            localStorage.clear();
            sessionStorage.clear();
            
            // Clear service worker cache if exists
            if ('caches' in window) {
                caches.keys().then(names => {
                    names.forEach(name => caches.delete(name));
                });
            }
            
            // Hard reload the page (bypass cache)
            window.location.reload(true);
        }
    }
}
</script>
