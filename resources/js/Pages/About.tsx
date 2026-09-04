import LandingLayout from '@/Layouts/LandingLayout';
import { Head } from '@inertiajs/react';

export default function About() {
    return (
        <LandingLayout>
            <Head title="Sobre nosotros — Pedales" />

            <div className="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
                <h1 className="text-2xl font-semibold tracking-tight">Sobre nosotros</h1>
                <p className="mt-2 text-muted-foreground">Conoce más sobre Pedales.</p>
            </div>
        </LandingLayout>
    );
}
