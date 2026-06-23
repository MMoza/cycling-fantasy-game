import { Head } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Trophy, Users, Medal, TrendingDown } from 'lucide-react';

interface LeaderboardEntry {
    rank: number;
    user_name: string;
    user_id: string;
    points: number;
    is_current_user: boolean;
    behind_leader: number;
}

interface IndexProps {
    league_id: string;
    league_name: string;
    leaderboard: LeaderboardEntry[];
    user_position: {
        rank: string;
        points: number;
        behind_leader: number;
    };
}

export default function Index({ league_name, leaderboard, user_position }: IndexProps) {
    return (
        <AppLayout>
            <Head title={`Clasificación — ${league_name}`} />

            <div className="space-y-6">
                <div>
                    <h1 className="text-2xl font-semibold tracking-tight">Clasificación</h1>
                    <p className="text-sm text-muted-foreground">{league_name}</p>
                </div>

                <div className="grid gap-4 sm:grid-cols-3">
                    <Card>
                        <div className="h-1 rounded-t-xl bg-brand-600" />
                        <CardContent className="flex flex-col items-center justify-center p-4">
                            <Medal className="mb-1 h-5 w-5 text-brand-600" />
                            <span className="text-xs text-muted-foreground">Posición</span>
                            <p className="text-2xl font-bold">{user_position.rank}º</p>
                        </CardContent>
                    </Card>
                    <Card>
                        <div className="h-1 rounded-t-xl bg-accent-500" />
                        <CardContent className="flex flex-col items-center justify-center p-4">
                            <Trophy className="mb-1 h-5 w-5 text-accent-500" />
                            <span className="text-xs text-muted-foreground">Puntos</span>
                            <p className="text-2xl font-bold">{user_position.points}</p>
                        </CardContent>
                    </Card>
                    <Card>
                        <div className="h-1 rounded-t-xl bg-destructive" />
                        <CardContent className="flex flex-col items-center justify-center p-4">
                            <TrendingDown className="mb-1 h-5 w-5 text-destructive" />
                            <span className="text-xs text-muted-foreground">Detrás del líder</span>
                            <p className="text-2xl font-bold">{user_position.behind_leader}</p>
                        </CardContent>
                    </Card>
                </div>

                <Card>
                    <CardHeader className="pb-3">
                        <CardTitle>Tabla de posiciones</CardTitle>
                    </CardHeader>
                    <CardContent>
                        {leaderboard.length === 0 ? (
                            <div className="flex flex-col items-center justify-center py-12 text-center">
                                <Users className="h-12 w-12 text-muted-foreground" />
                                <p className="mt-4 text-sm text-muted-foreground">
                                    Aún no hay puntuaciones registradas
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
