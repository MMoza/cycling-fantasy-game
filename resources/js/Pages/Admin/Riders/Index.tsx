import AdminLayout from '@/Layouts/AdminLayout';
import { Head, Link } from '@inertiajs/react';
import { Card, CardContent } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Plus, Edit, Bike } from 'lucide-react';

interface Rider {
    id: string;
    name: string;
    nationality: string | null;
    birth_date: string | null;
}

export default function Index({ riders }: { riders: Rider[] }) {
    return (
        <AdminLayout>
            <Head title="Corredores" />

            <div className="space-y-6">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl font-semibold tracking-tight">Corredores</h1>
                        <p className="text-sm text-muted-foreground">Gestiona los corredores del pelotón</p>
                    </div>
                    <Button asChild>
                        <Link href={route('admin.riders.create')}>
                            <Plus className="mr-2 h-4 w-4" />
                            Nuevo corredor
                        </Link>
                    </Button>
                </div>

                <Card>
                    <CardContent className="p-0">
                        {riders.length === 0 ? (
                            <div className="flex flex-col items-center justify-center py-12 text-center">
                                <Bike className="h-12 w-12 text-muted-foreground" />
                                <p className="mt-4 text-sm text-muted-foreground">No hay corredores</p>
                            </div>
                        ) : (
                            <div className="divide-y">
                                {riders.map((rider) => (
                                    <div key={rider.id} className="flex items-center justify-between p-4">
                                        <div>
                                            <p className="font-medium">{rider.name}</p>
                                            <p className="text-sm text-muted-foreground">
                                                {rider.nationality ?? '—'}
                                                {rider.birth_date ? ` · ${rider.birth_date}` : ''}
                                            </p>
                                        </div>
                                        <Button variant="ghost" size="sm" asChild>
                                            <Link href={route('admin.riders.edit', rider.id)}>
                                                <Edit className="h-4 w-4" />
                                            </Link>
                                        </Button>
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
