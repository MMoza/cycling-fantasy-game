import PedalesLogo from '@/components/PedalesLogo';
import { Link, usePage } from '@inertiajs/react';
import { LayoutDashboard, Route, Trophy, Shield, Bell, BellOff, Calendar } from 'lucide-react';
import { cn } from '@/lib/utils';
import UserMenu from '@/components/UserMenu';
import { usePushNotifications } from '@/hooks/usePushNotifications';

export default function AppLayout({ children }: { children: React.ReactNode }) {
    const page = usePage();
    const { auth, currentLeague } = page.props as any;
    const pageProps = page.props as Record<string, any>;
    const url = page.url;

    const activeLeagueId = pageProps.league_id ?? pageProps.leagueId ?? currentLeague?.id;
    const activeLeague = activeLeagueId ? { id: activeLeagueId } : currentLeague;

    const { isSupported, isSubscribed, subscribe, unsubscribe, loading } = usePushNotifications();

    const navItems = [
        { route: 'dashboard', href: route('dashboard'), label: 'Dashboard', icon: LayoutDashboard },
        { route: 'season.index', href: route('season.index'), label: 'Temporada', icon: Calendar },
        ...(activeLeague
            ? [
                  { route: 'stages.index', href: route('stages.index', activeLeague.id), label: 'Etapa', icon: Route },
                  { route: 'classification.index', href: route('classification.index', activeLeague.id), label: 'Clasificación', icon: Trophy },
              ]
            : []),
        ...(auth.user?.is_admin
            ? [{ route: 'admin', href: '/admin', label: 'Admin', icon: Shield }]
            : []),
        // TODO: Descomentar para añadir Pedales al nav
        // { route: 'pedales', href: route('pedales'), label: 'Pedales', icon: PedalesLogo },
    ];

    function isActive(item: typeof navItems[number]): boolean {
        if (item.route === 'admin') {
            return url.startsWith('/admin');
        }
        if (item.route === 'season.index') {
            return route().current('season.*');
        }
        return route().current(item.route);
    }

    return (
        <div className="min-h-screen bg-background pb-16 md:pb-0">
            {/* Background pattern */}
            <div
                className="pointer-events-none fixed inset-0 z-0 opacity-10 dark:opacity-5"
                style={{
                    backgroundImage: 'url(/logo.png)',
                    backgroundSize: '40px 40px',
                    backgroundRepeat: 'repeat',
                }}
            />
            {/* Desktop header */}
            <header className="sticky top-0 z-50 hidden border-b bg-background/95 backdrop-blur md:block">
                <div className="mx-auto flex h-14 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
                    <div className="flex items-center gap-6">
                        <Link href={route('dashboard')} className="flex items-center gap-2">
                            <PedalesLogo className="h-16 w-16" />
                            <div className="flex flex-col">
                                <span className="text-sm font-semibold leading-tight">Pedales</span>
                                <span className="text-[10px] leading-tight text-muted-foreground">PREDICTOR CYCLING</span>
                            </div>
                        </Link>
                        <nav className="flex items-center gap-4">
                            {navItems.map((item) => (
                                <Link
                                    key={item.href}
                                    href={item.href}
                                    className={cn(
                                        'flex items-center gap-2 text-sm transition-colors',
                                        isActive(item)
                                            ? 'text-accent-500 font-semibold'
                                            : 'text-muted-foreground hover:text-foreground',
                                    )}
                                >
                                    <item.icon className={cn('h-4 w-4', isActive(item) && 'text-accent-500')} />
                                    {item.label}
                                </Link>
                            ))}
                            {isSupported && (
                                <button
                                    type="button"
                                    onClick={isSubscribed ? unsubscribe : subscribe}
                                    disabled={loading}
                                    className="flex items-center gap-2 text-sm text-muted-foreground transition-colors hover:text-foreground"
                                >
                                    {isSubscribed ? (
                                        <BellOff className="h-4 w-4" />
                                    ) : (
                                        <Bell className="h-4 w-4" />
                                    )}
                                    {loading ? '...' : isSubscribed ? 'Silenciar' : 'Notificar'}
                                </button>
                            )}
                        </nav>
                    </div>
                    <UserMenu user={auth.user} leagues={auth.user_leagues ?? []} />
                </div>
            </header>

            {/* Mobile header */}
            <header className="sticky top-0 z-50 flex items-center justify-between border-b bg-background/95 backdrop-blur px-4 py-3 md:hidden">
                <Link href={route('dashboard')} className="flex items-center gap-2">
                    <PedalesLogo className="h-16 w-16" />
                    <div className="flex flex-col">
                        <span className="text-sm font-semibold leading-tight">Pedales</span>
                        <span className="text-[9px] leading-tight text-muted-foreground">PREDICTOR CYCLING</span>
                    </div>
                </Link>
                <div className="flex items-center gap-3">
                    {isSupported && (
                        <button
                            type="button"
                            onClick={isSubscribed ? unsubscribe : subscribe}
                            disabled={loading}
                            className="flex items-center gap-2 text-sm text-muted-foreground transition-colors hover:text-foreground"
                        >
                            {isSubscribed ? (
                                <BellOff className="h-4 w-4" />
                            ) : (
                                <Bell className="h-4 w-4" />
                            )}
                        </button>
                    )}
                    <UserMenu user={auth.user} leagues={auth.user_leagues ?? []} />
                </div>
            </header>

            {/* Mobile bottom nav */}
            <nav className="fixed bottom-0 left-0 right-0 z-50 flex border-t bg-background/95 backdrop-blur md:hidden">
                {navItems.map((item) => (
                    <Link
                        key={item.href}
                        href={item.href}
                        className={cn(
                            'flex flex-1 flex-col items-center justify-center gap-1 py-2 transition-colors',
                            isActive(item)
                                ? 'text-accent-500'
                                : 'text-muted-foreground hover:text-foreground active:text-foreground',
                        )}
                    >
                        <item.icon className="h-5 w-5" />
                        <span className="text-[10px] font-medium">{item.label}</span>
                    </Link>
                ))}
            </nav>

            <main className="relative z-10 mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                {children}
            </main>
        </div>
    );
}
