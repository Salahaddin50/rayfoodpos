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
        chunkSizeWarningLimit: 1600,
        // Let Vite/Rollup create async chunks from dynamic import() only.
        // Custom manualChunks was splitting Vue ecosystem packages incorrectly
        // and caused "X is not a constructor" at runtime.
    },
});
