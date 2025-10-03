import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',  // CSS principal
                'resources/js/app.js' // Remove o app.css daqui
            ],
            refresh: true,
        }),
    ],
});