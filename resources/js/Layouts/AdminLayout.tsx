import { Link, usePage } from '@inertiajs/react';
import { LayoutDashboard, Trophy, Users, Bike, ArrowLeft } from 'lucide-react';

export default function AdminLayout({ children }: { children: React.ReactNode }) {
    const { url } = usePage();

    const navItems = [
        { href: '/admin', label: 'Dashboard', icon: LayoutDashboard },
        { href: '/admin/competitions', label: 'Competiciones', icon: Trophy },
        { href: '/admin/users', label: 'Usuarios', icon: Users },
    ];

    const isActive = (href: string) => {
        if (href === '/admin') return url === '/admin';
        return url.startsWith(href);
    };

    return (
        <div className="min-h-screen bg-background">
            <div className="flex">
                <aside className="hidden w-56 shrink-0 border-r bg-muted/30 md:block">
                    <div className="flex h-14 items-center gap-2 border-b px-4">
                        <Link href="/admin" className="flex items-center gap-2 font-semibold">
                            <Bike className="h-4 w-4 text-brand-600" />
                            Admin
                        </Link>
                    </div>
                    <nav className="space-y-1 p-3">
                        {navItems.map((item) => (
                            <Link
                                key={item.href}
                                href={item.href}
                                className={`flex items-center gap-2 rounded-lg px-3 py-2 text-sm transition-colors ${
                                    isActive(item.href)
                                        ? 'bg-brand-600 text-white'
                                        : 'text-muted-foreground hover:bg-muted hover:text-foreground'
                                }`}
                            >
                                <item.icon className="h-4 w-4" />
                                {item.label}
                            </Link>
                        ))}
                        <div className="pt-4">
                            <Link
                                href="/dashboard"
                                className="flex items-center gap-2 rounded-lg px-3 py-2 text-sm text-muted-foreground hover:bg-muted hover:text-foreground transition-colors"
                            >
                                <ArrowLeft className="h-4 w-4" />
                                Volver
                            </Link>
                        </div>
                    </nav>
                </aside>
                <main className="flex-1">
                    <div className="mx-auto max-w-5xl px-4 py-6 sm:px-6 lg:px-8">
                        {children}
                    </div>
                </main>
            </div>
        </div>
    );
}
