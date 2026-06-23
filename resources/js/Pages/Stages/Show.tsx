import { useState } from 'react';
import { Head, Link, router, usePage } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { ChevronLeft, ChevronRight, Lock, Save, Mountain, MapPin, ArrowRight, Gauge } from 'lucide-react';

interface Stage {
    id: string;
    number: number;
    name: string;
    date: string;
    type: string;
    distance: string | null;
    elevation_gain: number | null;
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

interface AllStage {
    id: string;
    number: number;
    name: string;
}

interface ShowProps {
    league_id: string;
    stage: Stage;
    is_locked: boolean;
    predictions: Record<string, Prediction>;
    navigation: Navigation;
    all_stages: AllStage[];
}

const PREDICTION_CATEGORIES = [
    { key: 'stage_winner', label: 'Ganador de etapa' },
    { key: 'stage_second', label: '2º clasificado' },
    { key: 'stage_third', label: '3º clasificado' },
    { key: 'stage_leader', label: 'Líder GC' },
    { key: 'stage_combativo', label: 'Combativo del día' },
];

export default function Show({ league_id, stage, is_locked, predictions, navigation }: ShowProps) {
    const { errors } = usePage().props as any;

    const [formData, setFormData] = useState<Record<string, string>>(() => {
        const initial: Record<string, string> = {};
        PREDICTION_CATEGORIES.forEach(({ key }) => {
            const existing = predictions[key];
            initial[key] = existing ? (Array.isArray(existing.value) ? existing.value.join(', ') : String(existing.value)) : '';
        });
        return initial;
    });

    const [saving, setSaving] = useState(false);

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        setSaving(true);

        const predictionsData = PREDICTION_CATEGORIES.map(({ key }) => ({
            category: key,
            value: formData[key],
        }));

        router.post(
            route('predictions.store', [league_id, stage.id]),
            { predictions: predictionsData },
            {
                preserveScroll: true,
                preserveState: true,
                onFinish: () => setSaving(false),
            }
        );
    };

    const hasPredictions = Object.keys(predictions).length > 0;

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

                {/* Profile image */}
                {stage.profile_image && (
                    <div className="overflow-hidden rounded-xl border">
                        <img
                            src={stage.profile_image}
                            alt={`Perfil de la etapa ${stage.number}`}
                            className="w-full object-cover"
                            style={{ maxHeight: '200px' }}
                        />
                    </div>
                )}

                {/* Stage details */}
                <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <Card>
                        <div className="h-1 rounded-t-xl bg-brand-600" />
                        <CardContent className="flex flex-col items-center justify-center p-4">
                            <span className="text-xs text-muted-foreground">Tipo</span>
                            <span className="mt-1 font-medium">{stage.type}</span>
                        </CardContent>
                    </Card>
                    <Card>
                        <div className="h-1 rounded-t-xl bg-accent-500" />
                        <CardContent className="flex flex-col items-center justify-center p-4">
                            <span className="text-xs text-muted-foreground">Distancia</span>
                            <span className="mt-1 font-medium">{stage.distance ?? '-'}</span>
                        </CardContent>
                    </Card>
                    <Card>
                        <div className="h-1 rounded-t-xl bg-blue-500" />
                        <CardContent className="flex flex-col items-center justify-center p-4">
                            <span className="text-xs text-muted-foreground">Fecha</span>
                            <span className="mt-1 font-medium">{stage.date}</span>
                        </CardContent>
                    </Card>
                    {stage.elevation_gain && (
                        <Card>
                            <div className="h-1 rounded-t-xl bg-green-600" />
                            <CardContent className="flex flex-col items-center justify-center p-4">
                                <span className="text-xs text-muted-foreground">Desnivel</span>
                                <span className="mt-1 font-medium">{stage.elevation_gain.toLocaleString()} m</span>
                            </CardContent>
                        </Card>
                    )}
                    <Card className={stage.elevation_gain ? '' : 'lg:col-span-2'}>
                        <CardContent className="flex flex-col items-center justify-center p-4">
                            <span className="text-xs text-muted-foreground">Recorrido</span>
                            <span className="mt-1 text-center font-medium">{stage.origin} → {stage.destination}</span>
                        </CardContent>
                    </Card>
                </div>

                <Card>
                    <CardHeader className="pb-3">
                        <div className="flex items-center justify-between">
                            <CardTitle>Pronósticos de etapa</CardTitle>
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
                    </CardHeader>
                    <CardContent>
                        {is_locked ? (
                            <div className="space-y-4">
                                {PREDICTION_CATEGORIES.map(({ key, label }) => {
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
                            <form onSubmit={handleSubmit} className="space-y-4">
                                {PREDICTION_CATEGORIES.map(({ key, label }) => {
                                    const prediction = predictions[key];
                                    return (
                                        <div key={key} className="space-y-2">
                                            <Label htmlFor={key}>{label}</Label>
                                            <Input
                                                id={key}
                                                value={formData[key]}
                                                onChange={(e) => setFormData((prev) => ({ ...prev, [key]: e.target.value }))}
                                                placeholder={prediction ? (Array.isArray(prediction.value) ? prediction.value.join(', ') : String(prediction.value)) : `Ej: ciclista...`}
                                            />
                                            {errors[`predictions.${key}`] && (
                                                <p className="text-sm text-destructive">{errors[`predictions.${key}`]}</p>
                                            )}
                                        </div>
                                    );
                                })}
                                <div className="flex justify-end">
                                    <Button type="submit" disabled={saving}>
                                        <Save className="mr-2 h-4 w-4" />
                                        {saving ? 'Guardando...' : 'Guardar pronósticos'}
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
