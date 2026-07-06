import { useState, useMemo, useRef } from 'react';
import { Head, Link, router, usePage } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import SearchableSelect from '@/components/ui/searchable-select';
import { StageTypeIcon } from '@/components/ui/stage-type-icon';
import { ChevronLeft, ChevronRight, Lock, Save, Star, FileCheck, Trophy, Medal, Users, Crown, Flame, ChevronDown, ChevronUp, Route, Mountain, Clock, MapPin, ExternalLink } from 'lucide-react';
import { cn } from '@/lib/utils';

interface Stage {
    id: string;
    number: number;
    name: string;
    date: string;
    type: string;
    type_value: string;
    distance: string | null;
    elevation_gain: number | null;
    difficulty: number | null;
    profile_image: string | null;
    origin: string;
    destination: string;
    status: string;
    scheduled_start: string | null;
    live_stream_url: string | null;
}

interface Navigation {
    prev: { id: string; number: number } | null;
    next: { id: string; number: number } | null;
}

interface Prediction {
    category: string;
    value: string | string[] | Record<string, string>;
    locked_at: string | null;
}

interface StageResult {
    position: number;
    rider_id: string;
    rider_name: string;
    time: string | null;
    gap: string | null;
    profile_image: string | null;
    is_gc_leader: boolean;
    is_combativo: boolean;
}

interface ClassificationEntry {
    user_id: string;
    user_name: string;
    total_points: number;
}

interface Option {
    value: string;
    label: string;
}

interface ShowProps {
    league_id: string;
    league_name: string;
    stage: Stage;
    is_finished: boolean;
    is_locked: boolean;
    predictions: Record<string, Prediction>;
    stage_results: StageResult[];
    stage_classification: ClassificationEntry[];
    navigation: Navigation;
    all_stages: { id: string; number: number; name: string }[];
    availableRiders: Option[];
    availableTeams: Option[];
    pcs_slug: string | null;
    edition_year: number;
}

const PREDICTION_CATEGORIES = [
    { key: 'stage_winner', label: 'Ganador de etapa' },
    { key: 'stage_second', label: '2º clasificado' },
    { key: 'stage_third', label: '3º clasificado' },
    { key: 'stage_leader', label: 'Líder GC' },
    { key: 'stage_combativo', label: 'Combativo del día' },
];

const MEDAL_COLORS = ['text-yellow-500', 'text-gray-400', 'text-amber-700'];

const STEP_COLORS = ['', 'text-gray-500', 'text-yellow-600'];

