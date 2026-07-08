import { useState } from 'react';
import { Head, Link } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Trophy, Users, Calendar } from 'lucide-react';

interface BreakdownEntry {
    competition_name: string;
    edition_id: string;
    league_id: string;
    points: number;
}

interface AggregatedLeaderboardEntry {
    rank: number;
    userId: string;
    userName: string;
    avatar: string | null;
    totalPoints: number;
    isCurrentUser: boolean;
    breakdown: BreakdownEntry[];
}

interface CompetitionLeaderboardEntry {
    rank: number;
    userId: string;
    userName: string;
    avatar: string | null;
    points: number;
    isCurrentUser: boolean;
}

interface CompetitionClassification {
    editionId: string;
    competitionName: string;
    competitionType: string;
    typeLabel: string;
    logoImageUrl: string | null;
    editionStatus: string;
    leagueId: string;
    leaderboard: CompetitionLeaderboardEntry[];
}

interface ClassificationProps {
    year: number;
    aggregated_leaderboard: AggregatedLeaderboardEntry[];
    per_competition: CompetitionClassification[];
}

function AggregatedLeaderboardTable({ leaderboard }: { leaderboard: AggregatedLeaderboardEntry[] }) {
    if (leaderboard.length === 0) {
        return (
            <div className="flex flex-col items-center justify-center py-12 text-center">
                <Users className="h-12 w-12 text-muted-foreground" />
                <p className="mt-4 text-sm text-muted-foreground">Aún no hay puntuaciones en la temporada</p>
            </div>
        );
    }

    return (
        <div className="space-y-2">
            {leaderboard.map((entry) => (
                <div
                    key={entry.userId}
                    className={`rounded-lg p-3 ${
                        entry.isCurrentUser
                            ? 'bg-accent-100/50 dark:bg-accent-900/10 border border-accent-200 dark:border-accent-800'
                            : 'hover:bg-muted/50'
                    }`}
                >
                    <div className="flex items-center justify-between">
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
                            <span className={`text-sm ${entry.isCurrentUser ? 'font-semibold' : ''}`}>
                                {entry.userName}
                                {entry.isCurrentUser && (
                                    <span className="ml-2 text-xs text-muted-foreground">(tú)</span>
                                )}
                            </span>
                        </div>
                        <span className="text-sm font-medium">{entry.totalPoints} pts</span>
                    </div>

                    {entry.breakdown.length > 0 && (
                        <div className="mt-2 flex flex-wrap gap-2 pl-11">
                            {entry.breakdown.map((b) => (
                                <span
                                    key={b.league_id}
                                    className="inline-flex items-center gap-1 rounded-full bg-muted px-2 py-0.5 text-xs text-muted-foreground"
                                >
                                    {b.competition_name}: <span className="font-medium">{b.points} pts</span>
                                </span>
                            ))}
                        </div>
                    )}
                </div>
            ))}
        </div>
    );
}

