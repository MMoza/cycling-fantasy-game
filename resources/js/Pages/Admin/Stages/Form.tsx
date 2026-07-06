import AdminLayout from '@/Layouts/AdminLayout';
import { Head, Link, useForm } from '@inertiajs/react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { ArrowLeft, Star } from 'lucide-react';

interface StageType {
    value: string;
    label: string;
}

interface Stage {
    id: string;
    number: number;
    name: string;
    date: string;
    type: string;
    distance: number | null;
    elevation_gain: number | null;
    difficulty: number | null;
    origin: string;
    destination: string;
    profile_image: string | null;
    scheduled_start: string | null;
    live_stream_url: string | null;
    status: string;
}

function utcToLocalDatetime(isoString: string | null): string {
    if (!isoString) return '';
    const d = new Date(isoString);
    if (isNaN(d.getTime())) return '';
    const y = d.getFullYear();
    const m = String(d.getMonth() + 1).padStart(2, '0');
    const day = String(d.getDate()).padStart(2, '0');
    const h = String(d.getHours()).padStart(2, '0');
    const min = String(d.getMinutes()).padStart(2, '0');
    return `${y}-${m}-${day}T${h}:${min}`;
}

function localToUtcIso(localDatetime: string): string {
    if (!localDatetime) return '';
    return new Date(localDatetime).toISOString();
}

