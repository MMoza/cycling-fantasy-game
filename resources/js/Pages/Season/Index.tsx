import { useState } from 'react';
import AppLayout from '@/Layouts/AppLayout';
import { Head, Link, router, usePage } from '@inertiajs/react';
import { Card, CardContent } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Users, ImageIcon, Check, AlertCircle, UserPlus } from 'lucide-react';
import { FlagIcon } from '@/components/ui/flag-icon';
import SeasonHeader from './components/SeasonHeader';

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
    years: number[];
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
    upcoming: 'bg-blue-600 text-white dark:bg-blue-500 dark:text-white',
    ongoing: 'bg-green-600 text-white dark:bg-green-500 dark:text-white',
    finished: 'bg-gray-500 text-white dark:bg-gray-400 dark:text-gray-900',
};

const typeOrder = ['gc', 'major', 'monument', 'classic', 'championship'];

export default function Index({ year, years, competitions, user_joined_count, total_competitions }: SeasonProps) {
    const [isJoiningAll, setIsJoiningAll] = useState(false);
    const { flash } = usePage().props as {
        flash?: { success?: string; error?: string };
    };

    const handleJoin = (leagueId: string) => {
        router.post(route('leagues.join-official'), { league_id: leagueId }, {
            preserveScroll: true,
        });
    };

    const changeYear = (nextYear: number) => {
        if (nextYear !== year) {
            router.get(route('season.index', nextYear));
        }
    };

    const handleJoinAll = () => {
        setIsJoiningAll(true);
        router.post(
            route('season.join-all'),
            { year },
            {
                preserveScroll: true,
                onFinish: () => setIsJoiningAll(false),
            },
        );
    };

    const pendingCount = competitions.filter((comp) => !comp.isUserMember && comp.canJoin).length;

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
                <SeasonHeader year={year} years={years} onYearChange={changeYear} />

                <div className="space-y-3">
                    {flash?.success && (
                        <div className="flex items-center gap-2 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700 dark:border-green-800/50 dark:bg-green-950/20 dark:text-green-300">
                            <Check className="h-4 w-4 shrink-0" aria-hidden="true" />
                            <span>{flash.success}</span>
                        </div>
                    )}
                    {flash?.error && (
                        <div className="flex items-center gap-2 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-800/50 dark:bg-red-950/20 dark:text-red-300">
                            <AlertCircle className="h-4 w-4 shrink-0" aria-hidden="true" />
                            <span>{flash.error}</span>
                        </div>
                    )}

                    {total_competitions > 0 && (
                        <Card className="border-accent-200 bg-accent-50 dark:border-accent-800/50 dark:bg-accent-950/20">
                            <CardContent className="flex flex-col gap-3 p-4 sm:flex-row sm:items-center sm:justify-between">
                                <div className="flex items-center gap-3">
                                    <div className="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-accent-100 dark:bg-accent-900/30">
                                        {user_joined_count === total_competitions ? (
                                            <Check className="h-5 w-5 text-accent-600 dark:text-accent-400" />
                                        ) : (
                                            <Users className="h-5 w-5 text-accent-600 dark:text-accent-400" />
                                        )}
                                    </div>
                                    <div>
                                        <p className="font-medium">
                                            {user_joined_count === 0
                                                ? `Aún no estás inscrito en ninguna de las ${total_competitions} competiciones`
                                                : `Estás en ${user_joined_count} de ${total_competitions} competiciones`}
                                        </p>
                                        <p className="text-sm text-muted-foreground">
                                            {user_joined_count === total_competitions
                                                ? '¡Participas en todas las competiciones oficiales!'
                                                : pendingCount > 0
                                                  ? 'Únete a más competiciones para mejorar tu posición en la temporada'
                                                  : 'Las competiciones restantes ya han finalizado'}
                                        </p>
                                    </div>
                                </div>

                                {pendingCount > 0 && (
                                    <Button onClick={handleJoinAll} disabled={isJoiningAll} className="w-full gap-2 sm:w-auto">
                                        <UserPlus className="h-4 w-4" aria-hidden="true" />
                                        {isJoiningAll ? 'Inscribiéndote…' : `Inscribirme en las ${pendingCount} que faltan`}
                                    </Button>
                                )}
                            </CardContent>
                        </Card>
                    )}
                </div>

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

                            <div className="flex gap-4 overflow-x-auto pb-2 scrollbar-thin">
                                {comps.map((comp) => (
                                    <Card key={comp.editionId} className="min-w-[280px] max-w-[320px] shrink-0 overflow-hidden">
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
                                                <Badge variant="secondary" className={`text-xs rounded-full ${statusColors[comp.editionStatus] ?? ''}`}>
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
