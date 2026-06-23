import AdminLayout from '@/Layouts/AdminLayout';
import { Head, Link, useForm } from '@inertiajs/react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { ArrowLeft } from 'lucide-react';

interface Edition {
    id: string;
    year: number;
    start_date: string;
    end_date: string;
    status: string;
}

export default function Form({ competition, edition }: { competition: { id: string; name: string }; edition: Edition | null }) {
    const { data, setData, post, patch, processing, errors } = useForm({
        year: edition?.year ?? new Date().getFullYear(),
        start_date: edition?.start_date ?? '',
        end_date: edition?.end_date ?? '',
        status: edition?.status ?? 'upcoming',
    });

    const submit = (e: React.FormEvent) => {
        e.preventDefault();
        if (edition) {
            patch(route('admin.competitions.editions.update', [competition.id, edition.id]));
        } else {
            post(route('admin.competitions.editions.store', competition.id));
        }
    };

    return (
        <AdminLayout>
            <Head title={edition ? 'Editar edición' : 'Nueva edición'} />

            <div className="mx-auto max-w-xl space-y-6">
                <div className="flex items-center gap-4">
                    <Button variant="ghost" size="icon" asChild>
                        <Link href={route('admin.competitions.editions.index', competition.id)}>
                            <ArrowLeft className="h-4 w-4" />
                        </Link>
                    </Button>
                    <div>
                        <h1 className="text-2xl font-semibold tracking-tight">
                            {edition ? 'Editar edición' : 'Nueva edición'}
                        </h1>
                        <p className="text-sm text-muted-foreground">{competition.name}</p>
                    </div>
                </div>

                <form onSubmit={submit}>
                    <Card>
                        <CardHeader>
                            <CardTitle>Información</CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div className="space-y-2">
                                <Label htmlFor="year">Año</Label>
                                <Input id="year" type="number" value={data.year} onChange={(e) => setData('year', Number(e.target.value))} />
                                {errors.year && <p className="text-sm text-destructive">{errors.year}</p>}
                            </div>
                            <div className="space-y-2">
                                <Label htmlFor="start_date">Fecha inicio</Label>
                                <Input id="start_date" type="date" value={data.start_date} onChange={(e) => setData('start_date', e.target.value)} />
                                {errors.start_date && <p className="text-sm text-destructive">{errors.start_date}</p>}
                            </div>
                            <div className="space-y-2">
                                <Label htmlFor="end_date">Fecha fin</Label>
                                <Input id="end_date" type="date" value={data.end_date} onChange={(e) => setData('end_date', e.target.value)} />
                                {errors.end_date && <p className="text-sm text-destructive">{errors.end_date}</p>}
                            </div>
                            {edition && (
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
                                    {errors.status && <p className="text-sm text-destructive">{errors.status}</p>}
                                </div>
                            )}
                        </CardContent>
                        <div className="flex justify-end gap-2 border-t p-4">
                            <Button variant="outline" type="button" asChild>
                                <Link href={route('admin.competitions.editions.index', competition.id)}>Cancelar</Link>
                            </Button>
                            <Button type="submit" disabled={processing}>
                                {edition ? 'Guardar cambios' : 'Crear edición'}
                            </Button>
                        </div>
                    </Card>
                </form>
            </div>
        </AdminLayout>
    );
}
