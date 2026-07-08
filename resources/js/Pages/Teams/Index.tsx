import { useState } from 'react';
import AppLayout from '@/Layouts/AppLayout';
import { Head, Link } from '@inertiajs/react';
import { Card, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Users, Search, ChevronRight } from 'lucide-react';
import { FlagIcon } from '@/components/ui/flag-icon';
import { CompetitionHeader } from '@/components/CompetitionHeader';
import BackLink from '@/components/BackLink';

interface TeamRider {
    id: string;
    firstName: string;
    lastName: string;
    fullName: string;
    countryId: string | null;
    countryName: string | null;
    profileImageUrl: string | null;
    age: number | null;
}

interface TeamData {
    id: string;
    name: string;
    abbreviation: string | null;
    countryId: string | null;
    countryName: string | null;
    logoUrl: string | null;
    riders: TeamRider[];
}

interface TeamsProps {
    league_id: string;
    league_name: string;
    competition_name: string;
    year: number;
    teams: TeamData[];
    total_teams: number;
    total_riders: number;
}

export default function Index({ league_id, league_name, competition_name, year, teams, total_teams, total_riders }: TeamsProps) {
    const [search, setSearch] = useState('');
    const [expandedTeamId, setExpandedTeamId] = useState<string | null>(null);

    const filtered = teams.filter((team) => {
        if (!search) return true;
        const q = search.toLowerCase();
        return (
            team.name.toLowerCase().includes(q) ||
            team.abbreviation?.toLowerCase().includes(q) ||
            team.riders.some((r) => r.fullName.toLowerCase().includes(q))
        );
    });

    return (
        <AppLayout>
            <Head title={`Equipos — ${competition_name} ${year}`} />

            <CompetitionHeader
                competitionName={competition_name}
                year={year}
                leagueName={league_name}
            />

            <div className="space-y-6 px-4 py-6 sm:px-0">
                <div>
                    <BackLink href={route('leagues.show', league_id)} label="Volver a la liga" />
                    <h1 className="mt-3 text-2xl font-semibold tracking-tight">Equipos</h1>
                    <p className="text-sm text-muted-foreground">
                        {total_teams} equipos · {total_riders} ciclistas
                    </p>
                </div>

                <div className="relative">
                    <Search className="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                    <Input
                        type="search"
                        placeholder="Buscar equipo o ciclista..."
                        value={search}
                        onChange={(e) => setSearch(e.target.value)}
                        className="pl-9"
                    />
                </div>

                <div className="space-y-2">
                    {filtered.map((team) => (
                        <div key={team.id} className="rounded-xl bg-gradient-to-br from-slate-50 to-white transition-colors hover:from-slate-100/70 dark:border-slate-700/30 dark:from-slate-900/30 dark:to-transparent dark:hover:from-slate-800/40">
                            <button
                                type="button"
                                onClick={() => setExpandedTeamId(expandedTeamId === team.id ? null : team.id)}
                                className="flex w-full items-center gap-3 p-4 text-left"
                            >
                                <div className="flex h-10 w-10 shrink-0 items-center justify-center overflow-hidden rounded-lg border border-border/50 bg-muted/50">
                                    {team.logoUrl ? (
                                        <img src={team.logoUrl} alt="" className="h-full w-full object-contain p-0.5" />
                                    ) : (
                                        <span className="text-xs font-bold text-muted-foreground">
                                            {team.abbreviation ?? team.name.substring(0, 2).toUpperCase()}
                                        </span>
                                    )}
                                </div>
                                <div className="min-w-0 flex-1">
                                    <span className="block truncate text-sm font-semibold">{team.name}</span>
                                    <div className="flex items-center gap-1.5 text-xs text-muted-foreground">
                                        {team.countryId && (
                                            <FlagIcon code={team.countryId} className="inline-block h-3 w-4 rounded-sm" />
                                        )}
                                        <span>{team.riders.length} ciclistas</span>
                                    </div>
                                </div>
                                <ChevronRight className={`h-4 w-4 text-muted-foreground transition-transform ${expandedTeamId === team.id ? 'rotate-90' : ''}`} />
                            </button>

                            {expandedTeamId === team.id && (
                                <div className="border-t border-border/40 px-4 py-3">
                                    <div className="grid grid-cols-1 gap-1 sm:grid-cols-2">
                                        {team.riders.map((rider) => (
                                            <Link
                                                key={rider.id}
                                                href={route('leagues.riders.show', [league_id, rider.id])}
                                                className="flex items-center gap-2.5 rounded-lg px-3 py-2 transition-colors hover:bg-muted/50"
                                            >
                                                <div className="flex h-8 w-8 shrink-0 items-center justify-center overflow-hidden rounded-full bg-muted">
                                                    {rider.profileImageUrl ? (
                                                        <img src={rider.profileImageUrl} alt="" className="h-full w-full object-cover object-top" />
                                                    ) : (
                                                        <span className="text-[10px] font-bold text-muted-foreground">
                                                            {rider.lastName.charAt(0)}
                                                        </span>
                                                    )}
                                                </div>
                                                <div className="min-w-0 flex-1">
                                                    <span className="block truncate text-sm font-medium">{rider.fullName}</span>
                                                    <div className="flex items-center gap-1.5 text-xs text-muted-foreground">
                                                        {rider.countryId && (
                                                            <FlagIcon code={rider.countryId} className="inline-block h-2.5 w-3.5 rounded-sm" />
                                                        )}
                                                        {rider.age && <span>{rider.age} años</span>}
                                                    </div>
                                                </div>
                                            </Link>
                                        ))}
                                    </div>
                                </div>
                            )}
                        </div>
                    ))}
                </div>

                {filtered.length === 0 && (
                    <Card className="bg-gradient-to-br from-slate-50 to-white dark:from-slate-900/30 dark:to-transparent">
                        <CardContent className="flex flex-col items-center justify-center px-6 py-12 text-center">
                            <Users className="h-12 w-12 text-muted-foreground" />
                            <h3 className="mt-4 text-lg font-medium">Sin resultados</h3>
                            <p className="mt-2 text-sm text-muted-foreground">
                                No se encontraron equipos o ciclistas con "{search}"
                            </p>
                        </CardContent>
                    </Card>
                )}
            </div>
        </AppLayout>
    );
}
