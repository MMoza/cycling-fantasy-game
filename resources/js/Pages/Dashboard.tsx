import AppLayout from '@/Layouts/AppLayout';
import { Head, Link } from '@inertiajs/react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Plus, Users, Trophy, Calendar, ChevronRight } from 'lucide-react';

export default function Dashboard() {
    return (
        <AppLayout>
            <Head title="Dashboard" />

            <div className="space-y-6">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl font-semibold tracking-tight">Dashboard</h1>
                        <p className="text-sm text-muted-foreground">
                            Bienvenido a PseudoFantasy Cycling
                        </p>
                    </div>
                    <Button asChild>
                        <Link href="#">
                            <Plus className="mr-2 h-4 w-4" />
                            Crear liga
                        </Link>
                    </Button>
                </div>

                <div className="grid gap-4 md:grid-cols-3">
                    <Card className="text-center">
                        <CardContent className="flex flex-col items-center justify-center p-6">
                            <Users className="mb-2 h-5 w-5 text-muted-foreground" />
                            <div className="text-3xl font-bold">0</div>
                            <p className="mt-1 text-sm text-muted-foreground">
                                Ligas activas
                            </p>
                        </CardContent>
                    </Card>
                    <Card className="text-center">
                        <CardContent className="flex flex-col items-center justify-center p-6">
                            <Calendar className="mb-2 h-5 w-5 text-muted-foreground" />
                            <div className="text-3xl font-bold">-</div>
                            <p className="mt-1 text-sm text-muted-foreground">
                                Tour de Francia 2026
                            </p>
                        </CardContent>
                    </Card>
                    <Card className="text-center">
                        <CardContent className="flex flex-col items-center justify-center p-6">
                            <Trophy className="mb-2 h-5 w-5 text-muted-foreground" />
                            <div className="text-3xl font-bold">-</div>
                            <p className="mt-1 text-sm text-muted-foreground">
                                Clasificación general
                            </p>
                        </CardContent>
                    </Card>
                </div>

                <Card>
                    <CardHeader className="pb-3">
                        <CardTitle>Tus ligas</CardTitle>
                        <CardDescription>
                            Aún no te has unido a ninguna liga
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div className="flex flex-col items-center justify-center py-16 text-center">
                            <Users className="h-12 w-12 text-muted-foreground" />
                            <h3 className="mt-4 text-lg font-medium">No hay ligas aún</h3>
                            <p className="mt-2 text-sm text-muted-foreground">
                                Crea una liga o únete a una existente para empezar a competir
                            </p>
                            <div className="mt-6 flex gap-3">
                                <Button asChild>
                                    <Link href="#">
                                        <Plus className="mr-2 h-4 w-4" />
                                        Crear liga
                                    </Link>
                                </Button>
                                <Button variant="outline" asChild>
                                    <Link href="#">
                                        Unirse con código
                                    </Link>
                                </Button>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader className="pb-3">
                        <CardTitle>Próximas etapas</CardTitle>
                        <CardDescription>
                            Tour de Francia 2026
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div className="space-y-3">
                            {[
                                { number: 1, name: 'Lille → Paris', date: '1 Jul', type: 'Llano', distance: '180 km' },
                                { number: 2, name: 'Lyon → Grenoble', date: '2 Jul', type: 'Montaña', distance: '165 km' },
                                { number: 3, name: 'Bordeaux (CRI)', date: '3 Jul', type: 'Contrarreloj', distance: '35 km' },
                            ].map((stage) => (
                                <div
                                    key={stage.number}
                                    className="flex items-center justify-between rounded-lg border p-4"
                                >
                                    <div className="flex items-center gap-4">
                                        <Badge variant="secondary" className="flex h-8 w-8 items-center justify-center rounded-full p-0">
                                            {stage.number}
                                        </Badge>
                                        <div>
                                            <p className="font-medium">{stage.name}</p>
                                            <p className="text-sm text-muted-foreground">
                                                {stage.type} · {stage.distance}
                                            </p>
                                        </div>
                                    </div>
                                    <div className="flex items-center gap-3">
                                        <span className="text-sm text-muted-foreground">{stage.date}</span>
                                        <ChevronRight className="h-4 w-4 text-muted-foreground" />
                                    </div>
                                </div>
                            ))}
                        </div>
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}
