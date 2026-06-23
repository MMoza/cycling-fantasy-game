import AdminLayout from '@/Layouts/AdminLayout';
import { Head, Link, useForm } from '@inertiajs/react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { ArrowLeft } from 'lucide-react';

interface Team {
    id: string;
    name: string;
    country: string | null;
    logo_url: string | null;
}

export default function Form({ team }: { team: Team | null }) {
    const { data, setData, post, patch, processing, errors } = useForm({
        name: team?.name ?? '',
        country: team?.country ?? '',
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
                                <Label htmlFor="country">País</Label>
                                <Input id="country" value={data.country} onChange={(e) => setData('country', e.target.value)} placeholder="España" />
                                {errors.country && <p className="text-sm text-destructive">{errors.country}</p>}
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
