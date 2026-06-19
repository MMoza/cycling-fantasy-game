import AppLayout from '@/Layouts/AppLayout';
import { Head, Link } from '@inertiajs/react';
import { Card, CardContent } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Plus, Users, Search } from 'lucide-react';

export default function Dashboard() {
    return (
        <AppLayout>
            <Head title="Dashboard" />

            <div className="space-y-6">
                <div>
                    <h1 className="text-2xl font-semibold tracking-tight">Dashboard</h1>
                    <p className="text-sm text-muted-foreground">
                        Bienvenido a PseudoFantasy Cycling
                    </p>
                </div>

                <Card>
                    <CardContent className="flex flex-col items-center justify-center py-16 text-center">
                        <Users className="h-16 w-16 text-muted-foreground" />
                        <h3 className="mt-6 text-xl font-medium">No tienes ligas aún</h3>
                        <p className="mt-2 text-sm text-muted-foreground">
                            Crea una liga o únete a una existente para empezar a competir
                        </p>
                        <div className="mt-8 flex gap-3">
                            <Button asChild>
                                <Link href={route('leagues.create')}>
                                    <Plus className="mr-2 h-4 w-4" />
                                    Crear liga
                                </Link>
                            </Button>
                            <Button variant="outline" asChild>
                                <Link href={route('leagues.index')}>
                                    <Search className="mr-2 h-4 w-4" />
                                    Unirse con código
                                </Link>
                            </Button>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}
