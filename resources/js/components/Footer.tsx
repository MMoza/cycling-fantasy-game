import ApplicationLogo from '@/breeze/ApplicationLogo';
import { Link } from '@inertiajs/react';

const grandTours = [
    'Tour de Francia',
    'Giro d\'Italia',
    'Vuelta a España',
];

const majorTours = [
    'Paris-Nice',
    'Tirreno-Adriatico',
    'Volta a Catalunya',
    'Itzulia Basque Country',
    'Tour de Romandie',
    'Tour de Suisse',
    'Tour Auvergne-Rhône-Alpes',
];

const monuments = [
    'Milano-SanRemo',
    'Ronde van Vlaanderen',
    'Paris-Roubaix',
    'Liège-Bastogne-Liège',
    'Il Lombardia',
];

const championships = [
    'World Championships',
    'European Championships',
];

const topClassics = [
    'Omloop Het Nieuwsblad',
    'Strade Bianche',
    'E3 Classic',
    'Gent-Wevelgem',
    'Dwars door Vlaanderen',
    'Eschborn-Frankfurt',
    'Amstel Gold Race',
    'La Flèche Wallonne',
    'San Sebastian',
    'Bretagne Classic',
    'GP Québec',
    'GP Montréal',
];

export default function Footer() {
    return (
        <footer className="border-t bg-muted/50">
            <div className="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
                <div className="grid grid-cols-2 gap-8 md:grid-cols-4">
                    {/* Column 1: Pedales */}
                    <div className="col-span-2 md:col-span-1">
                        <div className="flex items-center gap-2">
                            <ApplicationLogo className="h-6 w-6" />
                            <span className="font-semibold">Pedales Fantasy Cycling</span>
                        </div>
                        <p className="mt-3 text-sm text-muted-foreground">
                            El fantasy de ciclismo para Grandes Vueltas y carreras del World Tour.
                        </p>
                        <div className="mt-4 space-y-2 text-sm">
                            <Link href={route('landing')} className="block text-muted-foreground hover:text-foreground transition-colors">
                                Clasificación temporada
                            </Link>
                            <Link href={route('pedales')} className="block text-muted-foreground hover:text-foreground transition-colors">
                                Sobre nosotros
                            </Link>
                            <span className="block text-muted-foreground">
                                Contacto: hola@pedales.app
                            </span>
                            <span className="block text-muted-foreground">
                                Política de cookies
                            </span>
                        </div>
                    </div>

                    {/* Column 2: Grand Tours + Major Tours */}
                    <div>
                        <h3 className="text-sm font-semibold">Grand Tours</h3>
                        <ul className="mt-3 space-y-2 text-sm text-muted-foreground">
                            {grandTours.map((race) => (
                                <li key={race}>{race}</li>
                            ))}
                        </ul>
                        <h3 className="mt-6 text-sm font-semibold">Major Tours</h3>
                        <ul className="mt-3 space-y-2 text-sm text-muted-foreground">
                            {majorTours.map((race) => (
                                <li key={race}>{race}</li>
                            ))}
                        </ul>
                    </div>

                    {/* Column 3: Monuments + Championships */}
                    <div>
                        <h3 className="text-sm font-semibold">Monuments</h3>
                        <ul className="mt-3 space-y-2 text-sm text-muted-foreground">
                            {monuments.map((race) => (
                                <li key={race}>{race}</li>
                            ))}
                        </ul>
                        <h3 className="mt-6 text-sm font-semibold">Championships</h3>
                        <ul className="mt-3 space-y-2 text-sm text-muted-foreground">
                            {championships.map((race) => (
                                <li key={race}>{race}</li>
                            ))}
                        </ul>
                    </div>

                    {/* Column 4: Top Classics */}
                    <div>
                        <h3 className="text-sm font-semibold">Top Classics</h3>
                        <ul className="mt-3 space-y-2 text-sm text-muted-foreground">
                            {topClassics.map((race) => (
                                <li key={race}>{race}</li>
                            ))}
                        </ul>
                    </div>
                </div>
            </div>

            {/* Bottom bar */}
            <div className="border-t">
                <div className="mx-auto flex h-12 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
                    <p className="text-xs text-muted-foreground">
                        &copy; {new Date().getFullYear()} Pedales. Todos los derechos reservados.
                    </p>
                    <p className="text-xs text-muted-foreground">
                        Hecho con ❤️ para los aficionados al ciclismo
                    </p>
                </div>
            </div>
        </footer>
    );
}
