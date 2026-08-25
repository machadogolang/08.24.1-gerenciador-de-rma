import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { bunny } from 'laravel-vite-plugin/fonts';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                // Fase 8 — bundles próprios por tema (Vite 8, compilação Sass nativa,
                // não precisa de plugin extra além de `sass` como devDependency).
                'resources/js/temas/v1.js',
                'resources/js/temas/v2.js',
                // Gateway de login compartilhado (correção Fase 8, 2026-08-25) — não
                // pertence a nenhum tema, ver resources/sass/identidade/login.scss.
                'resources/js/identidade/login.js',
            ],
            refresh: true,
            fonts: [
                bunny('Instrument Sans', {
                    weights: [400, 500, 600],
                }),
            ],
        }),
        tailwindcss(),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
