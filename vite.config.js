
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue'

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        vue(),
    ],
    resolve: {
        extensions: ['.js', '.vue', '.json']
    },
    optimizeDeps: {
        include: ["quill"],
        exclude: ["swiper/vue", "swiper/types"]
    },
    build: {
        cssCodeSplit: true,
        sourcemap: false,
        minify: 'esbuild',
        chunkSizeWarningLimit: 1200,
        rollupOptions: {
            output: {
                manualChunks(id) {
                    if (!id.includes('node_modules')) return;
                    if (id.includes('vue') || id.includes('vuex') || id.includes('vue-router')) {
                        return 'vendor-vue';
                    }
                    if (id.includes('axios') || id.includes('lodash') || id.includes('date-fns')) {
                        return 'vendor-utils';
                    }
                    if (
                        id.includes('vue-toastification') ||
                        id.includes('vue3-simple-alert') ||
                        id.includes('sweetalert2') ||
                        id.includes('vue-next-select') ||
                        id.includes('@vuepic')
                    ) {
                        return 'vendor-ui';
                    }
                    return 'vendor';
                }
            }
        }
    },
});

