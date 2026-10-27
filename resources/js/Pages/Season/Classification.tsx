import { useState } from 'react';
import { Head } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { LeaderboardList } from '@/Pages/Leagues/components/LeaderboardList';
import type { LeaderboardEntry } from '@/Pages/Leagues/components/types';
import type { VisibleItem } from '@/Pages/Leagues/components/leaderboard-utils';
import { Trophy, Users, Mountain, Flag, Landmark, Star, Bike } from 'lucide-react';

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

/**
 * Mapea una entrada de clasificación de temporada al formato compartido
 * de LeaderboardList (la página no duplica presentación).
 */
function toLeaderboardEntry(
    entry: AggregatedLeaderboardEntry | CompetitionLeaderboardEntry,
    points: number,
): LeaderboardEntry {
    return {
        rank: entry.rank,
        user_id: entry.userId,
        user_name: entry.userName,
        avatar: entry.avatar,
        points,
        behind_leader: 0,
        is_current_user: entry.isCurrentUser,
        previous_rank: null,
        rank_change: null,
    };
}

function toItems(entries: LeaderboardEntry[]): VisibleItem[] {
    return entries.map((entry) => ({ type: 'entry' as const, entry }));
}

function BreakdownContent({ breakdown }: { breakdown: BreakdownEntry[] }) {
    const sorted = [...breakdown].sort((a, b) => b.points - a.points);

    return (
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
    );
}

function SelectorChips<T>({
    items,
    selected,
    onSelect,
    keyOf,
    labelOf,
    iconOf,
}: {
    items: T[];
    selected: string | null;
    onSelect: (key: string) => void;
    keyOf: (item: T) => string;
    labelOf: (item: T) => string;
    iconOf?: (item: T) => { icon: typeof Mountain; color: string } | undefined;
}) {
    return (
        <div className="flex gap-2 overflow-x-auto pb-2" data-testid="selector-chips">
            {items.map((item) => {
                const key = keyOf(item);
                const active = selected === key;
                const config = iconOf?.(item);
                const Icon = config?.icon ?? Trophy;
                return (
                    <button
                        key={key}
                        type="button"
                        onClick={() => onSelect(key)}
                        className={`inline-flex shrink-0 items-center gap-1.5 rounded-full px-3 py-1.5 text-xs font-medium transition-colors ${
                            active
                                ? 'bg-brand-600 text-white'
                                : 'bg-muted text-muted-foreground hover:text-foreground'
                        }`}
                    >
                        {config && <Icon className={`h-3.5 w-3.5 ${active ? 'text-white' : config.color}`} />}
                        {labelOf(item)}
                    </button>
                );
            })}
        </div>
    );
}

