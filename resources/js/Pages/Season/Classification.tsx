import { useState } from 'react';
import { Head, Link } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Trophy, Users, Calendar, Mountain, Flag, Landmark, Star, Bike } from 'lucide-react';

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

interface TypeClassification {
    type: string;
    label: string;
    totalPoints: number;
    leaderboard: AggregatedLeaderboardEntry[];
}

interface ClassificationProps {
    year: number;
    aggregated_leaderboard: AggregatedLeaderboardEntry[];
    per_competition: CompetitionClassification[];
    by_type: TypeClassification[];
}

const TYPE_CONFIG: Record<string, { icon: typeof Mountain; color: string }> = {
    gc: { icon: Mountain, color: 'text-yellow-500' },
    championship: { icon: Flag, color: 'text-blue-500' },
    monument: { icon: Landmark, color: 'text-purple-500' },
    major: { icon: Star, color: 'text-orange-500' },
    classic: { icon: Bike, color: 'text-green-500' },
};

function BreakdownPopup({ breakdown }: { breakdown: BreakdownEntry[] }) {
    if (breakdown.length === 0) return null;

    const sorted = [...breakdown].sort((a, b) => b.points - a.points);

    return (
        <div className="invisible group-hover:visible absolute left-0 top-full z-50 mt-1 w-72 rounded-lg border bg-background shadow-lg">
            <div className="max-h-60 overflow-y-auto p-3">
                <p className="mb-2 text-xs font-medium text-muted-foreground">Desglose por competición</p>
                <div className="space-y-1.5">
                    {sorted.map((b) => (
                        <div key={b.league_id} className="flex items-center justify-between text-sm">
                            <span className="truncate text-muted-foreground">{b.competition_name}</span>
                            <span className="ml-2 shrink-0 font-medium">{b.points} pts</span>
                        </div>
                    ))}
                </div>
            </div>
        </div>
    );
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
        <div className="space-y-1">
            {leaderboard.map((entry) => (
                <div
                    key={entry.userId}
                    className={`group relative rounded-lg p-3 ${
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

                    <BreakdownPopup breakdown={entry.breakdown} />
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
        <div className="space-y-1">
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

export default function Classification({ year, aggregated_leaderboard, per_competition, by_type }: ClassificationProps) {
    const [activeTab, setActiveTab] = useState<'aggregated' | 'per_competition' | 'by_type'>('aggregated');
    const [selectedCompetitionId, setSelectedCompetitionId] = useState<string | null>(
        per_competition[0]?.editionId ?? null,
    );
    const [selectedType, setSelectedType] = useState<string | null>(by_type[0]?.type ?? null);

    const selectedCompetition = per_competition.find((c) => c.editionId === selectedCompetitionId);
    const selectedTypeData = by_type.find((t) => t.type === selectedType);

    const tabs = [
        { key: 'aggregated' as const, label: 'General' },
        { key: 'per_competition' as const, label: 'Por competición' },
        { key: 'by_type' as const, label: 'Por tipo' },
    ];

    return (
        <AppLayout>
            <Head title={`Clasificación Temporada ${year}`} />

            <div className="space-y-6 px-4 sm:px-6 lg:px-8">
                <div className="pt-6 pb-2">
                    <div className="flex items-center gap-2">
                        <Calendar className="h-6 w-6 text-accent-500" />
                        <h1 className="text-2xl font-semibold tracking-tight">Clasificación Temporada {year}</h1>
                    </div>
                    <p className="mt-1 text-sm text-muted-foreground">
                        Ranking global de todas las competiciones oficiales
                    </p>
                </div>

                <div className="flex gap-1 rounded-lg bg-muted p-1">
                    {tabs.map((tab) => (
                        <button
                            key={tab.key}
                            onClick={() => setActiveTab(tab.key)}
                            className={`flex-1 rounded-md px-3 py-2 text-sm font-medium transition-colors ${
                                activeTab === tab.key
                                    ? 'bg-background text-foreground shadow-sm'
                                    : 'text-muted-foreground hover:text-foreground'
                            }`}
                        >
                            {tab.label}
                        </button>
                    ))}
                </div>

                {activeTab === 'aggregated' && (
                    <Card>
                        <CardHeader className="px-6 pt-5 pb-3">
                            <CardTitle className="flex items-center gap-2 text-lg">
                                <Trophy className="h-5 w-5 text-accent-500" />
                                Clasificación General
                            </CardTitle>
                            <p className="text-xs text-muted-foreground">
                                Suma de puntos de todas las competiciones oficiales · Hover para ver desglose
                            </p>
                        </CardHeader>
                        <CardContent className="px-6 pt-2 pb-5">
                            <AggregatedLeaderboardTable leaderboard={aggregated_leaderboard} />
                        </CardContent>
                    </Card>
                )}

                {activeTab === 'per_competition' && (
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
                                <CardHeader className="px-6 pt-5 pb-3">
                                    <CardTitle className="flex items-center gap-2 text-lg">
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
                                        {selectedCompetition.typeLabel} ·{' '}
                                        {selectedCompetition.editionStatus === 'ongoing'
                                            ? 'En curso'
                                            : selectedCompetition.editionStatus === 'upcoming'
                                              ? 'Próxima'
                                              : 'Finalizada'}
                                    </p>
                                </CardHeader>
                                <CardContent className="px-6 pt-2 pb-5">
                                    <CompetitionLeaderboardTable
                                        leaderboard={selectedCompetition.leaderboard}
                                        leagueId={selectedCompetition.leagueId}
                                    />
                                </CardContent>
                            </Card>
                        ) : (
                            <Card>
                                <CardContent className="px-6 pt-5 pb-5 flex flex-col items-center justify-center py-12 text-center">
                                    <Trophy className="h-12 w-12 text-muted-foreground" />
                                    <p className="mt-4 text-sm text-muted-foreground">
                                        No hay competiciones con puntuaciones
                                    </p>
                                </CardContent>
                            </Card>
                        )}
                    </>
                )}

                {activeTab === 'by_type' && (
                    <>
                        {by_type.length > 0 && (
                            <div className="flex flex-wrap gap-2">
                                {by_type.map((typeData) => {
                                    const config = TYPE_CONFIG[typeData.type];
                                    const Icon = config?.icon ?? Trophy;
                                    return (
                                        <button
                                            key={typeData.type}
                                            onClick={() => setSelectedType(typeData.type)}
                                            className={`inline-flex items-center gap-1.5 rounded-full px-3 py-1.5 text-xs font-medium transition-colors ${
                                                selectedType === typeData.type
                                                    ? 'bg-brand-600 text-white'
                                                    : 'bg-muted text-muted-foreground hover:text-foreground'
                                            }`}
                                        >
                                            <Icon className={`h-3.5 w-3.5 ${selectedType === typeData.type ? 'text-white' : config?.color ?? ''}`} />
                                            {typeData.label}
                                        </button>
                                    );
                                })}
                            </div>
                        )}

                        {selectedTypeData ? (
                            <Card>
                                <CardHeader className="px-6 pt-5 pb-3">
                                    <CardTitle className="flex items-center gap-2 text-lg">
                                        {(() => {
                                            const config = TYPE_CONFIG[selectedTypeData.type];
                                            const Icon = config?.icon ?? Trophy;
                                            return <Icon className={`h-5 w-5 ${config?.color ?? 'text-accent-500'}`} />;
                                        })()}
                                        {selectedTypeData.label}
                                    </CardTitle>
                                    <p className="text-xs text-muted-foreground">
                                        Puntuación acumulada de todas las competiciones de este tipo
                                    </p>
                                </CardHeader>
                                <CardContent className="px-6 pt-2 pb-5">
                                    <AggregatedLeaderboardTable leaderboard={selectedTypeData.leaderboard} />
                                </CardContent>
                            </Card>
                        ) : (
                            <Card>
                                <CardContent className="px-6 pt-5 pb-5 flex flex-col items-center justify-center py-12 text-center">
                                    <Trophy className="h-12 w-12 text-muted-foreground" />
                                    <p className="mt-4 text-sm text-muted-foreground">
                                        No hay competiciones de este tipo con puntuaciones
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
