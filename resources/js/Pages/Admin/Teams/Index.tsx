import AdminLayout from '@/Layouts/AdminLayout';
import { Head, Link } from '@inertiajs/react';
import { Card, CardContent } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Plus, Eye, Edit, Bike } from 'lucide-react';
import { FlagIcon } from '@/components/ui/flag-icon';

interface Team {
    id: string;
    name: string;
    abbreviation: string | null;
    country_id: string | null;
    logo_url: string | null;
    riders_count: number;
}

interface CountryOption {
    value: string;
    label: string;
}

export default function Index({ teams, countries }: { teams: Team[]; countries: CountryOption[] }) {
    const countryLabel = (id: string | null) => countries.find((c) => c.value === id)?.label ?? id ?? '—';

    return (
        <AdminLayout>
            <Head title="Equipos" />

            <div className="space-y-6">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl font-semibold tracking-tight">Equipos</h1>
                        <p className="text-sm text-muted-foreground">Gestiona los equipos ciclistas</p>
                    </div>
                    <Button asChild>
                        <Link href={route('admin.teams.create')}>
                            <Plus className="mr-2 h-4 w-4" />
                            Nuevo equipo
                        </Link>
                    </Button>
                </div>

                <Card>
                    <CardContent className="p-0">
                        {teams.length === 0 ? (
                            <div className="flex flex-col items-center justify-center px-4 py-12 text-center">
                                <Bike className="h-12 w-12 text-muted-foreground" />
                                <p className="mt-4 text-sm text-muted-foreground">No hay equipos</p>
                            </div>
                        ) : (
                            <div className="divide-y">
                                {teams.map((team) => (
                                    <div key={team.id} className="flex items-center justify-between p-4">
                                        <div className="flex items-center gap-3">
                                            {team.logo_url ? (
                                                <img src={team.logo_url} alt="" className="h-8 w-8 rounded-full object-cover" />
                                            ) : (
                                                <div className="flex h-8 w-8 items-center justify-center rounded-full bg-muted">
                                                    <Bike className="h-4 w-4 text-muted-foreground" />
                                                </div>
                                            )}
                                            <div>
                                                <p className="font-medium">{team.name}</p>
                                                <p className="flex items-center g-4 text-sm text-muted-foreground">
                                                    {team.abbreviation && <span className="font-mono font-medium text-foreground/70">{team.abbreviation}</span>}
                                                    {team.abbreviation ? ' · ' : ''}
                                                    {team.country_id && <FlagIcon code={team.country_id} className="mr-1 inline-block h-3 w-4 align-middle rounded-sm" />}
                                                    {countryLabel(team.country_id)} · {team.riders_count} corredores
                                                </p>
                                            </div>
                                        </div>
                                        <div className="flex items-center gap-2">
                                            <Button variant="ghost" size="sm" asChild>
                                                <Link href={route('admin.teams.edit', team.id)}>
                                                    <Edit className="h-4 w-4" />
                                                </Link>
                                            </Button>
                                            <Button variant="outline" size="sm" asChild>
                                                <Link href={route('admin.teams.show', team.id)}>
                                                    <Eye className="mr-1 h-3 w-3" />
                                                    Ver
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
        </AdminLayout>
    );
}
