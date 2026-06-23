import AdminLayout from '@/Layouts/AdminLayout';
import { Head, Link } from '@inertiajs/react';
import { Card, CardContent } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Plus, Edit, ArrowLeft } from 'lucide-react';

interface Edition {
    id: string;
    year: number;
    start_date: string;
    end_date: string;
    status: string;
}

export default function Index({ competition, editions }: { competition: { id: string; name: string }; editions: Edition[] }) {
    return (
        <AdminLayout>
            <Head title={`Ediciones — ${competition.name}`} />

            <div className="space-y-6">
                <div className="flex items-center justify-between">
                    <div className="flex items-center gap-4">
                        <Button variant="ghost" size="icon" asChild>
                            <Link href={route('admin.competitions.index')}>
                                <ArrowLeft className="h-4 w-4" />
                            </Link>
                        </Button>
                        <div>
                            <h1 className="text-2xl font-semibold tracking-tight">{competition.name}</h1>
                            <p className="text-sm text-muted-foreground">Ediciones</p>
                        </div>
                    </div>
                    <Button asChild>
                        <Link href={route('admin.competitions.editions.create', competition.id)}>
                            <Plus className="mr-2 h-4 w-4" />
                            Nueva edición
                        </Link>
                    </Button>
                </div>

                <Card>
                    <CardContent className="p-0">
                        {editions.length === 0 ? (
                            <div className="flex flex-col items-center justify-center py-12 text-center">
                                <p className="text-sm text-muted-foreground">No hay ediciones</p>
                            </div>
                        ) : (
                            <div className="divide-y">
                                {editions.map((edition) => (
                                    <div key={edition.id} className="flex items-center justify-between p-4">
                                        <div>
                                            <p className="font-medium">{edition.year}</p>
                                            <p className="text-sm text-muted-foreground">
                                                {edition.start_date} → {edition.end_date}
                                            </p>
                                        </div>
                                        <div className="flex items-center gap-2">
                                            <Badge variant="secondary">{edition.status}</Badge>
                                            <Button variant="ghost" size="sm" asChild>
                                                <Link href={route('admin.competitions.editions.edit', [competition.id, edition.id])}>
                                                    <Edit className="h-4 w-4" />
                                                </Link>
                                            </Button>
                                            <Button variant="outline" size="sm" asChild>
                                                <Link href={route('admin.editions.stages.index', edition.id)}>
                                                    Etapas
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
