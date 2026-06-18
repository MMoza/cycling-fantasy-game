import ApplicationLogo from '@/breeze/ApplicationLogo';
import { Link, usePage } from '@inertiajs/react';
import { Menu, X, User, LogOut, LayoutDashboard, Users, Trophy } from 'lucide-react';
import { useState } from 'react';

export default function AppLayout({ children }: { children: React.ReactNode }) {
    const { auth } = usePage().props as any;
    const [mobileMenuOpen, setMobileMenuOpen] = useState(false);

    const navItems = [
        { href: route('dashboard'), label: 'Dashboard', icon: LayoutDashboard },
        { href: '#', label: 'Ligas', icon: Users },
        { href: '#', label: 'Clasificación', icon: Trophy },
    ];

    return (
        <div className="min-h-screen bg-background">
            <header className="sticky top-0 z-50 border-b bg-background/95 backdrop-blur">
                <div className="mx-auto flex h-14 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
                    <div className="flex items-center gap-6">
                        <Link href="/" className="flex items-center gap-2">
                            <ApplicationLogo className="h-6 w-6" />
                            <span className="font-semibold">PseudoFantasy</span>
                        </Link>
                        <nav className="hidden md:flex items-center gap-4">
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
                        <span className="hidden text-sm text-muted-foreground sm:inline">
                            {auth.user?.name}
                        </span>
                        <Link
                            href={route('logout')}
                            method="post"
                            className="hidden text-sm text-muted-foreground hover:text-foreground sm:inline"
                        >
                            <LogOut className="h-4 w-4" />
                        </Link>
                        <button
                            onClick={() => setMobileMenuOpen(!mobileMenuOpen)}
                            className="md:hidden p-2"
                        >
                            {mobileMenuOpen ? (
                                <X className="h-5 w-5" />
                            ) : (
                                <Menu className="h-5 w-5" />
                            )}
                        </button>
                    </div>
                </div>
                {mobileMenuOpen && (
                    <div className="border-t md:hidden">
                        <nav className="flex flex-col p-4 gap-2">
                            {navItems.map((item) => (
                                <Link
                                    key={item.href}
                                    href={item.href}
                                    onClick={() => setMobileMenuOpen(false)}
                                    className="flex items-center gap-2 text-sm text-muted-foreground hover:text-foreground transition-colors py-2"
                                >
                                    <item.icon className="h-4 w-4" />
                                    {item.label}
                                </Link>
                            ))}
                            <Link
                                href={route('logout')}
                                method="post"
                                className="flex items-center gap-2 text-sm text-muted-foreground hover:text-foreground transition-colors py-2"
                            >
                                <LogOut className="h-4 w-4" />
                                Cerrar sesión
                            </Link>
                        </nav>
                    </div>
                )}
            </header>
            <main className="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                {children}
            </main>
        </div>
    );
}
