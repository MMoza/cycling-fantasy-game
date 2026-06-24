import AdminLayout from '@/Layouts/AdminLayout';
import { Head, Link, useForm } from '@inertiajs/react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import SearchableSelect from '@/components/ui/searchable-select';
import { ArrowLeft } from 'lucide-react';

interface Rider {
    id: string;
    first_name: string;
    last_name: string;
    country_id: string | null;
    birth_date: string | null;
    profile_image: string | null;
}

interface CountryOption {
    value: string;
    label: string;
}

export default function Form({ rider, countries }: { rider: Rider | null; countries: CountryOption[] }) {
    const { data, setData, post, patch, processing, errors } = useForm({
        first_name: rider?.first_name ?? '',
        last_name: rider?.last_name ?? '',
        country_id: rider?.country_id ?? '',
        birth_date: rider?.birth_date ?? '',
        profile_image: rider?.profile_image ?? '',
    });

    const submit = (e: React.FormEvent) => {
        e.preventDefault();
        if (rider) {
            patch(route('admin.riders.update', rider.id));
        } else {
            post(route('admin.riders.store'));
        }
    };

    return (
        <AdminLayout>
            <Head title={rider ? 'Editar corredor' : 'Nuevo corredor'} />

            <div className="mx-auto max-w-xl space-y-6">
                <div className="flex items-center gap-4">
                    <Button variant="ghost" size="icon" asChild>
                        <Link href={route('admin.riders.index')}>
                            <ArrowLeft className="h-4 w-4" />
                        </Link>
                    </Button>
                    <div>
                        <h1 className="text-2xl font-semibold tracking-tight">
                            {rider ? 'Editar corredor' : 'Nuevo corredor'}
                        </h1>
                    </div>
                </div>

                <form onSubmit={submit}>
                    <Card>
                        <CardHeader>
                            <CardTitle>Información del corredor</CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div className="grid grid-cols-2 gap-4">
                                <div className="space-y-2">
                                    <Label htmlFor="first_name">Nombre</Label>
                                    <Input id="first_name" value={data.first_name} onChange={(e) => setData('first_name', e.target.value)} />
                                    {errors.first_name && <p className="text-sm text-destructive">{errors.first_name}</p>}
                                </div>
                                <div className="space-y-2">
                                    <Label htmlFor="last_name">Apellido</Label>
                                    <Input id="last_name" value={data.last_name} onChange={(e) => setData('last_name', e.target.value)} />
                                    {errors.last_name && <p className="text-sm text-destructive">{errors.last_name}</p>}
                                </div>
                            </div>
                            <div className="space-y-2">
                                <Label htmlFor="country_id">Nacionalidad</Label>
                                <SearchableSelect
                                    options={countries}
                                    value={data.country_id}
                                    onChange={(v) => setData('country_id', v)}
                                />
                                {errors.country_id && <p className="text-sm text-destructive">{errors.country_id}</p>}
                            </div>
                            <div className="space-y-2">
                                <Label htmlFor="birth_date">Fecha de nacimiento</Label>
                                <Input id="birth_date" type="date" value={data.birth_date} onChange={(e) => setData('birth_date', e.target.value)} />
                                {errors.birth_date && <p className="text-sm text-destructive">{errors.birth_date}</p>}
                            </div>
                            <div className="space-y-2">
                                <Label htmlFor="profile_image">URL de imagen</Label>
                                <Input id="profile_image" value={data.profile_image} onChange={(e) => setData('profile_image', e.target.value)} placeholder="https://..." />
                                {errors.profile_image && <p className="text-sm text-destructive">{errors.profile_image}</p>}
                            </div>
                        </CardContent>
                        <div className="flex justify-end gap-2 border-t p-4">
                            <Button variant="outline" type="button" asChild>
                                <Link href={route('admin.riders.index')}>Cancelar</Link>
                            </Button>
                            <Button type="submit" disabled={processing}>
                                {rider ? 'Guardar cambios' : 'Crear corredor'}
                            </Button>
                        </div>
                    </Card>
                </form>
            </div>
        </AdminLayout>
    );
}
