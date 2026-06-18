import AppLayout from '@/Layouts/AppLayout';
import { Head, Link, useForm } from '@inertiajs/react';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { ArrowLeft } from 'lucide-react';

interface Edition {
    id: string;
    name: string;
    year: number;
    competition: { name: string };
}

interface ScoringSystem {
    id: string;
    name: string;
    description: string;
    type: string;
}

interface CreateProps {
    editions: Edition[];
    scoringSystems: ScoringSystem[];
}

export default function Create({ editions, scoringSystems }: CreateProps) {
    const { data, setData, post, processing, errors } = useForm({
        name: '',
        edition_id: '',
        scoring_system_id: '',
    });

    const submit = (e: React.FormEvent) => {
        e.preventDefault();
        post(route('leagues.store'));
    };

    return (
        <AppLayout>
            <Head title="Crear Liga" />

            <div className="mx-auto max-w-2xl space-y-6">
                <div className="flex items-center gap-4">
                    <Button variant="ghost" size="icon" asChild>
                        <Link href={route('leagues.index')}>
                            <ArrowLeft className="h-4 w-4" />
                        </Link>
                    </Button>
                    <div>
                        <h1 className="text-2xl font-semibold tracking-tight">Crear Liga</h1>
                        <p className="text-sm text-muted-foreground">
                            Configura tu nueva liga de porra ciclista
                        </p>
                    </div>
                </div>

                <form onSubmit={submit}>
                    <Card>
                        <CardHeader>
                            <CardTitle>Información de la liga</CardTitle>
                            <CardDescription>
                                Elige la edición y el sistema de puntuación
                            </CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div className="space-y-2">
                                <Label htmlFor="name">Nombre de la liga</Label>
                                <Input
                                    id="name"
                                    placeholder="Amigos del Tour"
                                    value={data.name}
                                    onChange={(e: React.ChangeEvent<HTMLInputElement>) => setData('name', e.target.value)}
                                />
                                {errors.name && (
                                    <p className="text-sm text-destructive">{errors.name}</p>
                                )}
                            </div>

                            <div className="space-y-2">
                                <Label htmlFor="edition">Edición</Label>
                                <Select
                                    value={data.edition_id}
                                    onValueChange={(value: string | null) => { if (value) setData('edition_id', value); }}
                                >
                                    <SelectTrigger>
                                        <SelectValue placeholder="Selecciona una edición" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {editions.map((edition) => (
                                            <SelectItem key={edition.id} value={edition.id}>
                                                {edition.competition.name} {edition.year}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                                {errors.edition_id && (
                                    <p className="text-sm text-destructive">{errors.edition_id}</p>
                                )}
                            </div>

                            <div className="space-y-2">
                                <Label htmlFor="scoring_system">Sistema de puntuación</Label>
                                <Select
                                    value={data.scoring_system_id}
                                    onValueChange={(value: string | null) => { if (value) setData('scoring_system_id', value); }}
                                >
                                    <SelectTrigger>
                                        <SelectValue placeholder="Selecciona un sistema" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {scoringSystems.map((system) => (
                                            <SelectItem key={system.id} value={system.id}>
                                                {system.name} - {system.description}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                                {errors.scoring_system_id && (
                                    <p className="text-sm text-destructive">{errors.scoring_system_id}</p>
                                )}
                            </div>
                        </CardContent>
                        <CardFooter className="flex justify-end gap-2">
                            <Button variant="outline" type="button" onClick={() => window.history.back()}>
                                Cancelar
                            </Button>
                            <Button type="submit" disabled={processing}>
                                Crear liga
                            </Button>
                        </CardFooter>
                    </Card>
                </form>
            </div>
        </AppLayout>
    );
}
