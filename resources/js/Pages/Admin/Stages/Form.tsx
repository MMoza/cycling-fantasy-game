import { useState } from 'react';
import AdminLayout from '@/Layouts/AdminLayout';
import { Head, Link, useForm } from '@inertiajs/react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { ArrowLeft } from 'lucide-react';

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
    origin: string;
    destination: string;
    profile_image: string | null;
    status: string;
}

export default function Form({ edition, stage, stageTypes }: { edition: { id: string; year: number; competition: string }; stage: Stage | null; stageTypes: StageType[] }) {
    const { data, setData, post, patch, processing, errors } = useForm({
        number: stage?.number ?? 1,
        name: stage?.name ?? '',
        date: stage?.date ?? '',
        type: stage?.type ?? 'flat',
        distance: stage?.distance ?? '',
        elevation_gain: stage?.elevation_gain ?? '',
        origin: stage?.origin ?? '',
        destination: stage?.destination ?? '',
        status: stage?.status ?? 'upcoming',
    });

    const [profileFile, setProfileFile] = useState<File | null>(null);

    const submit = (e: React.FormEvent) => {
        e.preventDefault();

        const formData = new FormData();
        formData.append('number', String(data.number));
        formData.append('name', data.name);
        formData.append('date', data.date);
        formData.append('type', data.type);
        formData.append('distance', String(data.distance));
        formData.append('elevation_gain', String(data.elevation_gain));
        formData.append('origin', data.origin);
        formData.append('destination', data.destination);
        if (stage) {
            formData.append('_method', 'PATCH');
        }
        if (profileFile) {
            formData.append('profile_image', profileFile);
        }

        if (stage) {
            post(route('admin.editions.stages.update', [edition.id, stage.id]), {
                data: formData,
                headers: { 'Content-Type': 'multipart/form-data' },
            } as any);
        } else {
            post(route('admin.editions.stages.store', edition.id), {
                data: formData,
                headers: { 'Content-Type': 'multipart/form-data' },
            } as any);
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
                                <Label htmlFor="profile_image">Imagen de perfil</Label>
                                <Input
                                    id="profile_image"
                                    type="file"
                                    accept="image/*"
                                    onChange={(e) => setProfileFile(e.target.files?.[0] ?? null)}
                                />
                                {stage?.profile_image && (
                                    <p className="text-xs text-muted-foreground">
                                        Imagen actual: {stage.profile_image}
                                    </p>
                                )}
                                {(errors as any).profile_image && <p className="text-sm text-destructive">{(errors as any).profile_image}</p>}
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
