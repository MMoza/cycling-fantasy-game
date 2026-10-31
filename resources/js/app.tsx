import './instrument';

import '../css/app.css';
import './bootstrap';

import * as Sentry from '@sentry/react';
import type { FallbackRender } from '@sentry/react';
import { createInertiaApp } from '@inertiajs/react';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createRoot } from 'react-dom/client';

if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js');
    });
}

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

const errorFallback: FallbackRender = () => (
    <div className="flex min-h-screen items-center justify-center p-4 text-center">
        <div>
            <h1 className="text-lg font-semibold">Algo salió mal</h1>
            <p className="mt-2 text-sm text-muted-foreground">
                Recarga la página para intentar de nuevo.
            </p>
            <button
                onClick={() => window.location.reload()}
                className="mt-4 rounded-md bg-primary px-4 py-2 text-sm text-primary-foreground hover:bg-primary/90"
            >
                Recargar
            </button>
        </div>
    </div>
);

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.tsx`,
            import.meta.glob('./Pages/**/*.tsx'),
        ),
    setup({ el, App, props }) {
        const root = createRoot(el);

        root.render(
            <Sentry.ErrorBoundary fallback={errorFallback}>
                <App {...props} />
            </Sentry.ErrorBoundary>
        );
    },
    progress: {
        color: '#4B5563',
    },
});
