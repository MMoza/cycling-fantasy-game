import { useState } from 'react';
import { Head, Link } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Route, ChevronDown } from 'lucide-react';
import { StageLeaderboard } from '@/Pages/Leagues/components/StageLeaderboard';
import { LeagueLeaderboard } from '@/Pages/Leagues/components/LeagueLeaderboard';

interface LeaderboardEntry {
    rank: number;
    user_name: string;
    user_id: string;
    points: number;
    is_current_user: boolean;
    behind_leader: number;
    previous_rank: number | null;
    rank_change: number | null;
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
    user_id: string;
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
    user_position?: { rank: number | string; points: number; behind_leader: number };
}

export default function Index({
    league_id,
    league_name,
    stages,
    general_leaderboard,
    stage_leaderboards,
    last_scored_stage_id,
    general_details,
}: IndexProps) {
    const scoredStages = stages.filter((s) => s.has_scores);
    const defaultTab = scoredStages.length > 0 ? 'stages' : 'general';
    const [activeTab, setActiveTab] = useState<'general' | 'stages'>(defaultTab);
    const [collapsedCategories, setCollapsedCategories] = useState<Record<string, boolean>>({});

    const toggleCategory = (category: string) => {
        setCollapsedCategories((prev) => ({ ...prev, [category]: !prev[category] }));
    };

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
                        <LeagueLeaderboard
                            league_id={league_id}
                            leaderboard={general_leaderboard}
                        />

                        {general_details.map((detail) => {
                            const isCollapsed = collapsedCategories[detail.category] ?? false;
                            return (
                                <Card key={detail.category} className="border-emerald-200/60 bg-gradient-to-br from-emerald-50 to-white dark:border-emerald-800/30 dark:from-emerald-950/20 dark:to-transparent">
                                    <button
                                        type="button"
                                        onClick={() => toggleCategory(detail.category)}
                                        className="w-full"
                                    >
                                        <CardHeader className="pb-3 px-6 pt-6">
                                            <CardTitle className="flex items-center gap-2 text-base">
                                                {detail.label}
                                                <ChevronDown className={`ml-auto h-4 w-4 text-muted-foreground transition-transform ${isCollapsed ? '' : 'rotate-180'}`} />
                                            </CardTitle>
                                        </CardHeader>
                                    </button>
                                    {!isCollapsed && (
                                        <CardContent className="p-0 bg-white/80">
                                            <div className="px-6 py-3 border-b border-muted-200 dark:border-muted-800">
                                                <p className="text-xs font-semibold uppercase tracking-wider text-muted-foreground mb-2">Resultado real</p>
                                                <div className="flex flex-wrap gap-x-4 gap-y-1">
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

                                            <div className="flex items-center gap-3 px-6 py-2 text-xs font-semibold uppercase tracking-wider text-muted-foreground border-b border-muted-200 dark:border-muted-800">
                                                <span className="flex-1">Usuario</span>
                                                <span className="hidden sm:block w-48 text-center">Predicción</span>
                                                <span className="shrink-0 w-16 text-right">Puntos</span>
                                            </div>

                                            {detail.users.map((u) => (
                                                <Link
                                                    key={u.user_id}
                                                    href={route('leagues.members.show', [league_id, u.user_id])}
                                                    className={`
                                                        flex items-center gap-3 px-6 py-3 transition-colors hover:bg-muted/50
                                                        ${u.is_current_user
                                                            ? 'bg-accent-100/50 dark:bg-accent-900/10 border-y border-accent-200 dark:border-accent-800'
                                                            : 'border-b border-muted-100 dark:border-muted-800/50 last:border-b-0'
                                                        }
                                                    `}
                                                >
                                                    <div className="flex min-w-0 flex-1 items-center gap-2">
                                                        <span className={`truncate text-sm ${u.is_current_user ? 'font-semibold' : ''}`}>
                                                            {u.user_name}
                                                        </span>
                                                        {u.is_current_user && (
                                                            <span className="shrink-0 text-xs text-muted-foreground">(tú)</span>
                                                        )}
                                                    </div>
                                                    {u.predicted && (
                                                        <span className="hidden sm:block w-48 text-center text-xs text-muted-foreground truncate">
                                                            {u.predicted}
                                                        </span>
                                                    )}
                                                    <span className={`shrink-0 w-16 text-right text-sm font-medium tabular-nums ${
                                                        u.points > 0 ? 'text-green-600' : 'text-muted-foreground'
                                                    }`}>
                                                        {u.points > 0 ? `+${u.points}` : '0'}
                                                    </span>
                                                </Link>
                                            ))}
                                        </CardContent>
                                    )}
                                </Card>
                            );
                        })}
                    </div>
                ) : (
                    <>
                        {scoredStages.length > 0 && (
                            <div className="flex gap-2 overflow-x-auto pb-2">
                                {scoredStages.map((stage) => (
                                    <button
                                        key={stage.id}
                                        onClick={() => setSelectedStageId(stage.id)}
                                        className={`inline-flex shrink-0 items-center gap-1.5 rounded-full px-3 py-1.5 text-xs font-medium transition-colors ${
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

                        <StageLeaderboard
                            league_id={league_id}
                            title={selectedStageNumber ? `Puntuación etapa ${selectedStageNumber}` : 'Puntuación de etapa'}
                            leaderboard={selectedStageLeaderboard?.leaderboard ?? []}
                            emptyMessage="Aún no hay puntuaciones para esta etapa"
                        />
                    </>
                )}
            </div>
        </AppLayout>
    );
}
