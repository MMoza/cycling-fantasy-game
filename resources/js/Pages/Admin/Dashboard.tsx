import AdminLayout from '@/Layouts/AdminLayout';
import { Head, Link } from '@inertiajs/react';
import { Card, CardContent } from '@/components/ui/card';
import { Trophy, Bike, Users, Layers } from 'lucide-react';

interface Stats {
    competitions_count: number;
    editions_count: number;
    stages_count: number;
    users_count: number;
    active_competitions: number;
}

export default function Dashboard({ stats }: { stats: Stats }) {
    const cards = [
        { label: 'Competiciones', value: stats.competitions_count, icon: Trophy, href: '/admin/competitions', color: 'text-brand-600' },
        { label: 'Ediciones', value: stats.editions_count, icon: Layers, href: '/admin/competitions', color: 'text-accent-500' },
        { label: 'Etapas', value: stats.stages_count, icon: Bike, href: '#', color: 'text-blue-500' },
        { label: 'Usuarios', value: stats.users_count, icon: Users, href: '/admin/users', color: 'text-green-600' },
    ];

    return (
        <AdminLayout>
            <Head title="Admin Dashboard" />

            <div className="space-y-6">
                <h1 className="text-2xl font-semibold tracking-tight">Panel de Administración</h1>

                <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    {cards.map((card) => (
                        <Link key={card.label} href={card.href}>
                            <Card className="transition-colors hover:bg-muted/50">
                                <CardContent className="flex flex-col items-center justify-center p-6">
                                    <card.icon className={`mb-2 h-6 w-6 ${card.color}`} />
                                    <div className="text-2xl font-bold">{card.value}</div>
                                    <p className="text-sm text-muted-foreground">{card.label}</p>
                                </CardContent>
                            </Card>
                        </Link>
                    ))}
                </div>
            </div>
        </AdminLayout>
    );
}
