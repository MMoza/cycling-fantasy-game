import { useState } from 'react';
import AdminLayout from '@/Layouts/AdminLayout';
import { Head, Link, router } from '@inertiajs/react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { ArrowLeft, Plus, Trash2, Bike, Users, Check, X } from 'lucide-react';

interface Team {
    id: string;
    name: string;
}

interface Rider {
    id: string;
    name: string;
    nationality: string | null;
}

interface ParticipantGroup {
    team_id: string;
    team_name: string;
    riders: Rider[];
}

export default function Setup({ competition, edition, participants, teams }: {
    competition: { id: string; name: string };
    edition: { id: string; year: number; stages_count: number };
    participants: ParticipantGroup[];
    teams: Team[];
}) {
    const [selectedTeam, setSelectedTeam] = useState('');

    const addTeam = () => {
        if (selectedTeam) {
            router.post(route('admin.competitions.setup.add-team', [competition.id, edition.id]), {
                team_id: selectedTeam,
            }, {
                onSuccess: () => setSelectedTeam(''),
            });
        }
    };

    const removeTeam = (teamId: string) => {
        router.delete(route('admin.competitions.setup.remove-team', [competition.id, edition.id, teamId]));
    };

    const toggleRider = (teamId: string, riderId: string, active: boolean) => {
        router.post(route('admin.competitions.setup.toggle-rider', [competition.id, edition.id]), {
            team_id: teamId,
            rider_id: riderId,
            active,
        });
    };

    const teamOptions = teams.filter((t) => !participants.some((p) => p.team_id === t.id));

    return (
        <AdminLayout>
            <Head title={`${competition.name} ${edition.year} — Participantes`} />

            <div className="space-y-6">
                <div className="flex items-center gap-4">
                    <Button variant="ghost" size="icon" asChild>
                        <Link href={route('admin.competitions.editions.index', competition.id)}>
                            <ArrowLeft className="h-4 w-4" />
                        </Link>
                    </Button>
                    <div>
                        <h1 className="text-2xl font-semibold tracking-tight">
                            {competition.name} {edition.year}
                        </h1>
                        <p className="text-sm text-muted-foreground">
                            {edition.stages_count} etapas · Configuración de participantes
                        </p>
                    </div>
                </div>

                <Card>
                    <CardHeader className="pb-3">
                        <CardTitle>Añadir equipo participante</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div className="flex items-end gap-3">
                            <div className="flex-1 space-y-2">
                                <Select value={selectedTeam} onValueChange={(v) => v && setSelectedTeam(v)}>
                                    <SelectTrigger><SelectValue placeholder="Seleccionar equipo...">
                                        {(value: string) => teamOptions.find(t => t.id === value)?.name ?? value}
                                    </SelectValue></SelectTrigger>
                                    <SelectContent>
                                        {teamOptions.length === 0 ? (
                                            <div className="px-2 py-4 text-center text-sm text-muted-foreground">
                                                Todos los equipos están añadidos
                                            </div>
                                        ) : (
                                            teamOptions.map((t) => (
                                                <SelectItem key={t.id} value={t.id}>{t.name}</SelectItem>
                                            ))
                                        )}
                                    </SelectContent>
                                </Select>
                            </div>
                            <Button onClick={addTeam} disabled={!selectedTeam}>
                                <Plus className="mr-1 h-4 w-4" />
                                Añadir equipo
                            </Button>
                        </div>
                        <p className="mt-2 text-xs text-muted-foreground">
                            Se añadirán todos los corredores de la plantilla del equipo para esta temporada.
                        </p>
                    </CardContent>
                </Card>

                {participants.length === 0 ? (
                    <div className="flex flex-col items-center justify-center py-12 text-center">
                        <Users className="h-12 w-12 text-muted-foreground" />
                        <p className="mt-4 text-sm text-muted-foreground">No hay equipos participantes</p>
                    </div>
                ) : (
                    <div className="grid gap-4 md:grid-cols-2">
                        {participants.map((group) => (
                            <Card key={group.team_id}>
                                <CardHeader className="flex flex-row items-center justify-between pb-3">
                                    <CardTitle className="text-base">{group.team_name}</CardTitle>
                                    <div className="flex items-center gap-2">
                                        <Badge variant="secondary">{group.riders.length}</Badge>
                                        <Button variant="ghost" size="sm" onClick={() => removeTeam(group.team_id)}>
                                            <Trash2 className="h-4 w-4 text-destructive" />
                                        </Button>
                                    </div>
                                </CardHeader>
                                <CardContent className="p-0">
                                    <div className="divide-y">
                                        {group.riders.map((rider) => (
                                            <div key={rider.id} className="flex items-center justify-between px-4 py-2">
                                                <div className="flex items-center gap-2">
                                                    <Bike className="h-3 w-3 text-muted-foreground" />
                                                    <span className="text-sm">{rider.name}</span>
                                                </div>
                                                <span className="text-xs text-muted-foreground">{rider.nationality}</span>
                                            </div>
                                        ))}
                                    </div>
                                </CardContent>
                            </Card>
                        ))}
                    </div>
                )}
            </div>
        </AdminLayout>
    );
}
