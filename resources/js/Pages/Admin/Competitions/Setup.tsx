import { useState } from 'react';
import AdminLayout from '@/Layouts/AdminLayout';
import { Head, Link, router } from '@inertiajs/react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { ArrowLeft, Plus, Trash2, Bike, Users } from 'lucide-react';
import { FlagIcon } from '@/components/ui/flag-icon';

interface Team {
    id: string;
    name: string;
}

interface Rider {
    id: string;
    full_name: string;
    country: string | null;
    active: boolean;
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
                            Todos los corredores aparecerán activados por defecto. Puedes desactivar los que no participen.
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
                                        <Badge variant="secondary">
                                            {group.riders.filter((r) => r.active).length}/{group.riders.length}
                                        </Badge>
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
                                                    <span className="text-sm">{rider.full_name}</span>
                                                </div>
                                                <div className="flex items-center gap-2">
                                                    {rider.country && <FlagIcon code={rider.country} className="inline-block h-3 w-4 rounded-sm" />}
                                                    <span className="text-xs text-muted-foreground">{rider.country}</span>
                                                    <button
                                                        type="button"
                                                        onClick={() => toggleRider(group.team_id, rider.id, !rider.active)}
                                                        className={`relative inline-flex h-6 w-11 shrink-0 cursor-pointer items-center rounded-full border-2 border-transparent transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 ${
                                                            rider.active
                                                                ? 'bg-green-600 hover:bg-green-700'
                                                                : 'bg-muted-foreground/30 hover:bg-muted-foreground/50'
                                                        }`}
                                                        title={rider.active ? 'Activo' : 'Inactivo'}
                                                    >
                                                        <span
                                                            className={`pointer-events-none block h-5 w-5 rounded-full bg-white shadow ring-0 transition-transform ${
                                                                rider.active ? 'translate-x-5' : 'translate-x-0'
                                                            }`}
                                                        />
                                                    </button>
                                                </div>
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
