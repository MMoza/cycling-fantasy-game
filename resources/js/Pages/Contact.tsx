import LandingLayout from '@/Layouts/LandingLayout';
import { Head } from '@inertiajs/react';

export default function Contact() {
    return (
        <LandingLayout>
            <Head title="Contacto — Pedales" />

            <div className="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
                <h1 className="text-2xl font-semibold tracking-tight">Contacto</h1>
                <p className="mt-2 text-muted-foreground">Ponte en contacto con nosotros.</p>
            </div>
        </LandingLayout>
    );
}
