import { useState } from 'react';
import { Head, Link, router, usePage } from '@inertiajs/react';
import AdminLayout from '@/Layouts/AdminLayout';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import SearchableSelect from '@/components/ui/searchable-select';
import { ArrowLeft, Save, Trophy } from 'lucide-react';

interface Option {
    value: string;
    label: string;
}

const CATEGORIES = [
    { key: 'gc_top_5', label: 'Top 5 GC', positions: [1, 2, 3, 4, 5], type: 'rider' as const },
    { key: 'points_winner', label: 'Maillot Verde (podio)', positions: [1, 2, 3], type: 'rider' as const },
    { key: 'mountains_winner', label: 'Maillot Montaña (podio)', positions: [1, 2, 3], type: 'rider' as const },
    { key: 'youth_winner', label: 'Maillot Blanco (podio)', positions: [1, 2, 3], type: 'rider' as const },
    { key: 'teams_winner', label: 'Clasificación Equipos', positions: [1], type: 'team' as const },
    { key: 'super_combativo', label: 'Supercombativo', positions: [1], type: 'rider' as const },
];

interface Props {
    edition: { id: string; year: number; competition: string };
    riders: Option[];
    teams: Option[];
    classifications: Record<string, Record<string, { rider_id?: string; team_id?: string }>>;
}

export default function FinalClassifications({ edition, riders, teams, classifications }: Props) {
    const { errors, flash } = usePage().props as any;

    const getInitial = () => {
        const initial: Record<string, (string | null)[]> = {};
        for (const cat of CATEGORIES) {
            const existing = classifications[cat.key] ?? {};
            initial[cat.key] = cat.positions.map((pos) => {
                const entry = existing[pos];
                return entry?.team_id ?? entry?.rider_id ?? null;
            });
        }
        return initial;
    };

    const [formData, setFormData] = useState<Record<string, (string | null)[]>>(getInitial);
    const [saving, setSaving] = useState(false);

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        setSaving(true);

        const payload: Record<string, string | string[]> = {};

        for (const cat of CATEGORIES) {
            const values = formData[cat.key].filter(Boolean) as string[];
            if (values.length === 0) continue;
            if (cat.key === 'teams_winner' || cat.key === 'super_combativo') {
                payload[cat.key] = values[0];
            } else {
                payload[cat.key] = values;
            }
        }

        router.post(
            route('admin.editions.final-classifications.update', edition.id),
            { classifications: payload },
            {
                preserveScroll: true,
                preserveState: true,
                onFinish: () => setSaving(false),
            }
        );
    };

    const getOptions = (cat: typeof CATEGORIES[number]) =>
        cat.type === 'team' ? teams : riders;

    return (
        <AdminLayout>
            <Head title={`Clasificaciones finales — ${edition.competition} ${edition.year}`} />

            <div className="space-y-6">
                <div className="flex items-center gap-4">
                    <Button variant="ghost" size="icon" asChild>
                        <Link href={route('admin.competitions.editions.index', edition.id)}>
                            <ArrowLeft className="h-4 w-4" />
                        </Link>
                    </Button>
                    <div>
                        <h1 className="text-2xl font-semibold tracking-tight">Clasificaciones finales</h1>
                        <p className="text-sm text-muted-foreground">{edition.competition} {edition.year}</p>
                    </div>
                </div>

                {flash?.success && (
                    <p className="text-sm text-green-600">{flash.success}</p>
                )}

                <form onSubmit={handleSubmit} className="space-y-6">
                    {CATEGORIES.map((cat) => (
                        <Card key={cat.key}>
                            <CardHeader className="pb-3">
                                <CardTitle className="flex items-center gap-2 text-base">
                                    <Trophy className="h-4 w-4 text-accent-500" />
                                    {cat.label}
                                </CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-3">
                                {cat.positions.map((pos, idx) => (
                                    <div key={pos} className="space-y-1">
                                        <Label>
                                            {cat.positions.length > 1 ? `${pos}ª posición` : 'Ganador'}
                                        </Label>
                                        <SearchableSelect
                                            options={getOptions(cat)}
                                            value={formData[cat.key]?.[idx] ?? ''}
                                            onChange={(v) => {
                                                const next = [...(formData[cat.key] ?? [])];
                                                next[idx] = v || null;
                                                setFormData((prev) => ({ ...prev, [cat.key]: next }));
                                            }}
                                            placeholder={`Seleccionar ${cat.type === 'team' ? 'equipo' : 'corredor'}...`}
                                        />
                                    </div>
                                ))}
                            </CardContent>
                        </Card>
                    ))}

                    <div className="flex justify-end">
                        <Button type="submit" disabled={saving}>
                            <Save className="mr-2 h-4 w-4" />
                            {saving ? 'Guardando...' : 'Guardar clasificaciones'}
                        </Button>
                    </div>
                </form>
            </div>
        </AdminLayout>
    );
}
