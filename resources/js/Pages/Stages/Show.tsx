import { useState, useMemo, useRef } from 'react';
import { Head, Link, router, usePage } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import SearchableSelect from '@/components/ui/searchable-select';
import { StageTypeIcon } from '@/components/ui/stage-type-icon';
import { ChevronLeft, ChevronRight, Lock, Save, Star, FileCheck } from 'lucide-react';
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
}

interface Navigation {
    prev: { id: string; number: number } | null;
    next: { id: string; number: number } | null;
}

interface Prediction {
    category: string;
    value: string | string[];
    locked_at: string | null;
}

interface Option {
    value: string;
    label: string;
}

interface ShowProps {
    league_id: string;
    stage: Stage;
    is_locked: boolean;
    predictions: Record<string, Prediction>;
    navigation: Navigation;
    all_stages: { id: string; number: number; name: string }[];
    availableRiders: Option[];
    availableTeams: Option[];
}

const PREDICTION_CATEGORIES = [
    { key: 'stage_winner', label: 'Ganador de etapa' },
    { key: 'stage_second', label: '2º clasificado' },
    { key: 'stage_third', label: '3º clasificado' },
    { key: 'stage_leader', label: 'Líder GC' },
    { key: 'stage_combativo', label: 'Combativo del día' },
];

export default function Show({ league_id, stage, is_locked, predictions, navigation, availableRiders, availableTeams }: ShowProps) {
    const { errors } = usePage().props as any;
    const isTimeTrial = stage.type_value === 'time_trial' || stage.type_value === 'team_time_trial';
    const isTTT = stage.type_value === 'team_time_trial';

    const categories = PREDICTION_CATEGORIES.filter((c) => c.key !== 'stage_combativo' || !isTimeTrial);

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
            snapshot[key] = existing ? (Array.isArray(existing.value) ? existing.value.join(', ') : String(existing.value)) : '';
        });
        return snapshot;
    };

    const savedSnapshotRef = useRef<Record<string, string>>(buildSnapshot());
    const [savedVersion, setSavedVersion] = useState(0);

    const [formData, setFormData] = useState<Record<string, string>>(() => ({ ...savedSnapshotRef.current }));

    const hasSavedPredictions = useMemo(() =>
        categories.some(({ key }) => !!savedSnapshotRef.current[key]),
        // eslint-disable-next-line react-hooks/exhaustive-deps
        [savedVersion]
    );

    const hasUnsavedChanges = useMemo(() =>
        categories.some(({ key }) => formData[key] !== savedSnapshotRef.current[key]),
        // eslint-disable-next-line react-hooks/exhaustive-deps
        [formData, savedVersion]
    );

    const [saving, setSaving] = useState(false);

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        setSaving(true);

        const predictionsData = categories.map(({ key }) => ({
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
                const otherDuplicableValue = Object.entries(formData)
                    .filter(([k, v]) => DUPLICABLE_KEYS.includes(k) && k !== key && v)
                    .map(([, v]) => v);
                return !otherDuplicableValue.includes(o.value);
            }

            const otherSelected = Object.entries(formData)
                .filter(([k, v]) => k !== key && v)
                .map(([, v]) => v);
            return !otherSelected.includes(o.value);
        });
    };

    const statCards = [
        { label: 'Tipo', value: stage.type, iconType: stage.type_value, color: 'bg-brand-600' },
        { label: 'Distancia', value: stage.distance ?? '-', color: 'bg-accent-500' },
        { label: 'Fecha', value: stage.date, color: 'bg-blue-500' },
        { label: 'Desnivel', value: stage.elevation_gain ? `${stage.elevation_gain.toLocaleString()} m` : '-', color: 'bg-green-600' },
        { label: 'Recorrido', value: `${stage.origin} → ${stage.destination}`, color: 'bg-purple-500' },
        ...(stage.difficulty ? [{ label: 'Dificultad', value: '★'.repeat(stage.difficulty), color: 'bg-yellow-500' }] : []),
    ];

    return (
        <AppLayout>
            <Head title={`Etapa ${stage.number} — ${stage.name}`} />

            <div className="space-y-6">
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

                {stage.profile_image && (
                    <div className="overflow-hidden rounded-xl border">
                        <img
                            src={stage.profile_image}
                            alt={`Perfil de la etapa ${stage.number}`}
                            className="w-full object-cover"
                        />
                    </div>
                )}

                <div className="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:flex lg:flex-wrap">
                    {statCards.map((stat) => (
                        <Card key={stat.label} className="lg:flex-1 lg:min-w-0">
                            <div className={`h-1 rounded-t-xl ${stat.color}`} />
                            <CardContent className="flex flex-col items-center justify-center p-3 text-center sm:p-4">
                                <span className="text-xs text-muted-foreground">{stat.label}</span>
                                <span className="mt-1 text-sm font-medium sm:text-base">{stat.value}</span>
                                {'iconType' in stat && (
                                    <StageTypeIcon type={stat.iconType as string} className="mt-1 h-5 w-5 text-muted-foreground/70" />
                                )}
                            </CardContent>
                        </Card>
                    ))}
                </div>

                <Card className="overflow-visible">
                    <CardHeader className="px-6 pb-4 pt-6 sm:px-8">
                        <div className="flex items-center justify-between">
                            <CardTitle className="flex items-center gap-2">
                                {isTTT ? 'Pronóstico por equipos' : 'Pronósticos de etapa'}
                            </CardTitle>
                            {is_locked && (
                                <Badge variant="secondary" className="flex items-center gap-1">
                                    <Lock className="h-3 w-3" />
                                    Bloqueado
                                </Badge>
                            )}
                            {!is_locked && hasSavedPredictions && !hasUnsavedChanges && (
                                <Badge variant="secondary" className="flex items-center gap-1 border-green-500/30 text-green-600">
                                    <FileCheck className="h-3 w-3" />
                                    Guardado
                                </Badge>
                            )}
                            {!is_locked && hasUnsavedChanges && (
                                <Badge variant="secondary" className="flex items-center gap-1 border-amber-500/30 text-amber-600">
                                    <Save className="h-3 w-3" />
                                    Sin guardar
                                </Badge>
                            )}
                        </div>
                        {isTTT && (
                            <p className="text-sm text-muted-foreground">
                                Para las posiciones de etapa selecciona equipos. El líder GC sigue siendo un corredor.
                            </p>
                        )}
                        {stage.type_value === 'time_trial' && (
                            <p className="text-sm text-muted-foreground">
                                Las etapas contrarreloj no tienen premio a la combatividad.
                            </p>
                        )}
                    </CardHeader>
                    <CardContent className="space-y-6 px-6 py-5 sm:px-8 sm:py-6">
                        {is_locked ? (
                            <div className="space-y-5">
                                {categories.map(({ key, label }) => {
                                    const prediction = predictions[key];
                                    return (
                                        <div key={key} className="space-y-1">
                                            <Label className="text-muted-foreground">{label}</Label>
                                            <p className="rounded-lg border bg-muted px-3 py-2 text-sm">
                                                {prediction ? (
                                                    Array.isArray(prediction.value)
                                                        ? prediction.value.join(', ')
                                                        : prediction.value
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
