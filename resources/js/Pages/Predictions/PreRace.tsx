import { useState, useMemo } from 'react';
import { Head, router, usePage } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import SearchableSelect from '@/components/ui/searchable-select';
import { Lock, Save, Target, Info, X } from 'lucide-react';

interface Prediction {
    category: string;
    value: string | string[] | Record<string, string>;
    locked_at: string | null;
}

interface ScoringRule {
    id: string;
    type: string;
    label: string;
    points: number;
    position: number | null;
    difficulty: number | null;
    context: string | null;
}

interface ScoringSystem {
    id: string;
    name: string;
    description: string;
    rules: ScoringRule[];
}

interface PreRaceProps {
    league_id: string;
    league_name: string;
    competition: {
        name: string;
        year: number;
    };
    is_locked: boolean;
    predictions: Record<string, Prediction>;
    availableRiders: { value: string; label: string }[];
    availableTeams: { value: string; label: string }[];
    scoring_system: ScoringSystem;
}

const GC_TOP_5_SLOTS = [
    { label: '1º clasificado general', position: 0 },
    { label: '2º clasificado general', position: 1 },
    { label: '3º clasificado general', position: 2 },
    { label: '4º clasificado general', position: 3 },
    { label: '5º clasificado general', position: 4 },
];

const JERSEY_CATEGORIES = [
    { key: 'points_winner', label: 'Clasificación maillot verde', icon: '🟢' },
    { key: 'mountains_winner', label: 'Clasificación maillot montaña', icon: '🔴' },
    { key: 'youth_winner', label: 'Clasificación maillot blanco', icon: '⚪' },
] as const;

const PODIUM_LABELS = ['1º', '2º', '3º'];

function initArrayPredictions(predictions: Record<string, Prediction>, key: string, length: number): string[] {
    const existing = predictions[key];
    if (!existing) return Array(length).fill('');

    const value = existing.value;
    if (Array.isArray(value)) {
        const arr = [...value];
        while (arr.length < length) arr.push('');
        return arr.slice(0, length);
    }
    if (typeof value === 'object' && value !== null) {
        const riderId = value.rider_id ?? value.team_id ?? Object.values(value)[0];
        const arr = Array(length).fill('');
        arr[0] = riderId as string;
        return arr;
    }
    const arr = Array(length).fill('');
    arr[0] = String(value);
    return arr;
}

