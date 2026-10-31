import * as Sentry from '@sentry/react';

const dsn = import.meta.env.VITE_SENTRY_DSN as string | undefined;

if (dsn) {
    Sentry.init({
        dsn,
        integrations: [Sentry.browserTracingIntegration()],
        environment: import.meta.env.MODE,

        // Tracing: reduce el valor en producción si el volumen crece.
        tracesSampleRate: Number(import.meta.env.VITE_SENTRY_TRACES_SAMPLE_RATE ?? 1.0),

        // Propaga los headers de traza a las peticiones same-origin (Inertia/API)
        // para enlazar las trazas de frontend con las de backend.
        tracePropagationTargets: [/^\//],
    });
}
