import AdminLayout from '@/Layouts/AdminLayout';
import { Head, Link, useForm } from '@inertiajs/react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { ArrowLeft } from 'lucide-react';

interface Competition {
    id: string;
    name: string;
    type: string;
    country: string;
    active: boolean;
}

export default function Form({ competition }: { competition: Competition | null }) {
    const { data, setData, post, patch, processing, errors } = useForm({
        name: competition?.name ?? '',
        type: competition?.type ?? 'grand_tour',
        country: competition?.country ?? '',
        active: competition?.active ?? true,
    });

    const submit = (e: React.FormEvent) => {
        e.preventDefault();
        if (competition) {
            patch(route('admin.competitions.update', competition.id));
        } else {
            post(route('admin.competitions.store'));
        }
    };

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

                <form onSubmit={submit}>
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
                                    <SelectTrigger><SelectValue /></SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="grand_tour">Grand Tour</SelectItem>
                                        <SelectItem value="one_week">Vuelta de una semana</SelectItem>
                                        <SelectItem value="classic">Clásica</SelectItem>
                                    </SelectContent>
                                </Select>
                                {errors.type && <p className="text-sm text-destructive">{errors.type}</p>}
                            </div>
                            <div className="space-y-2">
                                <Label htmlFor="country">País</Label>
                                <Input id="country" value={data.country} onChange={(e) => setData('country', e.target.value)} />
                                {errors.country && <p className="text-sm text-destructive">{errors.country}</p>}
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
