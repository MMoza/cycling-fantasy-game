import { useState } from 'react';
import { Head, router, usePage } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Lock, Save, Target } from 'lucide-react';

const PRE_RACE_CATEGORIES = [
    { key: 'gc_top_5', label: 'Top 5 clasificación general', placeholder: 'Ej: Rider A, Rider B, Rider C, Rider D, Rider E' },
    { key: 'points_winner', label: 'Ganador maillot verde', placeholder: 'Ej: Rider A' },
    { key: 'mountains_winner', label: 'Ganador maillot montaña', placeholder: 'Ej: Rider B' },
    { key: 'youth_winner', label: 'Ganador maillot blanco', placeholder: 'Ej: Rider C' },
    { key: 'teams_winner', label: 'Ganador clasificación equipos', placeholder: 'Ej: Team A' },
    { key: 'super_combativo', label: 'Supercombativo final', placeholder: 'Ej: Rider D' },
];

interface Prediction {
    category: string;
    value: string | string[];
    locked_at: string | null;
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
}

export default function PreRace({ league_id, league_name, competition, is_locked, predictions }: PreRaceProps) {
    const { errors } = usePage().props as any;

    const [formData, setFormData] = useState<Record<string, string>>(() => {
        const initial: Record<string, string> = {};
        PRE_RACE_CATEGORIES.forEach(({ key }) => {
            const existing = predictions[key];
            initial[key] = existing ? (Array.isArray(existing.value) ? existing.value.join(', ') : String(existing.value)) : '';
        });
        return initial;
    });

    const [saving, setSaving] = useState(false);

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        setSaving(true);

        const predictionsData = PRE_RACE_CATEGORIES.map(({ key }) => ({
            category: key,
            value: formData[key],
        }));

        router.post(
            route('predictions.pre-race.store', league_id),
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
            <Head title={`Pronósticos — ${competition.name} ${competition.year}`} />

            <div className="space-y-6">
                <div>
                    <h1 className="text-2xl font-semibold tracking-tight">Pronósticos pre-carrera</h1>
                    <p className="text-sm text-muted-foreground">
                        {competition.name} {competition.year} · {league_name}
                    </p>
                </div>

                {errors.race && (
                    <p className="text-sm text-destructive">{errors.race}</p>
                )}

                <Card>
                    <CardHeader className="pb-3">
                        <div className="flex items-center justify-between">
                            <CardTitle className="flex items-center gap-2">
                                <Target className="h-5 w-5" />
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
                    <CardContent>
                        {is_locked ? (
                            <div className="space-y-4">
                                {PRE_RACE_CATEGORIES.map(({ key, label }) => {
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
                                {PRE_RACE_CATEGORIES.map(({ key, label, placeholder }) => (
                                    <div key={key} className="space-y-2">
                                        <Label htmlFor={key}>{label}</Label>
                                        <Input
                                            id={key}
                                            value={formData[key]}
                                            onChange={(e) => setFormData((prev) => ({ ...prev, [key]: e.target.value }))}
                                            placeholder={placeholder}
                                        />
                                        {errors[`predictions.${key}`] && (
                                            <p className="text-sm text-destructive">{errors[`predictions.${key}`]}</p>
                                        )}
                                    </div>
                                ))}
                                <div className="flex justify-end pt-2">
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
