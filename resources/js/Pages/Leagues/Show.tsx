import AppLayout from '@/Layouts/AppLayout';
import { Head, Link } from '@inertiajs/react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Trophy, Calendar, Route, ChevronRight, Users, Target, Mountain } from 'lucide-react';

interface League {
    id: string;
    name: string;
    invite_code: string;
    competition: {
        name: string;
        year: number;
    };
    scoring_system: {
        name: string;
    };
    progress: {
        current_stage: number;
        total_stages: number;
    };
}

interface Stage {
    id: string;
    number: number;
    name: string;
    date: string;
    type: string;
    distance: string | null;
    status: string;
}

interface LeaderboardEntry {
    rank: number;
    user_id: string;
    user_name: string;
    points: number;
    behind_leader: number;
    is_current_user: boolean;
}

interface ShowProps {
    league: League;
    next_stage: {
        number: number;
        name: string;
        date: string;
        type: string;
        distance: string | null;
        origin: string;
        destination: string;
    } | null;
    user_position: {
        rank: string;
        points: string;
        behind_leader: string;
    };
    stages: Stage[];
    leaderboard: LeaderboardEntry[];
}

export default function Show({ league, next_stage, user_position, stages, leaderboard }: ShowProps) {
    return (
        <AppLayout>
            <Head title={league.name} />

            <div className="mx-auto max-w-2xl space-y-6 px-4 py-6 sm:px-0">
                <div>
                    <h1 className="text-2xl font-semibold tracking-tight">{league.name}</h1>
                    <p className="text-sm text-muted-foreground">
                        {league.competition.name} {league.competition.year} · {league.scoring_system.name}
                    </p>
                </div>

                <div className="grid gap-4 md:grid-cols-3">
                    <Card>
                        <div className="h-1 rounded-t-xl bg-brand-600" />
                        <CardContent className="flex flex-col items-center justify-center p-6">
                            <Route className="mb-2 h-5 w-5 text-brand-600" />
                            <div className="text-2xl font-bold">
                                {league.progress.current_stage}/{league.progress.total_stages}
                            </div>
                            <p className="mt-1 text-sm text-muted-foreground">
                                {league.competition.name} {league.competition.year}
                            </p>
                        </CardContent>
                    </Card>

                    <Card>
                        <div className="h-1 rounded-t-xl bg-accent-500" />
                        <CardContent className="flex flex-col items-center justify-center p-6">
                            <Calendar className="mb-2 h-5 w-5 text-accent-500" />
                            {next_stage ? (
                                <>
                                    <div className="text-lg font-bold">
                                        Etapa {next_stage.number}
                                    </div>
                                    <p className="mt-1 text-sm text-muted-foreground">
                                        {next_stage.type} · {next_stage.distance}
                                    </p>
                                    <p className="text-xs text-muted-foreground">
                                        {next_stage.date}
                                    </p>
                                </>
                            ) : (
                                <>
                                    <div className="text-lg font-bold">-</div>
                                    <p className="mt-1 text-sm text-muted-foreground">
                                        No hay etapas pendientes
                                    </p>
                                </>
                            )}
                        </CardContent>
                    </Card>

                    <Card>
                        <div className="h-1 rounded-t-xl bg-green-600" />
                        <CardContent className="flex flex-col items-center justify-center p-6">
                            <Trophy className="mb-2 h-5 w-5 text-green-600" />
                            <div className="text-2xl font-bold">
                                {user_position.rank}º
                            </div>
                            <p className="mt-1 text-sm text-muted-foreground">
                                {user_position.points} pts · {user_position.behind_leader}
                            </p>
                        </CardContent>
                    </Card>
                </div>

                <Link href={route('predictions.pre-race', league.id)} className="block">
                    <Card className="cursor-pointer transition-colors hover:bg-muted/50">
                        <CardContent className="flex items-center gap-3 p-4">
                            <div className="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-accent-100 dark:bg-accent-900/20">
                                <Target className="h-5 w-5 text-accent-500" />
                            </div>
                            <div className="flex-1">
                                <p className="font-medium">Pronósticos pre-carrera</p>
                                <p className="text-sm text-muted-foreground">
                                    Top 5 GC, maillots y supercombativo
                                </p>
                            </div>
                            <ChevronRight className="h-5 w-5 text-muted-foreground" />
                        </CardContent>
                    </Card>
                </Link>

                {next_stage && (
                    <Card>
                        <CardHeader className="pb-3 px-6 pt-6">
                            <CardTitle className="flex items-center gap-2">
                                <Mountain className="h-4 w-4 text-brand-600" />
                                Próxima etapa
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="px-6 pb-6">
                            <Link
                                href={route('stages.show', [league.id, stages.find(s => s.number === next_stage.number)?.id ?? ''])}
                                className="flex items-center justify-between rounded-lg border p-4 transition-colors hover:bg-muted/50"
                            >
                                <div className="flex items-center gap-4">
                                    <Badge
                                        variant="outline"
                                        className="flex h-8 w-8 items-center justify-center rounded-full p-0"
                                    >
                                        {next_stage.number}
                                    </Badge>
                                    <div>
                                        <p className="font-medium">{next_stage.name}</p>
                                        <p className="text-sm text-muted-foreground">
                                            {next_stage.type} · {next_stage.distance}
                                        </p>
                                    </div>
                                </div>
                                <div className="flex items-center gap-3">
                                    <span className="text-sm text-muted-foreground">{next_stage.date}</span>
                                    <ChevronRight className="h-4 w-4 text-muted-foreground" />
                                </div>
                            </Link>
                        </CardContent>
                    </Card>
                )}

                <Card>
                    <CardHeader className="pb-3 px-6 pt-6">
                        <CardTitle>Clasificación</CardTitle>
                    </CardHeader>
                    <CardContent className="px-6 pb-6">
                        {leaderboard.length === 0 ? (
                            <div className="flex flex-col items-center justify-center py-12 text-center">
                                <Users className="h-12 w-12 text-muted-foreground" />
                                <p className="mt-4 text-sm text-muted-foreground">
                                    Aún no hay participantes
                                </p>
                            </div>
                        ) : (
                            <div className="space-y-2">
                                {leaderboard.map((entry) => (
                                    <div
                                        key={entry.user_id}
                                        className={`flex items-center justify-between rounded-lg p-3 ${
                                            entry.is_current_user
                                                ? 'bg-accent-100/50 dark:bg-accent-900/10 border border-accent-200 dark:border-accent-800'
                                                : 'hover:bg-muted/50'
                                        }`}
                                    >
                                        <div className="flex items-center gap-3">
                                            <div className="flex h-8 w-8 items-center justify-center">
                                                {entry.rank === 1 ? (
                                                    <Trophy className="h-5 w-5 text-yellow-500" />
                                                ) : entry.rank === 2 ? (
                                                    <Trophy className="h-5 w-5 text-gray-400" />
                                                ) : entry.rank === 3 ? (
                                                    <Trophy className="h-5 w-5 text-amber-700" />
                                                ) : (
                                                    <span className="w-6 text-center text-sm font-medium text-muted-foreground">
                                                        {entry.rank}º
                                                    </span>
                                                )}
                                            </div>
                                            <div>
                                                <span className={`text-sm ${entry.is_current_user ? 'font-semibold' : ''}`}>
                                                    {entry.user_name}
                                                    {entry.is_current_user && (
                                                        <span className="ml-2 text-xs text-muted-foreground">(tú)</span>
                                                    )}
                                                </span>
                                                {entry.behind_leader > 0 && (
                                                    <span className="ml-3 text-xs text-muted-foreground">
                                                        -{entry.behind_leader} pts
                                                    </span>
                                                )}
                                            </div>
                                        </div>
                                        <span className="text-sm font-medium">{entry.points} pts</span>
                                    </div>
                                ))}
                            </div>
                        )}
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}
