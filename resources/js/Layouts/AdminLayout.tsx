import { useState } from 'react';
import { Link, usePage } from '@inertiajs/react';
import { LayoutDashboard, Trophy, Users, Bike, Shield, Menu, X, ArrowLeft } from 'lucide-react';
import ApplicationLogo from '@/breeze/ApplicationLogo';
import { cn } from '@/lib/utils';
import UserMenu from '@/components/UserMenu';

export default function AdminLayout({ children }: { children: React.ReactNode }) {
    const page = usePage();
    const { auth } = page.props as any;
    const url = page.url;
    const [sidebarOpen, setSidebarOpen] = useState(false);

    const sidebarItems = [
        { href: '/admin', label: 'Dashboard', icon: LayoutDashboard },
        { href: '/admin/competitions', label: 'Competiciones', icon: Trophy },
        { href: '/admin/teams', label: 'Equipos', icon: Users },
        { href: '/admin/riders', label: 'Corredores', icon: Bike },
        { href: '/admin/users', label: 'Usuarios', icon: Shield },
    ];

    const topNavItems = [
        { route: 'dashboard', href: route('dashboard'), label: 'Dashboard', icon: LayoutDashboard },
        { route: 'leagues.index', href: route('leagues.index'), label: 'Ligas', icon: Users },
        { route: 'admin', href: '/admin', label: 'Admin', icon: Shield },
    ];

    const isSidebarActive = (href: string) => {
        if (href === '/admin') return url === '/admin';
        return url.startsWith(href);
    };

    const isTopActive = (item: typeof topNavItems[number]) => {
        if (item.route === 'admin') return url.startsWith('/admin');
        return route().current(item.route);
    };

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
                                <span className="text-[10px] leading-tight text-muted-foreground">PREDICTOR CYCLING</span>
                            </div>
                        </Link>
                        <nav className="flex items-center gap-4">
                            {topNavItems.map((item) => (
                                <Link
                                    key={item.href}
                                    href={item.href}
                                    className={cn(
                                        'flex items-center gap-2 text-sm transition-colors',
                                        isTopActive(item)
                                            ? 'text-accent-500 font-semibold'
                                            : 'text-muted-foreground hover:text-foreground',
                                    )}
                                >
                                    <item.icon className={cn('h-4 w-4', isTopActive(item) && 'text-accent-500')} />
                                    {item.label}
                                </Link>
                            ))}
                        </nav>
                    </div>
                    <UserMenu user={auth.user} leagues={auth.user_leagues ?? []} />
                </div>
            </header>

            {/* Mobile header */}
            <header className="sticky top-0 z-50 flex items-center justify-between border-b bg-background/95 backdrop-blur px-4 py-3 md:hidden">
                <div className="flex items-center gap-3">
                    <button
                        type="button"
                        onClick={() => setSidebarOpen(true)}
                        className="rounded-lg p-1.5 text-muted-foreground transition-colors hover:bg-accent hover:text-accent-foreground"
                    >
                        <Menu className="h-5 w-5" />
                    </button>
                    <Link href={route('dashboard')} className="flex items-center gap-2">
                        <ApplicationLogo className="h-6 w-6" />
                        <div className="flex flex-col">
                            <span className="text-sm font-semibold leading-tight">Pedales</span>
                            <span className="text-[9px] leading-tight text-muted-foreground">PREDICTOR CYCLING</span>
                        </div>
                    </Link>
                </div>
                <div className="flex items-center gap-3">
                    <UserMenu user={auth.user} leagues={auth.user_leagues ?? []} />
                </div>
            </header>

            {/* Mobile sidebar overlay */}
            {sidebarOpen && (
                <div
                    className="fixed inset-0 z-40 bg-black/50 md:hidden"
                    onClick={() => setSidebarOpen(false)}
                />
            )}

            {/* Mobile bottom nav */}
            <nav className="fixed bottom-0 left-0 right-0 z-30 flex border-t bg-background/95 backdrop-blur md:hidden">
                {topNavItems.map((item) => (
                    <Link
                        key={item.href}
                        href={item.href}
                        className={cn(
                            'flex flex-1 flex-col items-center justify-center gap-1 py-2 transition-colors',
                            isTopActive(item)
                                ? 'text-accent-500'
                                : 'text-muted-foreground hover:text-foreground active:text-foreground',
                        )}
                    >
                        <item.icon className="h-5 w-5" />
                        <span className="text-[10px] font-medium">{item.label}</span>
                    </Link>
                ))}
            </nav>

            {/* Desktop layout: sidebar + content */}
            <div className="md:flex">
                {/* Sidebar */}
                <aside
                    className={cn(
                        'fixed left-0 top-0 z-50 flex h-full w-64 flex-col border-r bg-background transition-transform',
                        'md:relative md:sticky md:top-14 md:z-0 md:h-[calc(100vh-3.5rem)] md:translate-x-0 md:shrink-0',
                        sidebarOpen ? 'translate-x-0' : '-translate-x-full',
                    )}
                >
                    {/* Sidebar header (mobile only) */}
                    <div className="flex h-14 items-center justify-between border-b px-4 md:hidden">
                        <Link href="/admin" className="flex items-center gap-2 font-semibold">
                            <Bike className="h-4 w-4 text-brand-600" />
                            Admin
                        </Link>
                        <button
                            type="button"
                            onClick={() => setSidebarOpen(false)}
                            className="rounded-lg p-1.5 text-muted-foreground transition-colors hover:bg-accent hover:text-accent-foreground"
                        >
                            <X className="h-5 w-5" />
                        </button>
                    </div>

                    {/* Sidebar header (desktop) */}
                    <div className="hidden h-14 items-center gap-2 border-b px-4 md:flex">
                        <Link href="/admin" className="flex items-center gap-2 font-semibold">
                            <Bike className="h-4 w-4 text-brand-600" />
                            Admin
                        </Link>
                    </div>

                    <nav className="flex-1 space-y-1 overflow-y-auto p-3">
                        {sidebarItems.map((item) => (
                            <Link
                                key={item.href}
                                href={item.href}
                                onClick={() => setSidebarOpen(false)}
                                className={cn(
                                    'flex items-center gap-2 rounded-lg px-3 py-2 text-sm transition-colors',
                                    isSidebarActive(item.href)
                                        ? 'bg-brand-600 text-white'
                                        : 'text-muted-foreground hover:bg-muted hover:text-foreground',
                                )}
                            >
                                <item.icon className="h-4 w-4" />
                                {item.label}
                            </Link>
                        ))}
                        <div className="pt-4">
                            <Link
                                href="/dashboard"
                                onClick={() => setSidebarOpen(false)}
                                className="flex items-center gap-2 rounded-lg px-3 py-2 text-sm text-muted-foreground transition-colors hover:bg-muted hover:text-foreground"
                            >
                                <ArrowLeft className="h-4 w-4" />
                                Volver
                            </Link>
                        </div>
                    </nav>
                </aside>

                <main className="flex-1 min-w-0">
                    <div className="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                        {children}
                    </div>
                </main>
            </div>
        </div>
    );
}