function CompetitionLeaderboardTable({ leaderboard, leagueId }: { leaderboard: CompetitionLeaderboardEntry[]; leagueId: string }) {
    if (leaderboard.length === 0) {
        return (
            <div className="flex flex-col items-center justify-center py-8 text-center">
                <Users className="h-10 w-10 text-muted-foreground" />
                <p className="mt-3 text-sm text-muted-foreground">Sin puntuaciones aún</p>
            </div>
        );
    }

    return (
        <div className="space-y-2">
            {leaderboard.map((entry) => (
                <div
                    key={entry.userId}
                    className={`flex items-center justify-between rounded-lg p-3 ${
                        entry.isCurrentUser
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
                        <Link
                            href={route('leagues.members.show', [leagueId, entry.userId])}
                            className="hover:underline"
                        >
                            <span className={`text-sm ${entry.isCurrentUser ? 'font-semibold' : ''}`}>
                                {entry.userName}
                                {entry.isCurrentUser && (
                                    <span className="ml-2 text-xs text-muted-foreground">(tú)</span>
                                )}
                            </span>
                        </Link>
                    </div>
                    <span className="text-sm font-medium">{entry.points} pts</span>
                </div>
            ))}
        </div>
    );
}

export default function Classification({ year, aggregated_leaderboard, per_competition }: ClassificationProps) {
    const [activeTab, setActiveTab] = useState<'aggregated' | 'per_competition'>('aggregated');
    const [selectedCompetitionId, setSelectedCompetitionId] = useState<string | null>(
        per_competition[0]?.editionId ?? null
    );

    const selectedCompetition = per_competition.find((c) => c.editionId === selectedCompetitionId);

    return (
        <AppLayout>
            <Head title={`Clasificación Temporada ${year}`} />

            <div className="space-y-6">
                <div>
                    <div className="flex items-center gap-2">
                        <Calendar className="h-6 w-6 text-accent-500" />
                        <h1 className="text-2xl font-semibold tracking-tight">Clasificación Temporada {year}</h1>
                    </div>
                    <p className="mt-1 text-sm text-muted-foreground">
                        Ranking global de todas las competiciones oficiales
                    </p>
                </div>

                <div className="flex gap-1 rounded-lg bg-muted p-1">
                    <button
                        onClick={() => setActiveTab('aggregated')}
                        className={`flex-1 rounded-md px-3 py-2 text-sm font-medium transition-colors ${
                            activeTab === 'aggregated'
                                ? 'bg-background text-foreground shadow-sm'
                                : 'text-muted-foreground hover:text-foreground'
                        }`}
                    >
                        General
                    </button>
                    <button
                        onClick={() => setActiveTab('per_competition')}
                        className={`flex-1 rounded-md px-3 py-2 text-sm font-medium transition-colors ${
                            activeTab === 'per_competition'
                                ? 'bg-background text-foreground shadow-sm'
                                : 'text-muted-foreground hover:text-foreground'
                        }`}
                    >
                        Por competición
                    </button>
                </div>

                {activeTab === 'aggregated' ? (
                    <Card>
                        <CardHeader className="pb-3">
                            <CardTitle className="flex items-center gap-2">
                                <Trophy className="h-5 w-5 text-accent-500" />
                                Clasificación General
                            </CardTitle>
                            <p className="text-xs text-muted-foreground">
                                Suma de puntos de todas las competiciones oficiales
                            </p>
                        </CardHeader>
                        <CardContent>
                            <AggregatedLeaderboardTable leaderboard={aggregated_leaderboard} />
                        </CardContent>
                    </Card>
                ) : (
                    <>
                        {per_competition.length > 0 && (
                            <div className="flex flex-wrap gap-2">
                                {per_competition.map((comp) => (
                                    <button
                                        key={comp.editionId}
                                        onClick={() => setSelectedCompetitionId(comp.editionId)}
                                        className={`inline-flex items-center gap-1.5 rounded-full px-3 py-1.5 text-xs font-medium transition-colors ${
                                            selectedCompetitionId === comp.editionId
                                                ? 'bg-brand-600 text-white'
                                                : 'bg-muted text-muted-foreground hover:text-foreground'
                                        }`}
                                    >
                                        {comp.competitionName}
                                    </button>
                                ))}
                            </div>
                        )}

                        {selectedCompetition ? (
                            <Card>
                                <CardHeader className="pb-3">
                                    <CardTitle className="flex items-center gap-2">
                                        {selectedCompetition.logoImageUrl && (
                                            <img
                                                src={selectedCompetition.logoImageUrl}
                                                alt=""
                                                className="h-6 w-6 rounded-full"
                                            />
                                        )}
                                        {selectedCompetition.competitionName}
                                    </CardTitle>
                                    <p className="text-xs text-muted-foreground">
                                        {selectedCompetition.typeLabel} · {selectedCompetition.editionStatus === 'ongoing' ? 'En curso' : selectedCompetition.editionStatus === 'upcoming' ? 'Próxima' : 'Finalizada'}
                                    </p>
                                </CardHeader>
                                <CardContent>
                                    <CompetitionLeaderboardTable
                                        leaderboard={selectedCompetition.leaderboard}
                                        leagueId={selectedCompetition.leagueId}
                                    />
                                </CardContent>
                            </Card>
                        ) : (
                            <Card>
                                <CardContent className="flex flex-col items-center justify-center py-12 text-center">
                                    <Trophy className="h-12 w-12 text-muted-foreground" />
                                    <p className="mt-4 text-sm text-muted-foreground">
                                        No hay competiciones con puntuaciones
                                    </p>
                                </CardContent>
                            </Card>
                        )}
                    </>
                )}
            </div>
        </AppLayout>
    );
}