function EmptyState({ message }: { message: string }) {
    return (
        <div className="flex flex-col items-center justify-center py-12 text-center">
            <Users className="h-12 w-12 text-muted-foreground" />
            <p className="mt-4 text-sm text-muted-foreground">{message}</p>
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

    const breakdownByUser = new Map<string, BreakdownEntry[]>(
        aggregated_leaderboard.map((entry) => [entry.userId, entry.breakdown]),
    );

    const tabs = [
        { key: 'aggregated' as const, label: 'General' },
        { key: 'per_competition' as const, label: 'Por competición' },
        { key: 'by_type' as const, label: 'Por tipo' },
    ];

    return (
        <AppLayout>
            <Head title={`Clasificación Temporada ${year}`} />

            <div className="space-y-6">
                <div>
                    <div className="flex items-center gap-2">
                        <Trophy className="h-6 w-6 text-accent-500" />
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
                            className={`flex-1 rounded-md px-2 py-2 text-xs font-medium transition-colors sm:px-3 sm:text-sm ${
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
                        <CardHeader className="px-4 pt-5 pb-3 sm:px-6">
                            <CardTitle className="flex items-center gap-2 text-lg">
                                <Trophy className="h-5 w-5 text-accent-500" />
                                Clasificación General
                            </CardTitle>
                            <p className="text-xs text-muted-foreground">
                                Suma de puntos de todas las competiciones oficiales · Toca un participante para ver el desglose
                            </p>
                        </CardHeader>
                        <CardContent className="px-2 pt-2 pb-5 sm:px-4">
                            {aggregated_leaderboard.length === 0 ? (
                                <EmptyState message="Aún no hay puntuaciones en la temporada" />
                            ) : (
                                <LeaderboardList
                                    items={toItems(aggregated_leaderboard.map((e) => toLeaderboardEntry(e, e.totalPoints)))}
                                    renderOverlay={(entry) => {
                                        const breakdown = breakdownByUser.get(entry.user_id) ?? [];
                                        return breakdown.length > 0 ? <BreakdownContent breakdown={breakdown} /> : null;
                                    }}
                                />
                            )}
                        </CardContent>
                    </Card>
                )}

                {activeTab === 'per_competition' && (
                    <>
                        {per_competition.length > 0 && (
                            <SelectorChips
                                items={per_competition}
                                selected={selectedCompetitionId}
                                onSelect={setSelectedCompetitionId}
                                keyOf={(comp) => comp.editionId}
                                labelOf={(comp) => comp.competitionName}
                            />
                        )}

                        {selectedCompetition ? (
                            <Card>
                                <CardHeader className="px-4 pt-5 pb-3 sm:px-6">
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
                                <CardContent className="px-2 pt-2 pb-5 sm:px-4">
                                    {selectedCompetition.leaderboard.length === 0 ? (
                                        <EmptyState message="Sin puntuaciones aún" />
                                    ) : (
                                        <LeaderboardList
                                            items={toItems(selectedCompetition.leaderboard.map((e) => toLeaderboardEntry(e, e.points)))}
                                            hrefFor={(entry) => route('leagues.members.show', [selectedCompetition.leagueId, entry.user_id])}
                                        />
                                    )}
                                </CardContent>
                            </Card>
                        ) : (
                            <Card>
                                <CardContent className="px-6 py-12 text-center">
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
                            <SelectorChips
                                items={by_type}
                                selected={selectedType}
                                onSelect={setSelectedType}
                                keyOf={(typeData) => typeData.type}
                                labelOf={(typeData) => typeData.label}
                                iconOf={(typeData) => TYPE_CONFIG[typeData.type]}
                            />
                        )}

                        {selectedTypeData ? (
                            <Card>
                                <CardHeader className="px-4 pt-5 pb-3 sm:px-6">
                                    <CardTitle className="flex items-center gap-2 text-lg">
                                        {(() => {
                                            const config = TYPE_CONFIG[selectedTypeData.type];
                                            const Icon = config?.icon ?? Trophy;
                                            return <Icon className={`h-5 w-5 ${config?.color ?? 'text-accent-500'}`} />;
                                        })()}
                                        {selectedTypeData.label}
                                    </CardTitle>
                                    <p className="text-xs text-muted-foreground">
                                        Puntuación acumulada de todas las competiciones de este tipo · Toca un participante para ver el desglose
                                    </p>
                                </CardHeader>
                                <CardContent className="px-2 pt-2 pb-5 sm:px-4">
                                    {selectedTypeData.leaderboard.length === 0 ? (
                                        <EmptyState message="No hay competiciones de este tipo con puntuaciones" />
                                    ) : (
                                        <LeaderboardList
                                            items={toItems(selectedTypeData.leaderboard.map((e) => toLeaderboardEntry(e, e.totalPoints)))}
                                            renderOverlay={(entry) => {
                                                const breakdown = breakdownByUser.get(entry.user_id) ?? [];
                                                return breakdown.length > 0 ? <BreakdownContent breakdown={breakdown} /> : null;
                                            }}
                                        />
                                    )}
                                </CardContent>
                            </Card>
                        ) : (
                            <Card>
                                <CardContent className="px-6 py-12 text-center">
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