function StageResultsCard({ results }: { results: StageResult[] }) {
    const [expanded, setExpanded] = useState(false);
    const podium = results.slice(0, 3);
    const top5 = results.slice(0, 5);
    const hideable = results.slice(5);
    const visible = expanded ? results : top5;
    const hasMore = hideable.length > 0;

    const gcLeader = results.find((r) => r.is_gc_leader);
    const combativo = results.find((r) => r.is_combativo);

    return (
        <Card>
            <CardHeader className="pb-4">
                <CardTitle className="flex items-center gap-2">
                    <Trophy className="h-5 w-5 text-accent-500" />
                    Clasificación de la etapa
                </CardTitle>
            </CardHeader>
            <CardContent className="space-y-5 px-6">
                {/* Podium: 2-1-3 */}
                <div className="grid grid-cols-3 items-end gap-3">
                    {[podium[1], podium[0], podium[2]].map((r, i) => {
                        if (!r) return <div key={i} />;
                        const pos = [2, 1, 3][i];
                        const colors = [
                            'border-gray-300 bg-gray-50',
                            'border-yellow-400 bg-yellow-50',
                            'border-amber-600 bg-amber-50',
                        ];
                        const stepColors = ['', 'text-yellow-600', 'text-gray-500'];
                        return (
                            <div
                                key={r.rider_id}
                                className={cn(
                                    'flex flex-col items-center gap-1 rounded-xl border-2 p-3 text-center',
                                    colors[i],
                                    i === 1 ? 'pt-5' : 'pt-3',
                                )}
                            >
                                <div className={cn('relative', i === 1 ? 'h-14 w-14' : 'h-12 w-12')}>
                                    {r.profile_image ? (
                                        <img
                                            src={r.profile_image}
                                            alt={r.rider_name}
                                            className="h-full w-full rounded-full object-cover object-top"
                                        />
                                    ) : (
                                        <div className="flex h-full w-full items-center justify-center rounded-full bg-muted text-xs font-bold text-muted-foreground">
                                            {r.rider_name.split(' ').pop()?.charAt(0)}
                                        </div>
                                    )}
                                    <div className="absolute -bottom-1 left-1/2 -translate-x-1/2 flex items-center gap-0.5">
                                        {gcLeader?.rider_id === r.rider_id && (
                                            <span className="flex h-4 w-4 items-center justify-center rounded-full bg-white shadow-sm">
                                                <Crown className="h-3 w-3 text-yellow-500" />
                                            </span>
                                        )}
                                        {combativo?.rider_id === r.rider_id && (
                                            <span className="flex h-4 w-4 items-center justify-center rounded-full bg-white shadow-sm">
                                                <Flame className="h-3 w-3 text-red-500" />
                                            </span>
                                        )}
                                    </div>
                                </div>
                                <span className="text-xs font-semibold leading-tight">{r.rider_name}</span>
                                {r.time && <span className="text-xs text-muted-foreground tabular-nums">{r.time}</span>}
                                <Medal className={cn('h-5 w-5', MEDAL_COLORS[pos - 1])} />
                            </div>
                        );
                    })}
                </div>

                {/* Positions 4+ (list) */}
                <div className="space-y-1">
                    {visible.slice(3).map((r) => (
                        <div
                            key={r.rider_id}
                            className="flex items-center gap-3 rounded-lg px-3 py-2 hover:bg-muted/50"
                        >
                            <div className="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-muted text-xs font-semibold text-muted-foreground">
                                {r.position}
                            </div>
                            {r.profile_image ? (
                                <img
                                    src={r.profile_image}
                                    alt=""
                                    className="h-7 w-7 shrink-0 rounded-full object-cover object-top"
                                />
                            ) : (
                                <div className="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-muted text-[10px] font-bold text-muted-foreground">
                                    {r.rider_name.split(' ').pop()?.charAt(0)}
                                </div>
                            )}
                            <span className="flex-1 text-sm font-medium">{r.rider_name}</span>
                            {r.is_gc_leader && <Crown className="h-4 w-4 text-yellow-500" />}
                            {r.is_combativo && <Flame className="h-4 w-4 text-red-500" />}
                            {r.time && <span className="text-xs text-muted-foreground tabular-nums">{r.time}</span>}
                            {r.gap && <span className="text-xs text-muted-foreground tabular-nums">{r.gap}</span>}
                        </div>
                    ))}
                </div>

                {/* Expand button */}
                {hasMore && (
                    <div className="text-center">
                        <Button
                            variant="ghost"
                            size="sm"
                            onClick={() => setExpanded(!expanded)}
                            className="text-xs text-muted-foreground"
                        >
                            {expanded ? (
                                <>Ver menos <ChevronUp className="ml-1 h-3 w-3" /></>
                            ) : (
                                <>Ver más ({hideable.length} restantes) <ChevronDown className="ml-1 h-3 w-3" /></>
                            )}
                        </Button>
                    </div>
                )}

                {/* GC leader + Combativo summary */}
                {(gcLeader || combativo) && (
                    <div className="flex justify-center gap-4">
                        {gcLeader && (
                            <span className="flex items-center gap-1">
                                <Crown className="h-4 w-4 text-yellow-500" />
                            </span>
                        )}
                        {combativo && (
                            <span className="flex items-center gap-1">
                                <Flame className="h-4 w-4 text-red-500" />
                            </span>
                        )}
                    </div>
                )}
            </CardContent>
        </Card>
    );
}

function Stat({ icon, label, value }: { icon: React.ReactNode; label: string; value: string }) {
    return (
        <div className="flex flex-col items-center gap-1.5">
            {icon}
            <span className="text-[10px] font-medium uppercase tracking-wider text-muted-foreground">{label}</span>
            <span className="text-center text-sm font-semibold leading-tight">{value}</span>
        </div>
    );
}

