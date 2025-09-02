import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css', 
                'resources/js/app.js'
            ],
            refresh: true,
        }),
        tailwindcss(),
    ],
    optimizeDeps: {
        esbuildOptions: {
            target: 'es2020'
        }
    },
    build: {
        target: 'es2020',
        rollupOptions: {
            output: {
                manualChunks: {
                    vendor: ['laravel-vite-plugin']
                }
            }
        },
        chunkSizeWarningLimit: 1000
    },
    server: {
        hmr: {
            overlay: false
        },
        host: 'localhost',
        port: 5173,
        strictPort: false
    },
    resolve: {
        alias: {
            '@': '/resources/js'
        }
    }
});
