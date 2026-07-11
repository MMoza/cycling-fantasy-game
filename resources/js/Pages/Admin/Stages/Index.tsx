import AdminLayout from '@/Layouts/AdminLayout';
import { Head, Link } from '@inertiajs/react';
import { Card, CardContent } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Plus, Edit, ArrowLeft, Eye, Users, Star, Image, Clock } from 'lucide-react';
import { StageTypeIcon } from '@/components/ui/stage-type-icon';

interface Stage {
    id: string;
    number: number;
    name: string;
    date: string;
    type: string;
    type_value: string;
    distance: number | null;
    elevation_gain: number | null;
    difficulty: number | null;
    origin: string;
    destination: string;
    status: string;
    profile_image: string | null;
    scheduled_start: string | null;
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
                            <div className="flex flex-col items-center justify-center px-4 py-12 text-center">
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
                                                <div className="flex items-center gap-2">
                                                    <p className="font-medium">{stage.name}</p>
                                                    {stage.profile_image ? (
                                                        <span className="inline-flex items-center gap-1 rounded-full bg-green-100 px-1.5 py-0.5 text-[10px] font-medium text-green-700 dark:bg-green-900/30 dark:text-green-400">
                                                            <Image className="h-2.5 w-2.5" />
                                                            Perfil
                                                        </span>
                                                    ) : (
                                                        <span className="inline-flex items-center gap-1 rounded-full bg-muted px-1.5 py-0.5 text-[10px] font-medium text-muted-foreground">
                                                            <Image className="h-2.5 w-2.5" />
                                                            Sin perfil
                                                        </span>
                                                    )}
                                                </div>
                                                <p className="text-sm text-muted-foreground">
                                                    <StageTypeIcon type={stage.type_value} className="mr-1 inline-block h-3.5 w-3.5 align-middle" />
                                                    {stage.type} · {stage.distance ? `${stage.distance} km` : '-'}
                                                    {stage.elevation_gain ? ` · ${stage.elevation_gain.toLocaleString()} m` : ''}
                                                    {stage.difficulty ? ` · ${'★'.repeat(stage.difficulty)}` : ''}
                                                    {stage.scheduled_start && (
                                                        <span className="ml-2 inline-flex items-center gap-0.5">
                                                            <Clock className="h-3 w-3" />
                                                            {stage.scheduled_start}
                                                        </span>
                                                    )}
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
