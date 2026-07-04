import { useState } from 'react';
import AdminLayout from '@/Layouts/AdminLayout';
import { Head, Link, router } from '@inertiajs/react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { ArrowLeft, Flag, RotateCcw, Plus, Trash2, Trophy, Star, Bike, MapPin, Ruler, Crown, Flame } from 'lucide-react';
import { StageTypeIcon } from '@/components/ui/stage-type-icon';
import { cn } from '@/lib/utils';

interface Rider {
    id: string;
    name: string;
    country_id: string | null;
}

interface Team {
    id: string;
    name: string;
}

interface Result {
    id: string;
    rider_id: string;
    position: number;
    time: string | null;
    gap: string | null;
    is_gc_leader?: boolean;
    is_combativo?: boolean;
}

interface Stage {
    id: string;
    number: number;
    name: string;
    type: string;
    type_value: string;
    date: string;
    distance: number | null;
    elevation_gain: number | null;
    difficulty: number | null;
    origin: string;
    destination: string;
    status: string;
    status_label: string;
}

export default function Show({ edition, stage, availableRiders, availableTeams, results, is_ttt }: {
    edition: { id: string; year: number; competition: string };
    stage: Stage;
    availableRiders: Rider[];
    availableTeams?: Team[];
    results: Result[];
    is_ttt?: boolean;
}) {
    const [resultEntries, setResultEntries] = useState<Result[]>(
        results.length > 0
            ? results
            : [{ id: '', rider_id: '', position: 1, time: '', gap: '' }]
    );

    const addRow = () => {
        setResultEntries([...resultEntries, { id: '', rider_id: '', position: resultEntries.length + 1, time: '', gap: '' }]);
    };

    const removeRow = (index: number) => {
        setResultEntries(resultEntries.filter((_, i) => i !== index));
    };

    const updateRow = (index: number, field: keyof Result, value: string | number | boolean) => {
        const updated = [...resultEntries];
        (updated[index] as any)[field] = value;
        setResultEntries(updated);
    };

    const saveResults = () => {
        const data = resultEntries.map((r, i) => ({
            rider_id: r.rider_id,
            position: i + 1,
            time: r.time || null,
            gap: r.gap || null,
            is_gc_leader: r.is_gc_leader ?? false,
            is_combativo: r.is_combativo ?? false,
        }));

        router.post(route('admin.editions.stages.results', [edition.id, stage.id]), { results: data });
    };

    const markFinished = () => {
        router.post(route('admin.editions.stages.finish', [edition.id, stage.id]));
    };

    const markUpcoming = () => {
        router.post(route('admin.editions.stages.upcoming', [edition.id, stage.id]));
    };

    const isFinished = stage.status === 'finished';

    return (
        <AdminLayout>
            <Head title={`Etapa ${stage.number} — ${stage.name}`} />

            <div className="space-y-6">
                <div className="flex items-center gap-4">
                    <Button variant="ghost" size="icon" asChild>
                        <Link href={route('admin.editions.stages.index', edition.id)}>
                            <ArrowLeft className="h-4 w-4" />
                        </Link>
                    </Button>
                    <div className="flex-1">
                        <h1 className="text-2xl font-semibold tracking-tight">
                            Etapa {stage.number}: {stage.name}
                            <StageTypeIcon type={stage.type_value} className="ml-2 inline-block h-5 w-5 align-text-bottom text-muted-foreground/70" />
                        </h1>
                        <p className="text-sm text-muted-foreground">
                            {edition.competition} {edition.year}
                        </p>
                    </div>
                    <Badge variant={isFinished ? 'default' : 'secondary'}>{stage.status_label}</Badge>
                </div>

                <div className="flex gap-2">
                    {!isFinished ? (
                        <Button onClick={markFinished}>
                            <Flag className="mr-2 h-4 w-4" />
                            Finalizar etapa
                        </Button>
                    ) : (
                        <Button variant="outline" onClick={markUpcoming}>
                            <RotateCcw className="mr-2 h-4 w-4" />
                            Reabrir etapa
                        </Button>
                    )}
                </div>

                <div className="grid gap-4 sm:grid-cols-4">
                    <Card>
                        <CardContent className="flex flex-col items-center gap-1 p-4 text-center">
                            <MapPin className="h-4 w-4 text-muted-foreground" />
                            <span className="text-xs text-muted-foreground">Recorrido</span>
                            <span className="text-sm font-medium">{stage.origin} → {stage.destination}</span>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardContent className="flex flex-col items-center gap-1 p-4 text-center">
                            <Ruler className="h-4 w-4 text-muted-foreground" />
                            <span className="text-xs text-muted-foreground">Distancia</span>
                            <span className="text-sm font-medium">{stage.distance ? `${stage.distance} km` : '-'}</span>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardContent className="flex flex-col items-center gap-1 p-4 text-center">
                            <Bike className="h-4 w-4 text-muted-foreground" />
                            <span className="text-xs text-muted-foreground">Desnivel</span>
                            <span className="text-sm font-medium">{stage.elevation_gain ? `${stage.elevation_gain.toLocaleString()} m` : '-'}</span>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardContent className="flex flex-col items-center gap-1 p-4 text-center">
                            <Star className="h-4 w-4 text-yellow-500" />
                            <span className="text-xs text-muted-foreground">Dificultad</span>
                            <span className="text-sm font-medium">{stage.difficulty ? '★'.repeat(stage.difficulty) : '-'}</span>
                        </CardContent>
                    </Card>
                </div>

                <Card>
                    <CardHeader className="pb-3">
                        <CardTitle className="flex items-center gap-2">
                            <Trophy className="h-5 w-5 text-accent-500" />
                            Resultados
                        </CardTitle>
                    </CardHeader>
                    <CardContent className="space-y-4">
                        <div className="space-y-3">
                            {resultEntries.map((entry, index) => (
                                <div key={index} className="flex items-end gap-2">
                                    <div className="w-8 text-center">
                                        <Label className="text-xs text-muted-foreground">#{index + 1}</Label>
                                    </div>
                                    <div className="flex-1 space-y-1">
                                        <Label className="text-xs text-muted-foreground">{is_ttt ? 'Equipo' : 'Corredor'}</Label>
                                        <Select
                                            value={entry.rider_id}
                                            onValueChange={(v) => v && updateRow(index, 'rider_id', v)}
                                        >
                                            <SelectTrigger><SelectValue placeholder="Seleccionar...">
                                                {(value: string) => {
                                                    if (is_ttt && availableTeams) return availableTeams.find(t => t.id === value)?.name ?? value;
                                                    return availableRiders.find(r => r.id === value)?.name ?? value;
                                                }}
                                            </SelectValue></SelectTrigger>
                                            <SelectContent>
                                                {(is_ttt && availableTeams ? availableTeams : availableRiders).map((item) => (
                                                    <SelectItem key={item.id} value={item.id}>{item.name}</SelectItem>
                                                ))}
                                            </SelectContent>
                                        </Select>
                                    </div>
                                    <div className="w-24 space-y-1">
                                        <Label className="text-xs text-muted-foreground">Tiempo</Label>
                                        <Input
                                            value={entry.time ?? ''}
                                            onChange={(e) => updateRow(index, 'time', e.target.value)}
                                            placeholder="4:30:00"
                                        />
                                    </div>
                                    <div className="w-20 space-y-1">
                                        <Label className="text-xs text-muted-foreground">Dif.</Label>
                                        <Input
                                            value={entry.gap ?? ''}
                                            onChange={(e) => updateRow(index, 'gap', e.target.value)}
                                            placeholder="+0:00"
                                        />
                                    </div>
                                    <div className="flex flex-col items-center gap-1 pt-5">
                                        <button
                                            type="button"
                                            onClick={() => updateRow(index, 'is_gc_leader', !entry.is_gc_leader)}
                                            className={cn(
                                                'flex h-7 w-7 items-center justify-center rounded-md border text-xs transition-colors',
                                                entry.is_gc_leader ? 'border-yellow-500/50 bg-yellow-500/10 text-yellow-600' : 'border-border text-muted-foreground/50 hover:text-muted-foreground',
                                            )}
                                            title="Líder GC"
                                        >
                                            <Crown className="h-3.5 w-3.5" />
                                        </button>
                                        <button
                                            type="button"
                                            onClick={() => updateRow(index, 'is_combativo', !entry.is_combativo)}
                                            className={cn(
                                                'flex h-7 w-7 items-center justify-center rounded-md border text-xs transition-colors',
                                                entry.is_combativo ? 'border-red-500/50 bg-red-500/10 text-red-600' : 'border-border text-muted-foreground/50 hover:text-muted-foreground',
                                            )}
                                            title="Supercombativo"
                                        >
                                            <Flame className="h-3.5 w-3.5" />
                                        </button>
                                    </div>
                                    {resultEntries.length > 1 && (
                                        <Button variant="ghost" size="icon" onClick={() => removeRow(index)}>
                                            <Trash2 className="h-4 w-4 text-destructive" />
                                        </Button>
                                    )}
                                </div>
                            ))}
                        </div>

                        <div className="flex items-center gap-2">
                            <Button variant="outline" size="sm" onClick={addRow}>
                                <Plus className="mr-1 h-3 w-3" />
                                Añadir posición
                            </Button>
                            <div className="flex-1" />
                            <Button onClick={saveResults} disabled={resultEntries.some((r) => !r.rider_id)}>
                                <Trophy className="mr-2 h-4 w-4" />
                                {isFinished ? 'Actualizar resultados' : 'Guardar resultados y finalizar'}
                            </Button>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </AdminLayout>
    );
}
