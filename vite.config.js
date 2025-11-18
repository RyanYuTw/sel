import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { createVuePlugin } from 'vite-plugin-vue2';
import { resolve } from 'path';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        createVuePlugin(),
    ],
    optimizeDeps: {
        include: [
            'tinymce/tinymce',
            'tinymce/themes/silver',
            'tinymce/models/dom',
            'tinymce/icons/default'
        ]
    },
    define: {
        global: 'globalThis'
    }
});
