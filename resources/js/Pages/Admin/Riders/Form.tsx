import AdminLayout from '@/Layouts/AdminLayout';
import { Head, Link, useForm } from '@inertiajs/react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { ArrowLeft } from 'lucide-react';

interface Rider {
    id: string;
    name: string;
    nationality: string | null;
    birth_date: string | null;
}

export default function Form({ rider }: { rider: Rider | null }) {
    const { data, setData, post, patch, processing, errors } = useForm({
        name: rider?.name ?? '',
        nationality: rider?.nationality ?? '',
        birth_date: rider?.birth_date ?? '',
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
                            <div className="space-y-2">
                                <Label htmlFor="name">Nombre</Label>
                                <Input id="name" value={data.name} onChange={(e) => setData('name', e.target.value)} />
                                {errors.name && <p className="text-sm text-destructive">{errors.name}</p>}
                            </div>
                            <div className="space-y-2">
                                <Label htmlFor="nationality">Nacionalidad</Label>
                                <Input id="nationality" value={data.nationality} onChange={(e) => setData('nationality', e.target.value)} placeholder="España" />
                                {errors.nationality && <p className="text-sm text-destructive">{errors.nationality}</p>}
                            </div>
                            <div className="space-y-2">
                                <Label htmlFor="birth_date">Fecha de nacimiento</Label>
                                <Input id="birth_date" type="date" value={data.birth_date} onChange={(e) => setData('birth_date', e.target.value)} />
                                {errors.birth_date && <p className="text-sm text-destructive">{errors.birth_date}</p>}
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
