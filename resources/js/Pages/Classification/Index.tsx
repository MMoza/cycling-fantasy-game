import { useState } from 'react';
import { Head } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Trophy, Users, Route, CheckCircle2, XCircle } from 'lucide-react';

interface LeaderboardEntry {
    rank: number;
    user_name: string;
    user_id: string;
    points: number;
    is_current_user: boolean;
    behind_leader: number;
}

interface Stage {
    id: string;
    number: number;
    name: string;
    has_scores: boolean;
}

interface StageLeaderboard {
    stage_id: string;
    stage_number: number;
    leaderboard: LeaderboardEntry[];
}

interface GeneralDetailEntry {
    user_name: string;
    is_current_user: boolean;
    predicted: string | null;
    points: number;
}

interface GeneralDetail {
    category: string;
    label: string;
    actual: { label: string; value: string }[];
    users: GeneralDetailEntry[];
}

interface IndexProps {
    league_id: string;
    league_name: string;
    stages: Stage[];
    general_leaderboard: LeaderboardEntry[];
    stage_leaderboards: StageLeaderboard[];
    last_scored_stage_id: string | null;
    general_details: GeneralDetail[];
}

function LeaderboardTable({ leaderboard, emptyMessage }: { leaderboard: LeaderboardEntry[]; emptyMessage: string }) {
    if (leaderboard.length === 0) {
        return (
            <div className="flex flex-col items-center justify-center py-12 text-center">
                <Users className="h-12 w-12 text-muted-foreground" />
                <p className="mt-4 text-sm text-muted-foreground">{emptyMessage}</p>
            </div>
        );
    }

    return (
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
    );
}

export default function Index({
    league_name,
    stages,
    stage_leaderboards,
    last_scored_stage_id,
    general_details,
}: IndexProps) {
    const scoredStages = stages.filter((s) => s.has_scores);
    const defaultTab = scoredStages.length > 0 ? 'stages' : 'general';
    const [activeTab, setActiveTab] = useState<'general' | 'stages'>(defaultTab);

    const defaultStageId = last_scored_stage_id ?? scoredStages[0]?.id ?? null;
    const [selectedStageId, setSelectedStageId] = useState<string | null>(defaultStageId);

    const selectedStageLeaderboard = stage_leaderboards.find(
        (sl) => sl.stage_id === selectedStageId,
    );

    const selectedStageNumber = stages.find((s) => s.id === selectedStageId)?.number;

    return (
        <AppLayout>
            <Head title={`Clasificación — ${league_name}`} />

            <div className="space-y-6">
                <div>
                    <h1 className="text-2xl font-semibold tracking-tight">Clasificación</h1>
                    <p className="text-sm text-muted-foreground">{league_name}</p>
                </div>

                <div className="flex gap-1 rounded-lg bg-muted p-1">
                    <button
                        onClick={() => setActiveTab('general')}
                        className={`flex-1 rounded-md px-3 py-2 text-sm font-medium transition-colors ${
                            activeTab === 'general'
                                ? 'bg-background text-foreground shadow-sm'
                                : 'text-muted-foreground hover:text-foreground'
                        }`}
                    >
                        General
                    </button>
                    <button
                        onClick={() => setActiveTab('stages')}
                        className={`flex-1 rounded-md px-3 py-2 text-sm font-medium transition-colors ${
                            activeTab === 'stages'
                                ? 'bg-background text-foreground shadow-sm'
                                : 'text-muted-foreground hover:text-foreground'
                        }`}
                    >
                        Etapas
                    </button>
                </div>

                {activeTab === 'general' ? (
                    <div className="space-y-4">
                        {general_details.map((detail) => (
                            <Card key={detail.category}>
                                <CardHeader className="pb-2">
                                    <CardTitle className="text-base">{detail.label}</CardTitle>
                                </CardHeader>
                                <CardContent>
                                    <div className="mb-3 rounded-lg bg-muted p-3">
                                        <p className="text-xs font-medium text-muted-foreground">Resultado real</p>
                                        <div className="mt-1 flex flex-wrap gap-x-4 gap-y-1">
                                            {detail.actual.length > 0 ? (
                                                detail.actual.map((a) => (
                                                    <span key={a.label} className="text-sm">
                                                        {a.label}: <span className="font-medium">{a.value}</span>
                                                    </span>
                                                ))
                                            ) : (
                                                <span className="text-sm text-muted-foreground">Sin resultados</span>
                                            )}
                                        </div>
                                    </div>

                                    <div className="space-y-1.5">
                                        {detail.users.map((u) => (
                                            <div
                                                key={u.user_name}
                                                className={`flex items-center justify-between rounded-md px-3 py-2 text-sm ${
                                                    u.is_current_user
                                                        ? 'bg-accent-100/50 dark:bg-accent-900/10 border border-accent-200 dark:border-accent-800'
                                                        : ''
                                                }`}
                                            >
                                                <div className="flex items-center gap-2 min-w-0">
                                                    <span className={`truncate ${u.is_current_user ? 'font-semibold' : ''}`}>
                                                        {u.user_name}
                                                        {u.is_current_user && (
                                                            <span className="ml-1.5 text-xs text-muted-foreground">(tú)</span>
                                                        )}
                                                    </span>
                                                    {u.predicted && (
                                                        <span className="hidden sm:block text-xs text-muted-foreground truncate max-w-[200px]">
                                                            → {u.predicted}
                                                        </span>
                                                    )}
                                                </div>
                                                <div className="flex items-center gap-2 shrink-0 ml-2">
                                                    {u.predicted && (
                                                        <span className="text-xs text-muted-foreground sm:hidden truncate max-w-[100px]">
                                                            → {u.predicted}
                                                        </span>
                                                    )}
                                                    <span className={`text-xs font-medium tabular-nums ${
                                                        u.points > 0 ? 'text-green-600' : 'text-muted-foreground'
                                                    }`}>
                                                        {u.points > 0 ? `+${u.points}` : '0'} pts
                                                    </span>
                                                </div>
                                            </div>
                                        ))}
                                    </div>
                                </CardContent>
                            </Card>
                        ))}
                    </div>
                ) : (
                    <>
                        {scoredStages.length > 0 && (
                            <div className="flex flex-wrap gap-2">
                                {scoredStages.map((stage) => (
                                    <button
                                        key={stage.id}
                                        onClick={() => setSelectedStageId(stage.id)}
                                        className={`inline-flex items-center gap-1.5 rounded-full px-3 py-1.5 text-xs font-medium transition-colors ${
                                            selectedStageId === stage.id
                                                ? 'bg-brand-600 text-white'
                                                : 'bg-muted text-muted-foreground hover:text-foreground'
                                        }`}
                                    >
                                        <Route className="h-3 w-3" />
                                        Etapa {stage.number}
                                    </button>
                                ))}
                            </div>
                        )}

                        <Card>
                            <CardHeader className="pb-3">
                                <CardTitle>
                                    {selectedStageNumber
                                        ? `Puntuación etapa ${selectedStageNumber}`
                                        : 'Puntuación de etapa'}
                                </CardTitle>
                            </CardHeader>
                            <CardContent>
                                <LeaderboardTable
                                    leaderboard={selectedStageLeaderboard?.leaderboard ?? []}
                                    emptyMessage="Aún no hay puntuaciones para esta etapa"
                                />
                            </CardContent>
                        </Card>
                    </>
                )}
            </div>
        </AppLayout>
    );
}
