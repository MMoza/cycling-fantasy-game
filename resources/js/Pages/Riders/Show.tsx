import AppLayout from '@/Layouts/AppLayout';
import { Head } from '@inertiajs/react';
import { CompetitionHeader } from '@/components/CompetitionHeader';
import BackLink from '@/components/BackLink';

interface RiderProps {
    id: string;
    first_name: string;
    last_name: string;
    full_name: string;
    country_id: string | null;
    country_name: string | null;
    age: number | null;
    birth_date: string | null;
    team: { id: string; name: string } | null;
    league_id: string;
}

export default function Show({ id, full_name, country_name, age, team, league_id }: RiderProps) {
    return (
        <AppLayout>
            <Head title={full_name} />

            <div className="space-y-6 px-4 py-6 sm:px-0">
                <BackLink href={route('leagues.teams', league_id)} label="Volver a equipos" />

                <div className="space-y-1">
                    <h1 className="text-2xl font-semibold tracking-tight">{full_name}</h1>
                    {team && <p className="text-sm text-muted-foreground">{team.name}</p>}
                </div>

                <div className="grid grid-cols-2 gap-4 sm:grid-cols-3">
                    {country_name && (
                        <div>
                            <span className="text-xs font-medium text-muted-foreground">País</span>
                            <p className="text-sm">{country_name}</p>
                        </div>
                    )}
                    {age && (
                        <div>
                            <span className="text-xs font-medium text-muted-foreground">Edad</span>
                            <p className="text-sm">{age} años</p>
                        </div>
                    )}
                    {team && (
                        <div>
                            <span className="text-xs font-medium text-muted-foreground">Equipo</span>
                            <p className="text-sm">{team.name}</p>
                        </div>
                    )}
                </div>
            </div>
        </AppLayout>
    );
}
