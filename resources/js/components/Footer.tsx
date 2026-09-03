import ApplicationLogo from '@/breeze/ApplicationLogo';
import { Link } from '@inertiajs/react';

export default function Footer() {
    return (
        <footer className="border-t bg-muted/50">
            <div className="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
                <div className="flex flex-col items-center gap-6 text-center">
                    <div className="flex items-center gap-2">
                        <ApplicationLogo className="h-6 w-6" />
                        <span className="font-semibold">Pedales Fantasy Cycling</span>
                    </div>

                    <p className="max-w-md text-sm text-muted-foreground">
                        El fantasy de ciclismo para Grandes Vueltas y carreras del World Tour.
                    </p>

                    <div className="flex flex-wrap items-center justify-center gap-4 text-sm">
                        <Link href={route('season-classification')} className="text-muted-foreground hover:text-foreground transition-colors">
                            Clasificación temporada
                        </Link>
                        <Link href={route('about')} className="text-muted-foreground hover:text-foreground transition-colors">
                            Sobre nosotros
                        </Link>
                        <Link href={route('contact')} className="text-muted-foreground hover:text-foreground transition-colors">
                            Contacto
                        </Link>
                        <Link href={route('cookie-policy')} className="text-muted-foreground hover:text-foreground transition-colors">
                            Política de cookies
                        </Link>
                    </div>
                </div>
            </div>

            {/* Bottom bar */}
            <div className="border-t">
                <div className="mx-auto flex flex-col items-center gap-1 py-4 text-center sm:h-12 sm:flex-row sm:justify-between sm:px-4 sm:py-0 lg:px-8">
                    <p className="text-xs text-muted-foreground">
                        &copy; {new Date().getFullYear()} Pedales. Todos los derechos reservados.
                    </p>
                    <p className="text-xs text-muted-foreground">
                        Hecho con ❤️ por{' '}
                        <a
                            href="https://mmoza.github.io/es"
                            target="_blank"
                            rel="noopener noreferrer"
                            className="underline underline-offset-2 hover:text-foreground transition-colors"
                        >
                            Miguel Ángel Moza Barquilla
                        </a>{' '}
                    </p>
                </div>
            </div>
        </footer>
    );
}
