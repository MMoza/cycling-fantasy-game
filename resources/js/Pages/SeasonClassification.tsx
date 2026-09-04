import LandingLayout from '@/Layouts/LandingLayout';
import { Head } from '@inertiajs/react';

interface Props {
    year: number;
}

export default function SeasonClassification({ year }: Props) {
    return (
        <LandingLayout>
            <Head title={`Temporada ${year} — Pedales`} />

            <div className="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
                <h1 className="text-2xl font-semibold tracking-tight">Temporada {year}</h1>
                <p className="mt-2 text-muted-foreground">Clasificación general de la temporada.</p>
            </div>
        </LandingLayout>
    );
}
