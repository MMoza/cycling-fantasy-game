import { defineConfig, loadEnv } from 'vite';
import laravel from 'laravel-vite-plugin';
import react from '@vitejs/plugin-react';
import { sentryVitePlugin } from '@sentry/bundler-plugins/vite';

export default defineConfig(({ mode }) => {
    const env = loadEnv(mode, process.cwd(), '');
    const sentryAuthToken = process.env.SENTRY_AUTH_TOKEN ?? env.SENTRY_AUTH_TOKEN;

    return {
        plugins: [
            laravel({
                input: 'resources/js/app.tsx',
                refresh: true,
            }),
            react(),
            // Sentry al final (orden requerido para que los source maps salgan bien).
            // Solo se activa cuando hay token de build (CI/Railway): los builds locales
            // no suben source maps ni fallan por falta de credenciales.
            ...(sentryAuthToken
                ? [
                      sentryVitePlugin({
                          org: 'pedales-vj',
                          project: 'cycling-fantasy-game-frontend',
                          authToken: sentryAuthToken,
                          sourcemaps: {
                              filesToDeleteAfterUpload: ['./public/build/**/*.map'],
                          },
                      }),
                  ]
                : []),
        ],
        build: {
            cssMinify: 'esbuild',
            // Requerido para subir source maps ("hidden" no añade el comentario al bundle)
            sourcemap: 'hidden',
        },
    };
});
