import AppLayout from '@/Layouts/AppLayout';
import { Head, Link } from '@inertiajs/react';
import { Card, CardContent } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Plus, Search, Bike } from 'lucide-react';

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

                <Card className="overflow-hidden">
                    <div className="h-1 bg-gradient-to-r from-brand-600 to-accent-500" />
                    <CardContent className="flex flex-col items-center justify-center py-16 text-center">
                        <div className="mb-6 flex h-16 w-16 items-center justify-center rounded-full bg-accent-100 dark:bg-accent-900/20">
                            <Bike className="h-8 w-8 text-accent-500" />
                        </div>
                        <h3 className="text-xl font-medium">No tienes ligas aún</h3>
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
