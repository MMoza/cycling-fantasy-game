import { Head } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Trophy, Users } from 'lucide-react';

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

                {/* User position card */}
                <Card>
                    <CardContent className="flex items-center justify-around p-4">
                        <div className="text-center">
                            <span className="text-xs text-muted-foreground">Posición</span>
                            <p className="text-2xl font-bold">{user_position.rank}º</p>
                        </div>
                        <div className="text-center">
                            <span className="text-xs text-muted-foreground">Puntos</span>
                            <p className="text-2xl font-bold">{user_position.points}</p>
                        </div>
                        <div className="text-center">
                            <span className="text-xs text-muted-foreground">Detrás del líder</span>
                            <p className="text-2xl font-bold">{user_position.behind_leader}</p>
                        </div>
                    </CardContent>
                </Card>

                {/* Leaderboard */}
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
                                                ? 'bg-accent'
                                                : 'hover:bg-muted/50'
                                        }`}
                                    >
                                        <div className="flex items-center gap-3">
                                            <div className="flex h-8 w-8 items-center justify-center">
                                                {entry.rank === 1 ? (
                                                    <Trophy className="h-5 w-5 text-yellow-500" />
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
                                            </div>
                                        </div>
                                        <div className="flex items-center gap-4">
                                            {entry.behind_leader > 0 && (
                                                <span className="text-xs text-muted-foreground">
                                                    -{entry.behind_leader}
                                                </span>
                                            )}
                                            <span className="text-sm font-medium">{entry.points} pts</span>
                                        </div>
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
