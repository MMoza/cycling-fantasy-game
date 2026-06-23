import ApplicationLogo from '@/breeze/ApplicationLogo';
import { Link, usePage } from '@inertiajs/react';
import { LogOut, LayoutDashboard, Users, Route, Trophy, Shield } from 'lucide-react';

export default function AppLayout({ children }: { children: React.ReactNode }) {
    const { auth, currentLeague } = usePage().props as any;

    const navItems = [
        { href: route('dashboard'), label: 'Dashboard', icon: LayoutDashboard },
        ...(currentLeague
            ? [
                  { href: route('stages.index', currentLeague.id), label: 'Etapa', icon: Route },
                  { href: route('classification.index', currentLeague.id), label: 'Clasificación', icon: Trophy },
              ]
            : []),
        { href: route('leagues.index'), label: 'Ligas', icon: Users },
        ...(auth.user?.is_admin
            ? [{ href: '/admin', label: 'Admin', icon: Shield }]
            : []),
    ];

    return (
        <div className="min-h-screen bg-background pb-16 md:pb-0">
            {/* Desktop header */}
            <header className="sticky top-0 z-50 hidden border-b bg-background/95 backdrop-blur md:block">
                <div className="mx-auto flex h-14 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
                    <div className="flex items-center gap-6">
                        <Link href={route('dashboard')} className="flex items-center gap-2">
                            <ApplicationLogo className="h-7 w-7" />
                            <div className="flex flex-col">
                                <span className="text-sm font-semibold leading-tight">Pedales</span>
                                <span className="text-[10px] leading-tight text-muted-foreground">FANTASY CYCLING</span>
                            </div>
                        </Link>
                        <nav className="flex items-center gap-4">
                            {navItems.map((item) => (
                                <Link
                                    key={item.href}
                                    href={item.href}
                                    className="flex items-center gap-2 text-sm text-muted-foreground hover:text-foreground transition-colors"
                                >
                                    <item.icon className="h-4 w-4" />
                                    {item.label}
                                </Link>
                            ))}
                        </nav>
                    </div>
                    <div className="flex items-center gap-4">
                        <span className="text-sm text-muted-foreground">
                            {auth.user?.name}
                        </span>
                        <Link
                            href={route('logout')}
                            method="post"
                            className="text-sm text-muted-foreground hover:text-foreground"
                        >
                            <LogOut className="h-4 w-4" />
                        </Link>
                    </div>
                </div>
            </header>

            {/* Mobile header */}
            <header className="sticky top-0 z-50 flex items-center justify-between border-b bg-background/95 backdrop-blur px-4 py-3 md:hidden">
                <Link href={route('dashboard')} className="flex items-center gap-2">
                    <ApplicationLogo className="h-6 w-6" />
                    <div className="flex flex-col">
                        <span className="text-sm font-semibold leading-tight">Pedales</span>
                        <span className="text-[9px] leading-tight text-muted-foreground">FANTASY CYCLING</span>
                    </div>
                </Link>
                <div className="flex items-center gap-3">
                    <span className="text-xs text-muted-foreground">
                        {auth.user?.name}
                    </span>
                    <Link href={route('logout')} method="post">
                        <LogOut className="h-4 w-4 text-muted-foreground" />
                    </Link>
                </div>
            </header>

            {/* Mobile bottom nav */}
            <nav className="fixed bottom-0 left-0 right-0 z-50 flex border-t bg-background/95 backdrop-blur md:hidden">
                {navItems.map((item) => (
                    <Link
                        key={item.href}
                        href={item.href}
                        className="flex flex-1 flex-col items-center justify-center gap-1 py-2 text-muted-foreground hover:text-foreground active:text-foreground"
                    >
                        <item.icon className="h-5 w-5" />
                        <span className="text-[10px] font-medium">{item.label}</span>
                    </Link>
                ))}
            </nav>

            <main className="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                {children}
            </main>
        </div>
    );
}
