import AdminLayout from '@/Layouts/AdminLayout';
import { Head, Link } from '@inertiajs/react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Plus, Edit } from 'lucide-react';

interface Competition {
    id: string;
    name: string;
    type: string;
    country: string;
    active: boolean;
    editions_count: number;
}

export default function Index({ competitions }: { competitions: Competition[] }) {
    return (
        <AdminLayout>
            <Head title="Competiciones" />

            <div className="space-y-6">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl font-semibold tracking-tight">Competiciones</h1>
                        <p className="text-sm text-muted-foreground">Gestiona las competiciones del sistema</p>
                    </div>
                    <Button asChild>
                        <Link href={route('admin.competitions.create')}>
                            <Plus className="mr-2 h-4 w-4" />
                            Nueva
                        </Link>
                    </Button>
                </div>

                <Card>
                    <CardContent className="p-0">
                        {competitions.length === 0 ? (
                            <div className="flex flex-col items-center justify-center py-12 text-center">
                                <p className="text-sm text-muted-foreground">No hay competiciones</p>
                            </div>
                        ) : (
                            <div className="divide-y">
                                {competitions.map((competition) => (
                                    <div key={competition.id} className="flex items-center justify-between p-4">
                                        <div className="flex items-center gap-3">
                                            <div>
                                                <p className="font-medium">{competition.name}</p>
                                                <p className="text-sm text-muted-foreground">
                                                    {competition.type} · {competition.country} · {competition.editions_count} ediciones
                                                </p>
                                            </div>
                                        </div>
                                        <div className="flex items-center gap-2">
                                            <Badge variant={competition.active ? 'default' : 'secondary'}>
                                                {competition.active ? 'Activa' : 'Inactiva'}
                                            </Badge>
                                            <Button variant="ghost" size="sm" asChild>
                                                <Link href={route('admin.competitions.edit', competition.id)}>
                                                    <Edit className="h-4 w-4" />
                                                </Link>
                                            </Button>
                                            <Button variant="outline" size="sm" asChild>
                                                <Link href={route('admin.competitions.editions.index', competition.id)}>
                                                    Ediciones
                                                </Link>
                                            </Button>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        )}
                    </CardContent>
                </Card>
            </div>
        </AdminLayout>
    );
}
