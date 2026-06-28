import ApplicationLogo from '@/breeze/ApplicationLogo';
import { Link } from '@inertiajs/react';

export default function LandingLayout({ children }: { children: React.ReactNode }) {
    return (
        <div className="min-h-screen bg-background">
            <header className="sticky top-0 z-50 border-b bg-background/95 backdrop-blur">
                <div className="mx-auto flex h-14 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
                    <Link href={route('landing')} className="flex items-center gap-2">
                        <ApplicationLogo className="h-7 w-7" />
                        <div className="flex flex-col">
                            <span className="text-sm font-semibold leading-tight">Pedales</span>
                            <span className="text-[10px] leading-tight text-muted-foreground">FANTASY CYCLING</span>
                        </div>
                    </Link>
                    <nav className="flex items-center gap-4">
                        <Link
                            href={route('login')}
                            className="text-sm text-muted-foreground hover:text-foreground transition-colors"
                        >
                            Iniciar sesión
                        </Link>
                        <Link
                            href={route('register')}
                            className="inline-flex h-9 items-center justify-center rounded-md bg-accent-500 px-4 py-2 text-sm font-medium text-white shadow hover:bg-accent-600 transition-colors"
                        >
                            Registrarse
                        </Link>
                    </nav>
                </div>
            </header>

            <main>{children}</main>

            <footer className="border-t bg-muted/50">
                <div className="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
                    <div className="flex items-center gap-2">
                        <ApplicationLogo className="h-5 w-5" />
                        <span className="text-xs text-muted-foreground">Pedales Fantasy Cycling</span>
                    </div>
                    <p className="text-xs text-muted-foreground">
                        &copy; {new Date().getFullYear()} Pedales. Todos los derechos reservados.
                    </p>
                </div>
            </footer>
        </div>
    );
}
