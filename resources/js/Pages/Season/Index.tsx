import AppLayout from '@/Layouts/AppLayout';
import { Head, Link, router } from '@inertiajs/react';
import { Card, CardContent } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Users, Trophy, ImageIcon, Check, Calendar } from 'lucide-react';
import { FlagIcon } from '@/components/ui/flag-icon';

interface SeasonCompetition {
    editionId: string;
    competitionId: string;
    competitionName: string;
    competitionType: string;
    typeLabel: string;
    countryId: string | null;
    countryName: string | null;
    coverImageUrl: string | null;
    logoImageUrl: string | null;
    editionStatus: string;
    officialLeagueId: string;
    memberCount: number;
    isUserMember: boolean;
    canJoin: boolean;
    year: number;
}

interface SeasonProps {
    year: number;
    competitions: SeasonCompetition[];
    user_joined_count: number;
    total_competitions: number;
}

const typeIcons: Record<string, string> = {
    gc: '🏔️',
    major: '⭐',
    monument: '🏛️',
    classic: '🚴',
    championship: '🏆',
};

const statusLabels: Record<string, string> = {
    upcoming: 'Próxima',
    ongoing: 'En curso',
    finished: 'Finalizada',
};

const statusColors: Record<string, string> = {
    upcoming: 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300',
    ongoing: 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300',
    finished: 'bg-gray-100 text-gray-800 dark:bg-gray-900/30 dark:text-gray-300',
};

const typeOrder = ['gc', 'major', 'monument', 'classic', 'championship'];

export default function Index({ year, competitions, user_joined_count, total_competitions }: SeasonProps) {
    const handleJoin = (leagueId: string) => {
        router.post(route('leagues.join-official'), { league_id: leagueId }, {
            preserveScroll: true,
        });
    };

    const groupedByType = competitions.reduce<Record<string, SeasonCompetition[]>>((acc, comp) => {
        if (!acc[comp.competitionType]) {
            acc[comp.competitionType] = [];
        }
        acc[comp.competitionType].push(comp);
        return acc;
    }, {});

    const sortedGroups = Object.entries(groupedByType).sort(
        ([a], [b]) => typeOrder.indexOf(a) - typeOrder.indexOf(b)
    );

    return (
        <AppLayout>
            <Head title={`Temporada ${year}`} />

            <div className="space-y-6">
                <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <div className="flex items-center gap-2">
                            <Calendar className="h-6 w-6 text-accent-500" />
                            <h1 className="text-2xl font-semibold tracking-tight">Temporada {year}</h1>
                        </div>
                        <p className="mt-1 text-sm text-muted-foreground">
                            Competiciones oficiales de la temporada
                        </p>
                    </div>

                    <Link href={route('season.classification')}>
                        <Button variant="outline" className="gap-2">
                            <Trophy className="h-4 w-4" />
                            Clasificación de temporada
                        </Button>
                    </Link>
                </div>

                {user_joined_count > 0 && (
                    <Card className="border-accent-200 bg-accent-50 dark:border-accent-800/50 dark:bg-accent-950/20">
                        <CardContent className="flex items-center gap-3 p-4">
                            <div className="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-accent-100 dark:bg-accent-900/30">
                                <Check className="h-5 w-5 text-accent-600 dark:text-accent-400" />
                            </div>
                            <div>
                                <p className="font-medium">
                                    Estás en {user_joined_count} de {total_competitions} competiciones
                                </p>
                                <p className="text-sm text-muted-foreground">
                                    {user_joined_count === total_competitions
                                        ? '¡Participas en todas las competiciones oficiales!'
                                        : 'Únete a más competiciones para mejorar tu posición en la temporada'}
                                </p>
                            </div>
                        </CardContent>
                    </Card>
                )}

                {competitions.length === 0 ? (
                    <Card>
                        <CardContent className="flex flex-col items-center justify-center px-6 py-16 text-center">
                            <ImageIcon className="h-12 w-12 text-muted-foreground" />
                            <h3 className="mt-4 text-lg font-medium">Sin competiciones</h3>
                            <p className="mt-2 text-sm text-muted-foreground">
                                No hay competiciones oficiales para {year}
                            </p>
                        </CardContent>
                    </Card>
                ) : (
                    sortedGroups.map(([type, comps]) => (
                        <section key={type}>
                            <div className="mb-3 flex items-center gap-2">
                                <span className="text-lg">{typeIcons[type] ?? '🚴'}</span>
                                <h2 className="text-lg font-semibold">{comps[0].typeLabel}</h2>
                                <span className="text-sm text-muted-foreground">({comps.length})</span>
                            </div>

                            <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                                {comps.map((comp) => (
                                    <Card key={comp.editionId} className="overflow-hidden">
                                        <div className="relative flex h-36 items-end bg-muted">
                                            {comp.coverImageUrl ? (
                                                <img
                                                    src={comp.coverImageUrl}
                                                    alt=""
                                                    className="absolute inset-0 h-full w-full object-cover"
                                                />
                                            ) : (
                                                <div className="absolute inset-0 flex items-center justify-center">
                                                    <ImageIcon className="h-10 w-10 text-muted-foreground/40" />
                                                </div>
                                            )}
                                            <div className="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent" />

                                            <div className="relative z-10 flex w-full items-end gap-3 p-4">
                                                <div className="flex h-10 w-10 shrink-0 items-center justify-center overflow-hidden rounded-full border-2 border-white/60 bg-background">
                                                    {comp.logoImageUrl ? (
                                                        <img
                                                            src={comp.logoImageUrl}
                                                            alt=""
                                                            className="h-full w-full object-cover"
                                                        />
                                                    ) : (
                                                        <span className="text-lg">{typeIcons[comp.competitionType] ?? '🚴'}</span>
                                                    )}
                                                </div>
                                                <div className="min-w-0 text-white">
                                                    <p className="font-semibold leading-tight truncate">{comp.competitionName}</p>
                                                    <div className="flex items-center gap-1.5 text-xs text-white/80">
                                                        {comp.countryId && (
                                                            <FlagIcon code={comp.countryId} className="inline-block h-3 w-4 rounded-sm" />
                                                        )}
                                                        {comp.countryName ?? ''}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <CardContent className="space-y-3 p-4">
                                            <div className="flex items-center justify-between">
                                                <Badge variant="outline" className={`text-xs ${statusColors[comp.editionStatus] ?? ''}`}>
                                                    {statusLabels[comp.editionStatus] ?? comp.editionStatus}
                                                </Badge>
                                                <div className="flex items-center gap-1.5 text-xs text-muted-foreground">
                                                    <Users className="h-3.5 w-3.5" />
                                                    <span>{comp.memberCount}</span>
                                                </div>
                                            </div>

                                            {comp.isUserMember ? (
                                                <Link href={route('leagues.show', comp.officialLeagueId)} className="block">
                                                    <Button variant="outline" className="w-full gap-2">
                                                        <Check className="h-4 w-4" />
                                                        Ver liga
                                                    </Button>
                                                </Link>
                                            ) : comp.canJoin ? (
                                                <Button
                                                    variant="default"
                                                    className="w-full gap-2"
                                                    onClick={() => handleJoin(comp.officialLeagueId)}
                                                >
                                                    <Users className="h-4 w-4" />
                                                    Unirse
                                                </Button>
                                            ) : (
                                                <Button variant="secondary" className="w-full" disabled>
                                                    Finalizada
                                                </Button>
                                            )}
                                        </CardContent>
                                    </Card>
                                ))}
                            </div>
                        </section>
                    ))
                )}
            </div>
        </AppLayout>
    );
}
