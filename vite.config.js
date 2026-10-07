import { defineConfig } from 'vite';
import inertia from '@inertiajs/vite';
import laravel from 'laravel-vite-plugin';
import react from '@vitejs/plugin-react';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/js/app.tsx', 'resources/css/filament/admin/theme.css'],
            refresh: true,
        }),
        inertia(),
        react(),
        tailwindcss(),
    ],
});
