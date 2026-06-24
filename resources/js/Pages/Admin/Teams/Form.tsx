import AdminLayout from '@/Layouts/AdminLayout';
import { Head, Link, useForm } from '@inertiajs/react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import SearchableSelect from '@/components/ui/searchable-select';
import { ArrowLeft } from 'lucide-react';

interface Team {
    id: string;
    name: string;
    abbreviation: string | null;
    country_id: string | null;
    logo_url: string | null;
}

interface CountryOption {
    value: string;
    label: string;
}

export default function Form({ team, countries }: { team: Team | null; countries: CountryOption[] }) {
    const { data, setData, post, patch, processing, errors } = useForm({
        name: team?.name ?? '',
        abbreviation: team?.abbreviation ?? '',
        country_id: team?.country_id ?? '',
        logo_url: team?.logo_url ?? '',
    });

    const submit = (e: React.FormEvent) => {
        e.preventDefault();
        if (team) {
            patch(route('admin.teams.update', team.id));
        } else {
            post(route('admin.teams.store'));
        }
    };

    return (
        <AdminLayout>
            <Head title={team ? 'Editar equipo' : 'Nuevo equipo'} />

            <div className="mx-auto max-w-xl space-y-6">
                <div className="flex items-center gap-4">
                    <Button variant="ghost" size="icon" asChild>
                        <Link href={route('admin.teams.index')}>
                            <ArrowLeft className="h-4 w-4" />
                        </Link>
                    </Button>
                    <div>
                        <h1 className="text-2xl font-semibold tracking-tight">
                            {team ? 'Editar equipo' : 'Nuevo equipo'}
                        </h1>
                    </div>
                </div>

                <form onSubmit={submit}>
                    <Card>
                        <CardHeader>
                            <CardTitle>Información del equipo</CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div className="space-y-2">
                                <Label htmlFor="name">Nombre</Label>
                                <Input id="name" value={data.name} onChange={(e) => setData('name', e.target.value)} />
                                {errors.name && <p className="text-sm text-destructive">{errors.name}</p>}
                            </div>
                            <div className="space-y-2">
                                <Label htmlFor="abbreviation">Abreviatura UCI</Label>
                                <Input
                                    id="abbreviation"
                                    value={data.abbreviation}
                                    onChange={(e) => setData('abbreviation', e.target.value.toUpperCase())}
                                    placeholder="TJV"
                                    maxLength={3}
                                    className="font-mono uppercase"
                                />
                                {errors.abbreviation && <p className="text-sm text-destructive">{errors.abbreviation}</p>}
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
                                <Label htmlFor="logo_url">URL del logo</Label>
                                <Input id="logo_url" value={data.logo_url} onChange={(e) => setData('logo_url', e.target.value)} placeholder="https://..." />
                                {errors.logo_url && <p className="text-sm text-destructive">{errors.logo_url}</p>}
                            </div>
                        </CardContent>
                        <div className="flex justify-end gap-2 border-t p-4">
                            <Button variant="outline" type="button" asChild>
                                <Link href={route('admin.teams.index')}>Cancelar</Link>
                            </Button>
                            <Button type="submit" disabled={processing}>
                                {team ? 'Guardar cambios' : 'Crear equipo'}
                            </Button>
                        </div>
                    </Card>
                </form>
            </div>
        </AdminLayout>
    );
}
