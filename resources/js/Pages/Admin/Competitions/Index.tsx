import { useState } from 'react';
import AdminLayout from '@/Layouts/AdminLayout';
import { Head, Link, router } from '@inertiajs/react';
import { Card, CardContent } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Plus, Edit, ImageIcon, Trash2 } from 'lucide-react';
import { FlagIcon } from '@/components/ui/flag-icon';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';

interface Competition {
    id: string;
    name: string;
    type: string;
    country_id: string | null;
    active: boolean;
    editions_count: number;
    cover_image: string | null;
    logo_image: string | null;
    coverImageUrl: string | null;
    logoImageUrl: string | null;
}

interface CountryOption {
    value: string;
    label: string;
}

const typeBadgeVariant: Record<string, 'default' | 'secondary' | 'outline' | 'destructive'> = {
    'Gran Vuelta': 'default',
    'Carrera importante': 'secondary',
    Monumento: 'outline',
    Clásica: 'secondary',
    Campeonato: 'outline',
};

export default function Index({ competitions, countries }: { competitions: Competition[]; countries: CountryOption[] }) {
    const [deleteId, setDeleteId] = useState<string | null>(null);

    const countryLabel = (id: string | null) => countries.find((c) => c.value === id)?.label ?? id ?? '—';

    const confirmDelete = () => {
        if (deleteId) {
            router.delete(route('admin.competitions.destroy', deleteId), {
                onSuccess: () => setDeleteId(null),
            });
        }
    };

    const deletionTarget = deleteId ? competitions.find((c) => c.id === deleteId) : null;

    return (
        <AdminLayout>
            <Head title="Competiciones" />

            <div className="space-y-6">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl font-semibold tracking-tight">Competiciones</h1>
                        <p className="text-sm text-muted-foreground">Gestiona las competiciones del sistema</p>
                    </div>
                    <Button asChild>
                        <Link href={route('admin.competitions.create')}>
                            <Plus className="mr-2 h-4 w-4" />
                            Nueva
                        </Link>
                    </Button>
                </div>

                <Card>
                    <CardContent className="p-0">
                        {competitions.length === 0 ? (
                            <div className="flex flex-col items-center justify-center px-4 py-12 text-center">
                                <p className="text-sm text-muted-foreground">No hay competiciones</p>
                            </div>
                        ) : (
                            <div className="divide-y">
                                {competitions.map((competition) => (
                                    <div key={competition.id} className="flex items-center justify-between p-4">
                                        <div className="flex items-center gap-3">
                                            <div className="relative flex h-12 w-12 shrink-0 items-center justify-center overflow-hidden rounded-lg border bg-muted">
                                                {competition.coverImageUrl ? (
                                                    <img src={competition.coverImageUrl} alt="" className="h-full w-full object-cover" />
                                                ) : (
                                                    <ImageIcon className="h-5 w-5 text-muted-foreground" />
                                                )}
                                            </div>
                                            <div className="flex flex-col gap-1">
                                                <div className="flex items-center gap-2">
                                                    <p className="font-medium">{competition.name}</p>
                                                    {competition.logoImageUrl && (
                                                        <img src={competition.logoImageUrl} alt="Logo" className="h-5 w-5 rounded-full object-cover" />
                                                    )}
                                                </div>
                                                <div className="flex items-center gap-2">
                                                    <Badge variant={typeBadgeVariant[competition.type] ?? 'secondary'}>
                                                        {competition.type}
                                                    </Badge>
                                                    <span className="text-sm text-muted-foreground">
                                                        {competition.country_id && <FlagIcon code={competition.country_id} className="mr-1 inline-block h-3 w-4 align-middle rounded-sm" />}{countryLabel(competition.country_id)} · {competition.editions_count} ediciones
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        <div className="flex items-center gap-2">
                                            <Badge variant={competition.active ? 'default' : 'secondary'}>
                                                {competition.active ? 'Activa' : 'Inactiva'}
                                            </Badge>
                                            <Button variant="ghost" size="sm" asChild>
                                                <Link href={route('admin.competitions.edit', competition.id)}>
                                                    <Edit className="h-4 w-4" />
                                                </Link>
                                            </Button>
                                            <Button variant="ghost" size="sm" onClick={() => setDeleteId(competition.id)}>
                                                <Trash2 className="h-4 w-4 text-destructive" />
                                            </Button>
                                            <Button variant="outline" size="sm" asChild>
                                                <Link href={route('admin.competitions.editions.index', competition.id)}>
                                                    Ediciones
                                                </Link>
                                            </Button>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        )}
                    </CardContent>
                </Card>
            </div>

            <Dialog open={deleteId !== null} onOpenChange={(open: boolean) => !open && setDeleteId(null)}>
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>Eliminar competición</DialogTitle>
                        <DialogDescription>
                            ¿Estás seguro de eliminar <strong>{deletionTarget?.name}</strong>?
                            Se eliminarán también todas sus ediciones, etapas, participantes y ligas asociadas.
                            Esta acción no se puede deshacer.
                        </DialogDescription>
                    </DialogHeader>
                    <DialogFooter>
                        <Button variant="outline" onClick={() => setDeleteId(null)}>
                            Cancelar
                        </Button>
                        <Button variant="destructive" onClick={confirmDelete}>
                            Eliminar
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </AdminLayout>
    );
}
