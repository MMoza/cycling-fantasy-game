import AppLayout from '@/Layouts/AppLayout';
import { Head, Link, useForm } from '@inertiajs/react';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { ArrowLeft, Lock, Globe2 } from 'lucide-react';

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
        max_players: 20,
        is_public: false,
    });

    const submit = (e: React.FormEvent) => {
        e.preventDefault();
        post(route('leagues.store'));
    };

    return (
        <AppLayout>
            <Head title="Crear Liga" />

            <div className="mx-auto max-w-2xl space-y-6 px-4 py-4 sm:px-0 sm:py-8">
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
                    <Card className="p-0">
                        <CardHeader className="p-6 pb-4">
                            <CardTitle>Información de la liga</CardTitle>
                            <CardDescription>
                                Elige la competición, el sistema de puntuación y la configuración inicial
                            </CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-6 px-6 pb-6">
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
                                <Label htmlFor="edition">Competición</Label>
                                <Select
                                    value={data.edition_id}
                                    onValueChange={(value: string | null) => { if (value) setData('edition_id', value); }}
                                >
                                    <SelectTrigger className="w-full">
                                        <SelectValue
                                            placeholder="Selecciona una competición"
                                            format={(value: unknown) => {
                                                const edition = editions.find(e => e.id === value);
                                                return edition ? `${edition.competition.name} ${edition.year}` : String(value);
                                            }}
                                        />
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
                                <p className="text-xs text-muted-foreground">
                                    Cada competición tiene asociada su edición activa.
                                </p>
                            </div>

                            <div className="space-y-2">
                                <Label htmlFor="scoring_system">Sistema de puntuación</Label>
                                <Select
                                    value={data.scoring_system_id}
                                    onValueChange={(value: string | null) => { if (value) setData('scoring_system_id', value); }}
                                >
                                    <SelectTrigger className="w-full">
                                        <SelectValue
                                            placeholder="Selecciona un sistema"
                                            format={(value: unknown) => {
                                                const system = scoringSystems.find(s => s.id === value);
                                                return system ? `${system.name} - ${system.description}` : String(value);
                                            }}
                                        />
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
                                <p className="text-xs text-muted-foreground">
                                    Más adelante podrás gestionar estos sistemas desde SuperAdmin.
                                </p>
                            </div>

                            <div className="grid gap-4 sm:grid-cols-2">
                                <div className="space-y-2">
                                    <Label htmlFor="max_players">Máximo de jugadores</Label>
                                    <Input
                                        id="max_players"
                                        type="number"
                                        min={2}
                                        max={200}
                                        value={data.max_players}
                                        onChange={(e: React.ChangeEvent<HTMLInputElement>) => setData('max_players', Number(e.target.value))}
                                    />
                                    {errors.max_players && (
                                        <p className="text-sm text-destructive">{errors.max_players}</p>
                                    )}
                                </div>

                                <div className="space-y-2">
                                    <Label>Visibilidad</Label>
                                    <div className="grid grid-cols-2 gap-2">
                                        <button
                                            type="button"
                                            onClick={() => setData('is_public', false)}
                                            className={`flex h-10 items-center justify-center gap-2 rounded-lg border text-sm transition-colors ${
                                                !data.is_public
                                                    ? 'border-foreground bg-foreground text-background'
                                                    : 'border-input hover:bg-muted'
                                            }`}
                                        >
                                            <Lock className="h-4 w-4" />
                                            Privada
                                        </button>
                                        <button
                                            type="button"
                                            onClick={() => setData('is_public', true)}
                                            className={`flex h-10 items-center justify-center gap-2 rounded-lg border text-sm transition-colors ${
                                                data.is_public
                                                    ? 'border-foreground bg-foreground text-background'
                                                    : 'border-input hover:bg-muted'
                                            }`}
                                        >
                                            <Globe2 className="h-4 w-4" />
                                            Pública
                                        </button>
                                    </div>
                                    {errors.is_public && (
                                        <p className="text-sm text-destructive">{errors.is_public}</p>
                                    )}
                                </div>
                            </div>
                        </CardContent>
                        <CardFooter className="flex justify-end gap-2 px-6 py-4">
                            <Button variant="outline" type="button" onClick={() => window.history.back()}>
                                Cancelar
                            </Button>
                            <Button type="submit" disabled={processing}>
                                {processing ? 'Creando...' : 'Crear liga'}
                            </Button>
                        </CardFooter>
                        {errors && 'plan' in errors && (
                            <div className="px-6 pb-4">
                                <p className="text-sm text-destructive">{(errors as Record<string, string>).plan}</p>
                            </div>
                        )}
                    </Card>
                </form>
            </div>
        </AppLayout>
    );
}
