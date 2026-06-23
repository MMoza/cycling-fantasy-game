import AdminLayout from '@/Layouts/AdminLayout';
import { Head, Link } from '@inertiajs/react';
import { Card, CardContent } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Plus, Edit, ArrowLeft, Eye, Users } from 'lucide-react';

interface Stage {
    id: string;
    number: number;
    name: string;
    date: string;
    type: string;
    distance: number | null;
    elevation_gain: number | null;
    origin: string;
    destination: string;
    status: string;
}

export default function Index({ edition, stages }: { edition: { id: string; year: number; competition_id: string; competition: string }; stages: Stage[] }) {
    return (
        <AdminLayout>
            <Head title={`Etapas — ${edition.competition} ${edition.year}`} />

            <div className="space-y-6">
                <div className="flex items-center justify-between">
                    <div className="flex items-center gap-4">
                        <Button variant="ghost" size="icon" asChild>
                            <Link href={route('admin.competitions.editions.index', { competitionId: edition.competition_id })}>
                                <ArrowLeft className="h-4 w-4" />
                            </Link>
                        </Button>
                        <div>
                            <h1 className="text-2xl font-semibold tracking-tight">
                                {edition.competition} {edition.year}
                            </h1>
                            <p className="text-sm text-muted-foreground">Etapas</p>
                        </div>
                    </div>
                    <div className="flex gap-2">
                        <Button variant="outline" asChild>
                            <Link href={route('admin.competitions.setup', [edition.competition_id, edition.id])}>
                                <Users className="mr-2 h-4 w-4" />
                                Participantes
                            </Link>
                        </Button>
                        <Button asChild>
                            <Link href={route('admin.editions.stages.create', edition.id)}>
                                <Plus className="mr-2 h-4 w-4" />
                                Nueva etapa
                            </Link>
                        </Button>
                    </div>
                </div>

                <Card>
                    <CardContent className="p-0">
                        {stages.length === 0 ? (
                            <div className="flex flex-col items-center justify-center py-12 text-center">
                                <p className="text-sm text-muted-foreground">No hay etapas</p>
                            </div>
                        ) : (
                            <div className="divide-y">
                                {stages.map((stage) => (
                                    <div key={stage.id} className="flex items-center justify-between p-4">
                                        <div className="flex items-center gap-3">
                                            <Badge variant="outline" className="flex h-8 w-8 items-center justify-center rounded-full p-0">
                                                {stage.number}
                                            </Badge>
                                            <div>
                                                <p className="font-medium">{stage.name}</p>
                                                <p className="text-sm text-muted-foreground">
                                                    {stage.type} · {stage.distance ? `${stage.distance} km` : '-'}
                                                    {stage.elevation_gain ? ` · ${stage.elevation_gain.toLocaleString()} m` : ''}
                                                </p>
                                            </div>
                                        </div>
                                        <div className="flex items-center gap-2">
                                            <Badge variant="secondary">{stage.status}</Badge>
                                            <Button variant="ghost" size="sm" asChild>
                                                <Link href={route('admin.editions.stages.show', [edition.id, stage.id])}>
                                                    <Eye className="h-4 w-4" />
                                                </Link>
                                            </Button>
                                            <Button variant="ghost" size="sm" asChild>
                                                <Link href={route('admin.editions.stages.edit', [edition.id, stage.id])}>
                                                    <Edit className="h-4 w-4" />
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
