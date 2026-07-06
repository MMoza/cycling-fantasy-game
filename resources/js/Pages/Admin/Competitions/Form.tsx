import { useState } from 'react';
import AdminLayout from '@/Layouts/AdminLayout';
import { Head, Link, useForm } from '@inertiajs/react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import SearchableSelect from '@/components/ui/searchable-select';
import { ArrowLeft, ImagePlus, Trash2 } from 'lucide-react';

interface Competition {
    id: string;
    name: string;
    type: string;
    country_id: string | null;
    active: boolean;
    cover_image: string | null;
    logo_image: string | null;
    cover_image_url: string | null;
    logo_image_url: string | null;
    pcs_slug: string | null;
}

interface CountryOption {
    value: string;
    label: string;
}

export default function Form({ competition, countries }: { competition: Competition | null; countries: CountryOption[] }) {
    const { data, setData, post, patch, processing, errors } = useForm({
        name: competition?.name ?? '',
        type: competition?.type ?? 'gc',
        country_id: competition?.country_id ?? '',
        active: competition?.active ?? true,
        cover_image: null as File | null,
        logo_image: null as File | null,
        remove_cover_image: false,
        remove_logo_image: false,
        pcs_slug: competition?.pcs_slug ?? '',
    });

    const [coverPreview, setCoverPreview] = useState<string | null>(competition?.cover_image_url ?? null);
    const [logoPreview, setLogoPreview] = useState<string | null>(competition?.logo_image_url ?? null);

    const handleCoverSelect = (e: React.ChangeEvent<HTMLInputElement>) => {
        const file = e.target.files?.[0];
        if (file) {
            setData('cover_image', file);
            setData('remove_cover_image', false);
            setCoverPreview(URL.createObjectURL(file));
        }
    };

    const handleLogoSelect = (e: React.ChangeEvent<HTMLInputElement>) => {
        const file = e.target.files?.[0];
        if (file) {
            setData('logo_image', file);
            setData('remove_logo_image', false);
            setLogoPreview(URL.createObjectURL(file));
        }
    };

    const removeCover = () => {
        setData('cover_image', null);
        setData('remove_cover_image', true);
        setCoverPreview(null);
    };

    const removeLogo = () => {
        setData('logo_image', null);
        setData('remove_logo_image', true);
        setLogoPreview(null);
    };

    const submit = (e: React.FormEvent) => {
        e.preventDefault();
        if (competition) {
            patch(route('admin.competitions.update', competition.id));
        } else {
            post(route('admin.competitions.store'));
        }
    };

    const typeOptions: { value: string; label: string }[] = [
        { value: 'gc', label: 'Gran Vuelta' },
        { value: 'major', label: 'Carrera importante' },
        { value: 'monument', label: 'Monumento' },
        { value: 'classic', label: 'Clásica' },
        { value: 'championship', label: 'Campeonato' },
    ];

    return (
        <AdminLayout>
            <Head title={competition ? 'Editar competición' : 'Nueva competición'} />

            <div className="mx-auto max-w-xl space-y-6">
                <div className="flex items-center gap-4">
                    <Button variant="ghost" size="icon" asChild>
                        <Link href={route('admin.competitions.index')}>
                            <ArrowLeft className="h-4 w-4" />
                        </Link>
                    </Button>
                    <div>
                        <h1 className="text-2xl font-semibold tracking-tight">
                            {competition ? 'Editar competición' : 'Nueva competición'}
                        </h1>
                    </div>
                </div>

                <form onSubmit={submit} encType="multipart/form-data">
                    <Card>
                        <CardHeader>
                            <CardTitle>Información</CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div className="space-y-2">
                                <Label htmlFor="name">Nombre</Label>
                                <Input id="name" value={data.name} onChange={(e) => setData('name', e.target.value)} />
                                {errors.name && <p className="text-sm text-destructive">{errors.name}</p>}
                            </div>

                            <div className="space-y-2">
                                <Label htmlFor="type">Tipo</Label>
                                <Select value={data.type} onValueChange={(v) => v && setData('type', v)}>
                                    <SelectTrigger><SelectValue format={(v) => typeOptions.find(o => o.value === v)?.label ?? String(v)} /></SelectTrigger>
                                    <SelectContent>
                                        {typeOptions.map((o) => (
                                            <SelectItem key={o.value} value={o.value}>{o.label}</SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                                {errors.type && <p className="text-sm text-destructive">{errors.type}</p>}
                            </div>

                            <div className="space-y-2">
                                <Label htmlFor="country_id">País</Label>
                                <SearchableSelect
                                    options={countries}
                                    value={data.country_id}
                                    onChange={(v) => setData('country_id', v)}
                                />
                                {errors.country_id && <p className="text-sm text-destructive">{errors.country_id}</p>}
                            </div>

                            <div className="space-y-2">
                                <Label htmlFor="pcs_slug">PCS Slug</Label>
                                <Input
                                    id="pcs_slug"
                                    value={data.pcs_slug}
                                    onChange={(e) => setData('pcs_slug', e.target.value)}
                                    placeholder="tour-de-france"
                                />
                                <p className="text-xs text-muted-foreground">
                                    Slug de ProcyclingStats para enlaces en vivo (ej: tour-de-france, giro-d-italia)
                                </p>
                                {errors.pcs_slug && <p className="text-sm text-destructive">{errors.pcs_slug}</p>}
                            </div>

                            <div className="space-y-2">
                                <Label>Imagen de portada</Label>
                                <div className="flex items-center gap-4">
                                    <div className="relative flex h-32 w-56 shrink-0 items-center justify-center overflow-hidden rounded-lg border bg-muted">
                                        {coverPreview ? (
                                            <img src={coverPreview} alt="Portada" className="h-full w-full object-cover" />
                                        ) : (
                                            <div className="flex flex-col items-center gap-1 text-muted-foreground">
                                                <ImagePlus className="h-8 w-8" />
                                                <span className="text-xs">Sin imagen</span>
                                            </div>
                                        )}
                                    </div>
                                    <div className="flex flex-col gap-2">
                                        <Label htmlFor="cover_image" className="cursor-pointer">
                                            <div className="rounded-md border bg-background px-3 py-1.5 text-sm transition-colors hover:bg-accent">
                                                Seleccionar
                                            </div>
                                            <Input
                                                id="cover_image"
                                                type="file"
                                                accept="image/jpeg,image/png,image/webp"
                                                className="hidden"
                                                onChange={handleCoverSelect}
                                            />
                                        </Label>
                                        {(coverPreview || competition?.cover_image) && (
                                            <Button type="button" variant="outline" size="sm" onClick={removeCover}>
                                                <Trash2 className="mr-1 h-3 w-3" />
                                                Eliminar
                                            </Button>
                                        )}
                                    </div>
                                </div>
                                {errors.cover_image && <p className="text-sm text-destructive">{errors.cover_image}</p>}
                            </div>

                            <div className="space-y-2">
                                <Label>Logo</Label>
                                <div className="flex items-center gap-4">
                                    <div className="relative flex h-20 w-20 shrink-0 items-center justify-center overflow-hidden rounded-full border bg-muted">
                                        {logoPreview ? (
                                            <img src={logoPreview} alt="Logo" className="h-full w-full object-cover" />
                                        ) : (
                                            <div className="flex flex-col items-center gap-1 text-muted-foreground">
                                                <ImagePlus className="h-6 w-6" />
                                                <span className="text-[10px]">Sin logo</span>
                                            </div>
                                        )}
                                    </div>
                                    <div className="flex flex-col gap-2">
                                        <Label htmlFor="logo_image" className="cursor-pointer">
                                            <div className="rounded-md border bg-background px-3 py-1.5 text-sm transition-colors hover:bg-accent">
                                                Seleccionar
                                            </div>
                                            <Input
                                                id="logo_image"
                                                type="file"
                                                accept="image/jpeg,image/png,image/webp"
                                                className="hidden"
                                                onChange={handleLogoSelect}
                                            />
                                        </Label>
                                        {(logoPreview || competition?.logo_image) && (
                                            <Button type="button" variant="outline" size="sm" onClick={removeLogo}>
                                                <Trash2 className="mr-1 h-3 w-3" />
                                                Eliminar
                                            </Button>
                                        )}
                                    </div>
                                </div>
                                {errors.logo_image && <p className="text-sm text-destructive">{errors.logo_image}</p>}
                            </div>
                        </CardContent>
                        <div className="flex justify-end gap-2 border-t p-4">
                            <Button variant="outline" type="button" asChild>
                                <Link href={route('admin.competitions.index')}>Cancelar</Link>
                            </Button>
                            <Button type="submit" disabled={processing}>
                                {competition ? 'Guardar cambios' : 'Crear competición'}
                            </Button>
                        </div>
                    </Card>
                </form>
            </div>
        </AdminLayout>
    );
}
