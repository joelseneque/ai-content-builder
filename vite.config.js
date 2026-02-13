import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';

export default defineConfig({
    plugins: [
        laravel({
            hotFile: 'vite.hot',
            publicDirectory: 'resources/dist',
            input: [
                'resources/js/addon.js',
            ],
        }),
        vue(),
    ],
});