export default function Show({ league_id, league_name, stage, is_finished, is_locked, predictions, stage_results, stage_classification, navigation, availableRiders, availableTeams, pcs_slug, edition_year }: ShowProps) {
    const { errors } = usePage().props as any;
    const isTimeTrial = stage.type_value === 'time_trial' || stage.type_value === 'team_time_trial';
    const isTTT = stage.type_value === 'team_time_trial';

    const categories = PREDICTION_CATEGORIES.filter((c) => c.key !== 'stage_combativo' || !isTimeTrial);

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

    const resolvePredictionValue = (value: string | string[] | Record<string, string>): string => {
        if (Array.isArray(value)) {
            return value.map((id) => riderMap[id] ?? teamMap[id] ?? id).join(', ');
        }
        if (typeof value === 'object' && value !== null) {
            const id = value.team_id ?? value.rider_id ?? Object.values(value)[0];
            return teamMap[id] ?? riderMap[id] ?? id;
        }
        return riderMap[value] ?? teamMap[value] ?? value;
    };

    const extractPredictionId = (value: string | string[] | Record<string, string>): string => {
        if (Array.isArray(value)) return value.join(', ');
        if (typeof value === 'object' && value !== null) return value.team_id ?? value.rider_id ?? String(Object.values(value)[0] ?? '');
        return value;
    };

    const DUPLICABLE_KEYS = ['stage_leader', 'stage_combativo'];

    const getOptions = (key: string) => {
        if (key === 'stage_leader') return availableRiders;
        if (isTTT) return availableTeams;
        return availableRiders;
    };

    const buildSnapshot = () => {
        const snapshot: Record<string, string> = {};
        categories.forEach(({ key }) => {
            const existing = predictions[key];
            snapshot[key] = existing ? extractPredictionId(existing.value) : '';
        });
        return snapshot;
    };

    const savedSnapshotRef = useRef<Record<string, string>>(buildSnapshot());
    const [savedVersion, setSavedVersion] = useState(0);

    const [formData, setFormData] = useState<Record<string, string>>(() => ({ ...savedSnapshotRef.current }));

    const hasSavedPredictions = useMemo(() =>
        categories.some(({ key }) => !!savedSnapshotRef.current[key]),
        [savedVersion]
    );

    const hasUnsavedChanges = useMemo(() =>
        categories.some(({ key }) => formData[key] !== savedSnapshotRef.current[key]),
        [formData, savedVersion]
    );

    const [saving, setSaving] = useState(false);

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        setSaving(true);

        const predictionsData = categories
            .filter(({ key }) => formData[key])
            .map(({ key }) => ({
                category: key,
                value: formData[key],
            }));

        router.post(
            route('predictions.store', [league_id, stage.id]),
            { predictions: predictionsData },
            {
                preserveScroll: true,
                preserveState: true,
                onSuccess: () => {
                    savedSnapshotRef.current = { ...formData };
                    setSavedVersion((v) => v + 1);
                    setSaving(false);
                },
                onError: () => setSaving(false),
            }
        );
    };

    const getFilteredOptions = (key: string) => {
        const allOptions = getOptions(key);
        const currentValue = formData[key];

        return allOptions.filter((o) => {
            if (o.value === currentValue) return true;

            if (DUPLICABLE_KEYS.includes(key)) {
                return true;
            }

            const otherNonDuplicableSelected = Object.entries(formData)
                .filter(([k, v]) => !DUPLICABLE_KEYS.includes(k) && k !== key && v)
                .map(([, v]) => v);
            return !otherNonDuplicableSelected.includes(o.value);
        });
    };

    const myStagePoints = stage_classification.find((e) => e.user_id === (usePage().props as any).auth?.user?.id)?.total_points ?? 0;

    return (
        <AppLayout>
            <Head title={`Etapa ${stage.number} — ${stage.name}`} />

            <div className="space-y-6 py-6" style={{ maxWidth: '42rem', marginLeft: 'auto', marginRight: 'auto', paddingLeft: '1rem', paddingRight: '1rem' }}>
                <div className="flex items-center justify-between">
                    <div className="flex items-center gap-3">
                        {navigation.prev ? (
                            <Link
                                href={route('stages.show', [league_id, navigation.prev.id])}
                                className="flex h-8 w-8 items-center justify-center rounded-full border hover:bg-muted"
                            >
                                <ChevronLeft className="h-4 w-4" />
                            </Link>
                        ) : (
                            <div className="h-8 w-8" />
                        )}
                            <div className="text-center">
                                <p className="text-xs text-muted-foreground">{league_name}</p>
                                <h1 className="text-xl font-semibold tracking-tight">
                                    Etapa {stage.number}
                                </h1>
                                {stage.name && (
                                    <p className="text-sm text-muted-foreground">{stage.name}</p>
                                )}
                            </div>
                        {navigation.next ? (
                            <Link
                                href={route('stages.show', [league_id, navigation.next.id])}
                                className="flex h-8 w-8 items-center justify-center rounded-full border hover:bg-muted"
                            >
                                <ChevronRight className="h-4 w-4" />
                            </Link>
                        ) : (
                            <div className="h-8 w-8" />
                        )}
                    </div>
                </div>

                <Card className="overflow-hidden border">
                    {stage.profile_image && (
                        <div className="w-full">
                            <img
                                src={stage.profile_image}
                                alt={`Perfil de la etapa ${stage.number}`}
                                className="w-full"
                            />
                        </div>
                    )}
                    <CardContent className="space-y-5 p-5 sm:p-6">
                        <div className="grid grid-cols-3 gap-4 sm:grid-cols-6">
                            <Stat icon={<StageTypeIcon type={stage.type_value} className="h-5 w-5 text-muted-foreground" />} label="Tipo" value={stage.type} />
                            <Stat icon={<Route className="h-5 w-5 text-muted-foreground" />} label="Distancia" value={stage.distance ?? '-'} />
                            <Stat icon={<Mountain className="h-5 w-5 text-muted-foreground" />} label="Desnivel" value={stage.elevation_gain ? `${stage.elevation_gain.toLocaleString()}m` : '-'} />
                            <Stat icon={<Star className="h-5 w-5 text-muted-foreground" />} label="Dificultad" value={stage.difficulty ? '★'.repeat(stage.difficulty) : '-'} />
                            <Stat icon={<Clock className="h-5 w-5 text-muted-foreground" />} label="Salida" value={stage.scheduled_start ? new Date(stage.scheduled_start).toLocaleTimeString('es-ES', { hour: '2-digit', minute: '2-digit', timeZone: 'UTC' }) + ' UTC' : '-'} />
                            <Stat icon={<MapPin className="h-5 w-5 text-muted-foreground" />} label="Recorrido" value={`${stage.origin} → ${stage.destination}`} />
                        </div>
                        {pcs_slug && (
                            <div className="pt-3 border-t">
                                <a
                                    href={`https://www.procyclingstats.com/race/${pcs_slug}/${edition_year}/stage-${stage.number}/live`}
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    className="flex items-center justify-center gap-2 w-full rounded-md bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-blue-700 transition-colors"
                                >
                                    <ExternalLink className="h-4 w-4" />
                                    Seguir carrera en PCS
                                </a>
                            </div>
                        )}
                        {stage.live_stream_url && (
                            <div className={pcs_slug ? 'pt-2' : 'pt-3 border-t'}>
                                <a
                                    href={stage.live_stream_url}
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    className="flex items-center justify-center gap-2 w-full rounded-md bg-black px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-neutral-800 transition-colors"
                                >
                                    <img
                                        src="https://images.cdn.prd.api.discomax.com/9274/49faa35978a9.png?h=60&f=webp"
                                        alt="HBO Max"
                                        className="h-5 w-auto"
                                    />
                                    Ver en HBO Max
                                </a>
                            </div>
                        )}
                    </CardContent>
                </Card>

                {is_finished && stage_results.length > 0 && (
                    <StageResultsCard results={stage_results} />
                )}

                {is_finished && stage_classification.length > 0 && (
        <Card className="border-0">
                        <CardHeader className="pb-3">
                            <CardTitle className="flex items-center gap-2">
                                <Users className="h-5 w-5 text-accent-500" />
                                Clasificación de la liga — Etapa {stage.number}
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="p-0">
                            <div className="divide-y">
                                {stage_classification.map((entry, i) => (
                                    <div key={entry.user_id} className={cn(
                                        'flex items-center gap-3 px-6 py-2.5',
                                        entry.user_id === (usePage().props as any).auth?.user?.id && 'bg-accent/5',
                                    )}>
                                        <span className={cn(
                                            'flex h-6 w-6 shrink-0 items-center justify-center text-xs font-bold',
                                            i < 3 ? '' : 'text-muted-foreground',
                                        )}>
                                            {i < 3 ? (
                                                <Medal className={cn('h-4 w-4', MEDAL_COLORS[i])} />
                                            ) : (
                                                `#${i + 1}`
                                            )}
                                        </span>
                                        <span className="flex-1 text-sm font-medium">{entry.user_name}</span>
                                        <span className="text-sm font-semibold tabular-nums">{entry.total_points} pts</span>
                                    </div>
                                ))}
                            </div>
                        </CardContent>
                    </Card>
                )}

                <Card className="overflow-visible">
                    <CardHeader className="px-6 pb-4 pt-6 sm:px-8">
                        <div className="flex items-center justify-between">
                            <CardTitle className="flex items-center gap-2">
                                {isTTT ? 'Pronóstico por equipos' : 'Mis pronósticos'}
                            </CardTitle>
                            {is_finished && (
                                <Badge variant="secondary" className={cn(
                                    'flex items-center gap-1',
                                    myStagePoints > 0 ? 'border-green-500/30 text-green-600' : 'text-muted-foreground',
                                )}>
                                    <Trophy className="h-3 w-3" />
                                    {myStagePoints} pts
                                </Badge>
                            )}
                            {!is_finished && is_locked && (
                                <Badge variant="secondary" className="flex items-center gap-1">
                                    <Lock className="h-3 w-3" />
                                    Bloqueado
                                </Badge>
                            )}
                            {!is_finished && !is_locked && hasSavedPredictions && !hasUnsavedChanges && (
                                <Badge variant="secondary" className="flex items-center gap-1 border-green-500/30 text-green-600">
                                    <FileCheck className="h-3 w-3" />
                                    Guardado
                                </Badge>
                            )}
                            {!is_finished && !is_locked && hasUnsavedChanges && (
                                <Badge variant="secondary" className="flex items-center gap-1 border-amber-500/30 text-amber-600">
                                    <Save className="h-3 w-3" />
                                    Sin guardar
                                </Badge>
                            )}
                        </div>
                        {!is_finished && isTTT && (
                            <p className="text-sm text-muted-foreground">
                                Para las posiciones de etapa selecciona equipos. El líder GC sigue siendo un corredor.
                            </p>
                        )}
                        {!is_finished && stage.type_value === 'time_trial' && (
                            <p className="text-sm text-muted-foreground">
                                Las etapas contrarreloj no tienen premio a la combatividad.
                            </p>
                        )}
                    </CardHeader>
                    <CardContent className="space-y-6 px-6 py-5 sm:px-8 sm:py-6">
                        {is_finished || is_locked ? (
                            <div className="space-y-5">
                                {categories.map(({ key, label }) => {
                                    const prediction = predictions[key];
                                    return (
                                        <div key={key} className="space-y-1">
                                            <Label className="text-muted-foreground">{label}</Label>
                                            <p className="rounded-lg border bg-muted px-3 py-2 text-sm">
                                                {prediction ? (
                                                    resolvePredictionValue(prediction.value)
                                                ) : (
                                                    <span className="italic text-muted-foreground">Sin pronóstico</span>
                                                )}
                                            </p>
                                        </div>
                                    );
                                })}
                            </div>
                        ) : (
                            <form onSubmit={handleSubmit} className="space-y-5">
                                {categories.map(({ key, label }) => {
                                    const isTeamPick = isTTT && key !== 'stage_leader';
                                    return (
                                        <div key={key} className="space-y-2">
                                            <Label htmlFor={key}>{label}</Label>
                                            <SearchableSelect
                                                options={getFilteredOptions(key)}
                                                value={formData[key]}
                                                onChange={(v) => setFormData((prev) => ({ ...prev, [key]: v }))}
                                                placeholder={`Seleccionar ${isTeamPick ? 'equipo' : 'corredor'}...`}
                                            />
                                            {errors[`predictions.${key}`] && (
                                                <p className="text-sm text-destructive">{errors[`predictions.${key}`]}</p>
                                            )}
                                        </div>
                                    );
                                })}
                                <div className="flex justify-end pt-2">
                                    <Button type="submit" disabled={saving || (!hasUnsavedChanges && hasSavedPredictions)} className={cn(
                                        hasSavedPredictions && !hasUnsavedChanges && 'border-green-500/50 text-green-700 hover:bg-green-50',
                                        hasUnsavedChanges && 'border-amber-500/50 text-amber-700 hover:bg-amber-50',
                                    )}>
                                        {hasUnsavedChanges ? (
                                            <Save className="mr-2 h-4 w-4" />
                                        ) : (
                                            <FileCheck className="mr-2 h-4 w-4" />
                                        )}
                                        {saving ? 'Guardando...' : hasSavedPredictions ? (hasUnsavedChanges ? 'Guardar cambios' : 'Pronóstico guardado') : 'Guardar pronósticos'}
                                    </Button>
                                </div>
                            </form>
                        )}
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}