export default function PreRace({ league_id, league_name, competition, is_locked, predictions, availableRiders, availableTeams, scoring_system }: PreRaceProps) {
    const { errors } = usePage().props as any;
    const [scoringInfoOpen, setScoringInfoOpen] = useState(false);

    const riderMap = useMemo(() => {
        const map: Record<string, string> = {};
        for (const r of availableRiders) {
            map[r.value] = r.label;
        }
        return map;
    }, [availableRiders]);

    const teamMap = useMemo(() => {
        const map: Record<string, string> = {};
        for (const t of availableTeams) {
            map[t.value] = t.label;
        }
        return map;
    }, [availableTeams]);

    const resolvePredictionValue = (value: string | string[] | Record<string, string>, category: string): string => {
        if (Array.isArray(value)) {
            return value.map((id, i) => {
                if (!id) return null;
                const name = riderMap[id] ?? id;
                return `${i + 1}º ${name}`;
            })
                .filter(Boolean)
                .join(', ');
        }
        if (typeof value === 'object' && value !== null) {
            const id = value.rider_id ?? value.team_id ?? Object.values(value)[0];
            const map = category === 'teams_winner' ? teamMap : riderMap;
            return map[id] ?? id;
        }
        return riderMap[value] ?? value;
    };

    const resolvePodiumValue = (value: string[]): string[] => {
        return value.map((id) => riderMap[id] ?? id);
    };

    const initFormData = (): Record<string, string | string[]> => {
        const data: Record<string, string | string[]> = {};
        data.gc_top_5 = initArrayPredictions(predictions, 'gc_top_5', 5);
        data.points_winner = initArrayPredictions(predictions, 'points_winner', 3);
        data.mountains_winner = initArrayPredictions(predictions, 'mountains_winner', 3);
        data.youth_winner = initArrayPredictions(predictions, 'youth_winner', 3);
        data.teams_winner = initArrayPredictions(predictions, 'teams_winner', 1)[0] ?? '';
        data.super_combativo = initArrayPredictions(predictions, 'super_combativo', 1)[0] ?? '';
        return data;
    };

    const [formData, setFormData] = useState<Record<string, string | string[]>>(initFormData);
    const [saving, setSaving] = useState(false);

    const setArrayValue = (key: string, index: number, value: string) => {
        setFormData((prev) => {
            const arr = [...(prev[key] as string[])];
            arr[index] = value;
            return { ...prev, [key]: arr };
        });
    };

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        setSaving(true);

        const allPredictions = [
            { category: 'gc_top_5', value: (formData.gc_top_5 as string[]).filter(Boolean).join(',') },
            { category: 'points_winner', value: (formData.points_winner as string[]).filter(Boolean).join(',') },
            { category: 'mountains_winner', value: (formData.mountains_winner as string[]).filter(Boolean).join(',') },
            { category: 'youth_winner', value: (formData.youth_winner as string[]).filter(Boolean).join(',') },
            { category: 'teams_winner', value: formData.teams_winner as string },
            { category: 'super_combativo', value: formData.super_combativo as string },
        ];

        const predictionsData = allPredictions.filter((p) => p.value !== '');

        if (predictionsData.length === 0) {
            setSaving(false);
            return;
        }

        router.post(
            route('predictions.pre-race.store', league_id),
            { predictions: predictionsData },
            {
                preserveScroll: true,
                onSuccess: () => setSaving(false),
                onError: () => setSaving(false),
            }
        );
    };

    const hasPredictions = Object.keys(predictions).length > 0;
    const riderOptions = availableRiders;
    const teamOptions = availableTeams;

    return (
        <AppLayout>
            <Head title={`Pronósticos — ${competition.name} ${competition.year}`} />

            <div className="mx-auto max-w-2xl space-y-6 px-4 py-6 sm:px-0">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl font-semibold tracking-tight">Pronósticos pre-carrera</h1>
                        <p className="text-sm text-muted-foreground">
                            {competition.name} {competition.year} · {league_name}
                        </p>
                    </div>
                    <Button variant="ghost" size="sm" onClick={() => setScoringInfoOpen(true)} className="shrink-0">
                        <Info className="mr-1.5 h-4 w-4" />
                        Puntuación
                    </Button>
                </div>

                {errors.race && (
                    <p className="text-sm text-destructive">{errors.race}</p>
                )}

                <Card className="overflow-visible">
                    <div className="h-1 rounded-t-xl bg-gradient-to-r from-brand-600 to-accent-500" />
                    <CardHeader className="px-6 pb-3 pt-6">
                        <div className="flex items-center justify-between">
                            <CardTitle className="flex items-center gap-2">
                                <Target className="h-5 w-5 text-brand-600" />
                                Tus pronósticos
                            </CardTitle>
                            {is_locked && (
                                <Badge variant="secondary" className="flex items-center gap-1">
                                    <Lock className="h-3 w-3" />
                                    Bloqueado
                                </Badge>
                            )}
                            {!is_locked && hasPredictions && (
                                <Badge variant="secondary" className="flex items-center gap-1">
                                    <Save className="h-3 w-3" />
                                    Guardado
                                </Badge>
                            )}
                        </div>
                        <p className="text-sm text-muted-foreground">
                            {is_locked
                                ? 'La competición ya ha comenzado. Estos son tus pronósticos registrados.'
                                : 'Estos pronósticos se bloquearán cuando comience la primera etapa.'}
                        </p>
                    </CardHeader>
                    <CardContent className="overflow-visible px-6">
                        {is_locked ? (
                            <div className="space-y-6">
                                <div className="space-y-3">
                                    <Label className="text-base font-medium">Top 5 clasificación general</Label>
                                    {GC_TOP_5_SLOTS.map(({ label, position }) => {
                                        const prediction = predictions['gc_top_5'];
                                        const value = prediction ? (Array.isArray(prediction.value) ? prediction.value[position] : null) : null;
                                        return (
                                            <div key={`gc_${position}`} className="flex items-center gap-3">
                                                <span className="w-8 shrink-0 text-sm font-medium text-muted-foreground">{position + 1}º</span>
                                                <p className="flex-1 rounded-lg border bg-muted px-3 py-2 text-sm">
                                                    {value ? resolvePredictionValue(value, 'gc_top_5') : <span className="italic text-muted-foreground">Sin pronóstico</span>}
                                                </p>
                                            </div>
                                        );
                                    })}
                                </div>

                                {JERSEY_CATEGORIES.map(({ key, label }) => {
                                    const prediction = predictions[key];
                                    const values = prediction ? (Array.isArray(prediction.value) ? prediction.value : []) : [];
                                    return (
                                        <div key={key} className="space-y-3">
                                            <Label className="text-base font-medium">{label}</Label>
                                            {PODIUM_LABELS.map((podiumLabel, i) => {
                                                const value = values[i] ?? null;
                                                return (
                                                    <div key={`${key}_${i}`} className="flex items-center gap-3">
                                                        <span className="w-8 shrink-0 text-sm font-medium text-muted-foreground">{podiumLabel}</span>
                                                        <p className="flex-1 rounded-lg border bg-muted px-3 py-2 text-sm">
                                                            {value ? resolvePredictionValue(value, key) : <span className="italic text-muted-foreground">Sin pronóstico</span>}
                                                        </p>
                                                    </div>
                                                );
                                            })}
                                        </div>
                                    );
                                })}

                                <div className="space-y-3">
                                    <Label className="text-base font-medium">Ganador clasificación equipos</Label>
                                    {(() => {
                                        const prediction = predictions['teams_winner'];
                                        const raw = prediction?.value;
                                        const value = raw && typeof raw === 'object' && !Array.isArray(raw) ? raw.team_id ?? Object.values(raw)[0] : raw;
                                        return (
                                            <p className="rounded-lg border bg-muted px-3 py-2 text-sm">
                                                {value ? resolvePredictionValue(prediction!.value, 'teams_winner') : <span className="italic text-muted-foreground">Sin pronóstico</span>}
                                            </p>
                                        );
                                    })()}
                                </div>

                                <div className="space-y-3">
                                    <Label className="text-base font-medium">Supercombativo final</Label>
                                    {(() => {
                                        const prediction = predictions['super_combativo'];
                                        const raw = prediction?.value;
                                        const value = raw && typeof raw === 'object' && !Array.isArray(raw) ? raw.rider_id ?? Object.values(raw)[0] : raw;
                                        return (
                                            <p className="rounded-lg border bg-muted px-3 py-2 text-sm">
                                                {value ? resolvePredictionValue(prediction!.value, 'super_combativo') : <span className="italic text-muted-foreground">Sin pronóstico</span>}
                                            </p>
                                        );
                                    })()}
                                </div>
                            </div>
                        ) : (
                            <form onSubmit={handleSubmit} className="space-y-8">
                                <div className="space-y-3">
                                    <Label className="text-base font-medium">Top 5 clasificación general</Label>
                                    <p className="text-sm text-muted-foreground">Selecciona los 5 corredores que crees que terminarán en el Top 5 de la general.</p>
                                    {GC_TOP_5_SLOTS.map(({ label, position }) => (
                                        <div key={`gc_${position}`} className="flex items-center gap-3">
                                            <span className="w-8 shrink-0 text-sm font-medium text-muted-foreground">{position + 1}º</span>
                                            <div className="flex-1">
                                                <SearchableSelect
                                                    options={riderOptions}
                                                    value={(formData.gc_top_5 as string[])[position]}
                                                    onChange={(v) => setArrayValue('gc_top_5', position, v)}
                                                    placeholder="Buscar corredor..."
                                                />
                                            </div>
                                        </div>
                                    ))}
                                    {errors['predictions.gc_top_5'] && (
                                        <p className="text-sm text-destructive">{errors['predictions.gc_top_5']}</p>
                                    )}
                                </div>

                                {JERSEY_CATEGORIES.map(({ key, label }) => (
                                    <div key={key} className="space-y-3">
                                        <Label className="text-base font-medium">{label}</Label>
                                        <p className="text-sm text-muted-foreground">Selecciona el podio (top 3) de esta clasificación.</p>
                                        {PODIUM_LABELS.map((podiumLabel, i) => (
                                            <div key={`${key}_${i}`} className="flex items-center gap-3">
                                                <span className="w-8 shrink-0 text-sm font-medium text-muted-foreground">{podiumLabel}</span>
                                                <div className="flex-1">
                                                    <SearchableSelect
                                                        options={riderOptions}
                                                        value={(formData[key] as string[])[i]}
                                                        onChange={(v) => setArrayValue(key, i, v)}
                                                        placeholder="Buscar corredor..."
                                                    />
                                                </div>
                                            </div>
                                        ))}
                                        {errors[`predictions.${key}`] && (
                                            <p className="text-sm text-destructive">{errors[`predictions.${key}`]}</p>
                                        )}
                                    </div>
                                ))}

                                <div className="space-y-3">
                                    <Label className="text-base font-medium">Ganador clasificación equipos</Label>
                                    <p className="text-sm text-muted-foreground">Selecciona el equipo que crees que ganará la clasificación por equipos.</p>
                                    <SearchableSelect
                                        options={teamOptions}
                                        value={formData.teams_winner as string}
                                        onChange={(v) => setFormData((prev) => ({ ...prev, teams_winner: v }))}
                                        placeholder="Buscar equipo..."
                                    />
                                    {errors['predictions.teams_winner'] && (
                                        <p className="text-sm text-destructive">{errors['predictions.teams_winner']}</p>
                                    )}
                                </div>

                                <div className="space-y-3">
                                    <Label className="text-base font-medium">Supercombativo final</Label>
                                    <p className="text-sm text-muted-foreground">Selecciona el corredor que crees que será el supercombativo de la ronda.</p>
                                    <SearchableSelect
                                        options={riderOptions}
                                        value={formData.super_combativo as string}
                                        onChange={(v) => setFormData((prev) => ({ ...prev, super_combativo: v }))}
                                        placeholder="Buscar corredor..."
                                    />
                                    {errors['predictions.super_combativo'] && (
                                        <p className="text-sm text-destructive">{errors['predictions.super_combativo']}</p>
                                    )}
                                </div>

                                <div className="flex flex-col gap-3 pt-4 pb-2">
                                    {hasPredictions && !saving && (
                                        <p className="text-center text-xs text-muted-foreground">
                                            <Save className="mr-1 inline h-3 w-3" />
                                            Pronósticos guardados anteriormente. Al guardar de nuevo se sobrescribirán.
                                        </p>
                                    )}
                                    <Button
                                        type="submit"
                                        disabled={saving}
                                        className="w-full sm:w-auto self-end"
                                        variant={hasPredictions ? 'secondary' : 'default'}
                                    >
                                        <Save className="mr-2 h-4 w-4" />
                                        {saving ? 'Guardando...' : hasPredictions ? 'Actualizar pronósticos' : 'Guardar pronósticos'}
                                    </Button>
                                </div>
                            </form>
                        )}
                    </CardContent>
                </Card>
                {scoringInfoOpen && (
                    <div className="fixed inset-0 z-50 flex items-end sm:items-center justify-center">
                        <div className="fixed inset-0 bg-black/50" onClick={() => setScoringInfoOpen(false)} />
                        <div className="relative z-10 w-full max-w-lg max-h-[85vh] overflow-y-auto rounded-t-xl bg-popover p-6 shadow-lg sm:rounded-xl animate-in fade-in-0 zoom-in-95 slide-in-from-bottom-8 sm:slide-in-from-bottom-0">
                            <div className="mb-6 flex items-center justify-between">
                                <h2 className="text-lg font-semibold">Sistema de puntuación</h2>
                                <button
                                    type="button"
                                    onClick={() => setScoringInfoOpen(false)}
                                    className="rounded-lg p-1.5 text-muted-foreground transition-colors hover:bg-accent hover:text-accent-foreground"
                                >
                                    <X className="h-5 w-5" />
                                </button>
                            </div>

                            <p className="mb-6 text-sm text-muted-foreground">
                                {scoring_system.name}: {scoring_system.description}
                            </p>

                            <div className="space-y-8">
                                <div>
                                    <h3 className="mb-4 flex items-center gap-2 text-sm font-semibold uppercase tracking-wide text-muted-foreground">
                                        <span className="h-px flex-1 bg-border" />
                                        Clasificación general
                                        <span className="h-px flex-1 bg-border" />
                                    </h3>
                                    <div className="space-y-1.5">
                                        {[1, 2, 3, 4, 5].map((pos) => {
                                            const rule = scoring_system.rules.find((r) => r.type === 'gc_top_5' && r.position === pos);
                                            return (
                                                <div key={pos} className="flex items-center justify-between rounded-lg bg-muted/30 px-4 py-2.5">
                                                    <span className="text-sm font-medium">{pos}º clasificado</span>
                                                    <span className="text-sm font-semibold text-accent-500">{rule?.points ?? '-'} pts</span>
                                                </div>
                                            );
                                        })}
                                    </div>
                                    {(() => {
                                        const partial = scoring_system.rules.find((r) => r.type === 'gc_top_5_partial');
                                        return partial ? (
                                            <p className="mt-2 text-xs text-muted-foreground">
                                                Si aciertas un corredor del Top 5 pero en posición incorrecta: <span className="font-semibold text-accent-500">{partial.points} pts</span>
                                            </p>
                                        ) : null;
                                    })()}
                                </div>

                                <div>
                                    <h3 className="mb-4 flex items-center gap-2 text-sm font-semibold uppercase tracking-wide text-muted-foreground">
                                        <span className="h-px flex-1 bg-border" />
                                        Clasificaciones secundarias
                                        <span className="h-px flex-1 bg-border" />
                                    </h3>
                                    {['points_winner', 'mountains_winner', 'youth_winner'].map((type) => {
                                        const podium = [1, 2, 3].map((pos) =>
                                            scoring_system.rules.find((r) => r.type === type && r.position === pos)
                                        ).filter(Boolean);
                                        const label = type === 'points_winner' ? 'Maillot verde' : type === 'mountains_winner' ? 'Maillot montaña' : 'Maillot blanco';
                                        const partial = scoring_system.rules.find((r) => r.type === `${type}_partial`);
                                        return (
                                            <div key={type} className="mb-4 last:mb-0">
                                                <p className="mb-2 text-sm font-medium">{label}</p>
                                                <div className="space-y-1.5">
                                                    {podium.map((rule) => (
                                                        <div key={rule!.position} className="flex items-center justify-between rounded-lg bg-muted/30 px-4 py-2">
                                                            <span className="text-sm">{rule!.position}º clasificado</span>
                                                            <span className="text-sm font-semibold text-accent-500">{rule!.points} pts</span>
                                                        </div>
                                                    ))}
                                                </div>
                                                {partial && (
                                                    <p className="mt-1.5 text-xs text-muted-foreground">
                                                        Acierto parcial (corredor en el podio pero posición incorrecta): <span className="font-semibold text-accent-500">{partial.points} pts</span>
                                                    </p>
                                                )}
                                            </div>
                                        );
                                    })}
                                </div>

                                <div>
                                    <h3 className="mb-4 flex items-center gap-2 text-sm font-semibold uppercase tracking-wide text-muted-foreground">
                                        <span className="h-px flex-1 bg-border" />
                                        Otras predicciones pre-carrera
                                        <span className="h-px flex-1 bg-border" />
                                    </h3>
                                    <div className="space-y-1.5">
                                        {(() => {
                                            const teamRule = scoring_system.rules.find((r) => r.type === 'teams_winner');
                                            return (
                                                <div className="flex items-center justify-between rounded-lg bg-muted/30 px-4 py-2.5">
                                                    <span className="text-sm font-medium">Ganador clasificación equipos</span>
                                                    <span className="text-sm font-semibold text-accent-500">{teamRule?.points ?? '-'} pts</span>
                                                </div>
                                            );
                                        })()}
                                        {(() => {
                                            const scRule = scoring_system.rules.find((r) => r.type === 'super_combativo');
                                            return (
                                                <div className="flex items-center justify-between rounded-lg bg-muted/30 px-4 py-2.5">
                                                    <span className="text-sm font-medium">Supercombativo final</span>
                                                    <span className="text-sm font-semibold text-accent-500">{scRule?.points ?? '-'} pts</span>
                                                </div>
                                            );
                                        })()}
                                    </div>
                                </div>

                                <div>
                                    <h3 className="mb-4 flex items-center gap-2 text-sm font-semibold uppercase tracking-wide text-muted-foreground">
                                        <span className="h-px flex-1 bg-border" />
                                        Pronósticos por etapa
                                        <span className="h-px flex-1 bg-border" />
                                    </h3>

                                    {[1, 2, 3].map((diff) => {
                                        const rules = scoring_system.rules.filter(
                                            (r) => r.context === 'pre_stage' && r.difficulty === diff && r.type !== 'stage_leader'
                                        );
                                        if (rules.length === 0) return null;
                                        const stars = '★'.repeat(diff) + '☆'.repeat(3 - diff);
                                        return (
                                            <div key={diff} className="mb-4 last:mb-0">
                                                <div className="mb-2 flex items-center gap-2">
                                                    <span className="text-sm font-medium">Dificultad {stars}</span>
                                                </div>
                                                <div className="space-y-1.5">
                                                    {rules.map((rule) => (
                                                        <div key={rule.type} className="flex items-center justify-between rounded-lg bg-muted/30 px-4 py-2">
                                                            <span className="text-sm">{rule.label}</span>
                                                            <span className="text-sm font-semibold text-accent-500">{rule.points} pts</span>
                                                        </div>
                                                    ))}
                                                </div>
                                            </div>
                                        );
                                    })}

                                    {(() => {
                                        const leaderRule = scoring_system.rules.find((r) => r.type === 'stage_leader');
                                        return leaderRule ? (
                                            <div key="leader" className="mb-4">
                                                <div className="mb-2 flex items-center gap-2">
                                                    <span className="text-sm font-medium">Líder de la etapa</span>
                                                </div>
                                                <div className="space-y-1.5">
                                                    <div className="flex items-center justify-between rounded-lg bg-muted/30 px-4 py-2">
                                                        <span className="text-sm">Adivinar el líder al final de la etapa</span>
                                                        <span className="text-sm font-semibold text-accent-500">{leaderRule.points} pts</span>
                                                    </div>
                                                </div>
                                            </div>
                                        ) : null;
                                    })()}

                                    {(() => {
                                        const combativoRule = scoring_system.rules.find((r) => r.type === 'stage_combativo');
                                        return combativoRule ? (
                                            <div key="combativo" className="mb-4">
                                                <div className="mb-2 flex items-center gap-2">
                                                    <span className="text-sm font-medium">Combativo de la etapa</span>
                                                </div>
                                                <div className="space-y-1.5">
                                                    <div className="flex items-center justify-between rounded-lg bg-muted/30 px-4 py-2">
                                                        <span className="text-sm">Adivinar el combativo de la etapa</span>
                                                        <span className="text-sm font-semibold text-accent-500">{combativoRule.points} pts</span>
                                                    </div>
                                                </div>
                                            </div>
                                        ) : null;
                                    })()}
                                </div>
                            </div>
                        </div>
                    </div>
                )}
            </div>
        </AppLayout>
    );
}