export default function Form({ edition, stage, stageTypes }: { edition: { id: string; year: number; competition: string; competition_id: string; competition_type: string }; stage: Stage | null; stageTypes: StageType[] }) {
    const isClassic = edition.competition_type === 'classic';
    const { data, setData, post, patch, processing, errors, transform } = useForm({
        number: stage?.number ?? 1,
        name: stage?.name ?? '',
        date: stage?.date ?? '',
        type: stage?.type ?? 'flat',
        distance: stage?.distance ?? '',
        elevation_gain: stage?.elevation_gain ?? '',
        difficulty: stage?.difficulty ?? '',
        origin: stage?.origin ?? '',
        destination: stage?.destination ?? '',
        scheduled_start: utcToLocalDatetime(stage?.scheduled_start ?? null),
        profile_image: stage?.profile_image ?? '',
        live_stream_url: stage?.live_stream_url ?? '',
        status: stage?.status ?? 'upcoming',
    });

    const submit = (e: React.FormEvent) => {
        e.preventDefault();

        transform((formData) => ({
            ...formData,
            scheduled_start: localToUtcIso(formData.scheduled_start),
        }));

        const options = {
            onSuccess: () => {},
        } as any;

        if (stage) {
            patch(route('admin.editions.stages.update', [edition.id, stage.id]), options);
        } else {
            post(route('admin.editions.stages.store', edition.id), options);
        }
    };

    return (
        <AdminLayout>
            <Head title={stage ? 'Editar etapa' : 'Nueva etapa'} />

            <div className="mx-auto max-w-2xl space-y-6">
                <div className="flex items-center gap-4">
                    <Button variant="ghost" size="icon" asChild>
                        <Link href={route('admin.editions.stages.index', edition.id)}>
                            <ArrowLeft className="h-4 w-4" />
                        </Link>
                    </Button>
                    <div>
                        <h1 className="text-2xl font-semibold tracking-tight">
                            {stage ? 'Editar etapa' : 'Nueva etapa'}
                        </h1>
                        <p className="text-sm text-muted-foreground">
                            {edition.competition} {edition.year}
                        </p>
                    </div>
                </div>

                <form onSubmit={submit}>
                    <Card>
                        <CardHeader>
                            <CardTitle>Información de la etapa</CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div className="grid gap-4 sm:grid-cols-2">
                                <div className="space-y-2">
                                    <Label htmlFor="number">Número</Label>
                                    <Input id="number" type="number" min={1} value={data.number} onChange={(e) => setData('number', Number(e.target.value))} />
                                    {errors.number && <p className="text-sm text-destructive">{errors.number}</p>}
                                </div>
                                <div className="space-y-2">
                                    <Label htmlFor="name">Nombre</Label>
                                    <Input id="name" value={data.name} onChange={(e) => setData('name', e.target.value)} />
                                    {errors.name && <p className="text-sm text-destructive">{errors.name}</p>}
                                </div>
                            </div>
                            <div className="grid gap-4 sm:grid-cols-2">
                                <div className="space-y-2">
                                    <Label htmlFor="date">Fecha</Label>
                                    <Input id="date" type="date" value={data.date} onChange={(e) => setData('date', e.target.value)} />
                                    {errors.date && <p className="text-sm text-destructive">{errors.date}</p>}
                                </div>
                                <div className="space-y-2">
                                    <Label htmlFor="scheduled_start">Hora de inicio (UTC)</Label>
                                    <Input id="scheduled_start" type="datetime-local" value={data.scheduled_start} onChange={(e) => setData('scheduled_start', e.target.value)} />
                                    {errors.scheduled_start && <p className="text-sm text-destructive">{errors.scheduled_start}</p>}
                                </div>
                            </div>
                            <div className="grid gap-4 sm:grid-cols-2">
                                <div className="space-y-2">
                                    <Label htmlFor="type">Tipo</Label>
                                    <Select value={data.type} onValueChange={(v) => v && setData('type', v)}>
                                        <SelectTrigger><SelectValue /></SelectTrigger>
                                        <SelectContent>
                                            {stageTypes.map((t) => (
                                                <SelectItem key={t.value} value={t.value}>{t.label}</SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                    {errors.type && <p className="text-sm text-destructive">{errors.type}</p>}
                                </div>
                            </div>
                            <div className="grid gap-4 sm:grid-cols-2">
                                <div className="space-y-2">
                                    <Label htmlFor="distance">Distancia (km)</Label>
                                    <Input id="distance" type="number" step="0.1" min={0} value={data.distance} onChange={(e) => setData('distance', e.target.value)} />
                                    {errors.distance && <p className="text-sm text-destructive">{errors.distance}</p>}
                                </div>
                                <div className="space-y-2">
                                    <Label htmlFor="elevation_gain">Desnivel positivo (m)</Label>
                                    <Input id="elevation_gain" type="number" min={0} value={data.elevation_gain} onChange={(e) => setData('elevation_gain', e.target.value)} />
                                    {errors.elevation_gain && <p className="text-sm text-destructive">{errors.elevation_gain}</p>}
                                </div>
                            </div>
                            <div className="grid gap-4 sm:grid-cols-2">
                                <div className="space-y-2">
                                    <Label htmlFor="difficulty">Dificultad</Label>
                                    <Select value={String(data.difficulty)} onValueChange={(v) => setData('difficulty', v ? Number(v) : '')}>
                                        <SelectTrigger><SelectValue placeholder="Sin determinar" /></SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="1">
                                                <span className="flex items-center gap-1">
                                                    <Star className="h-4 w-4 fill-yellow-500 text-yellow-500" />
                                                </span>
                                            </SelectItem>
                                            <SelectItem value="2">
                                                <span className="flex items-center gap-1">
                                                    <Star className="h-4 w-4 fill-yellow-500 text-yellow-500" />
                                                    <Star className="h-4 w-4 fill-yellow-500 text-yellow-500" />
                                                </span>
                                            </SelectItem>
                                            <SelectItem value="3">
                                                <span className="flex items-center gap-1">
                                                    <Star className="h-4 w-4 fill-yellow-500 text-yellow-500" />
                                                    <Star className="h-4 w-4 fill-yellow-500 text-yellow-500" />
                                                    <Star className="h-4 w-4 fill-yellow-500 text-yellow-500" />
                                                </span>
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
                                    {errors.difficulty && <p className="text-sm text-destructive">{errors.difficulty}</p>}
                                </div>
                                {isClassic && !stage && (
                                    <div className="space-y-2">
                                        <Label>Nota</Label>
                                        <p className="text-sm text-muted-foreground">
                                            Las clásicas solo pueden tener una etapa.
                                        </p>
                                    </div>
                                )}
                            </div>
                            <div className="grid gap-4 sm:grid-cols-2">
                                <div className="space-y-2">
                                    <Label htmlFor="origin">Origen</Label>
                                    <Input id="origin" value={data.origin} onChange={(e) => setData('origin', e.target.value)} />
                                    {errors.origin && <p className="text-sm text-destructive">{errors.origin}</p>}
                                </div>
                                <div className="space-y-2">
                                    <Label htmlFor="destination">Destino</Label>
                                    <Input id="destination" value={data.destination} onChange={(e) => setData('destination', e.target.value)} />
                                    {errors.destination && <p className="text-sm text-destructive">{errors.destination}</p>}
                                </div>
                            </div>
                            <div className="space-y-2">
                                <Label htmlFor="profile_image">URL de imagen de perfil</Label>
                                <Input
                                    id="profile_image"
                                    type="url"
                                    placeholder="https://ejemplo.com/perfil-etapa.jpg"
                                    value={data.profile_image}
                                    onChange={(e) => setData('profile_image', e.target.value)}
                                />
                                {(errors as any).profile_image && <p className="text-sm text-destructive">{(errors as any).profile_image}</p>}
                                {data.profile_image && (
                                    <div className="mt-2 overflow-hidden rounded-lg border">
                                        <img
                                            src={data.profile_image}
                                            alt="Vista previa"
                                            className="max-h-32 w-full object-cover"
                                            onError={(e) => { (e.target as HTMLImageElement).style.display = 'none'; }}
                                        />
                                    </div>
                                )}
                            </div>
                            <div className="space-y-2">
                                <Label htmlFor="live_stream_url">URL de retransmisión en vivo</Label>
                                <Input
                                    id="live_stream_url"
                                    type="url"
                                    placeholder="https://play.hbomax.com/video/watch-sport/..."
                                    value={data.live_stream_url}
                                    onChange={(e) => setData('live_stream_url', e.target.value)}
                                />
                                {(errors as any).live_stream_url && <p className="text-sm text-destructive">{(errors as any).live_stream_url}</p>}
                                <p className="text-xs text-muted-foreground">
                                    URL del directo (HBO Max, etc.). Si se rellena, se mostrará un botón en la etapa.
                                </p>
                            </div>
                            {stage && (
                                <div className="space-y-2">
                                    <Label htmlFor="status">Estado</Label>
                                    <Select value={data.status} onValueChange={(v) => v && setData('status', v)}>
                                        <SelectTrigger><SelectValue /></SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="upcoming">Próxima</SelectItem>
                                            <SelectItem value="ongoing">En curso</SelectItem>
                                            <SelectItem value="finished">Finalizada</SelectItem>
                                        </SelectContent>
                                    </Select>
                                </div>
                            )}
                        </CardContent>
                        <div className="flex justify-end gap-2 border-t p-4">
                            <Button variant="outline" type="button" asChild>
                                <Link href={route('admin.editions.stages.index', edition.id)}>Cancelar</Link>
                            </Button>
                            <Button type="submit" disabled={processing}>
                                {stage ? 'Guardar cambios' : 'Crear etapa'}
                            </Button>
                        </div>
                    </Card>
                </form>
            </div>
        </AdminLayout>
    );
}
