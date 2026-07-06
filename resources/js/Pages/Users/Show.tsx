import { useState } from 'react';
import { Head, Link } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';
import Avatar from '@/components/Avatar';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { cn } from '@/lib/utils';
import { ArrowLeft, Target, Medal, ChevronDown, ChevronUp, EyeOff } from 'lucide-react';

interface PredictionItem {
    id: string;
    name: string;
    type: 'rider' | 'team';
}

interface Prediction {
    category: string;
    label: string;
    items: PredictionItem[];
    points: number;
}

interface StageDetail {
    stage_id: string;
    stage_number: number;
    stage_name: string;
    stage_status: string;
    points: number;
    predictions: Prediction[];
}

interface Props {
    league_id: string;
    league_name: string;
    competition_started: boolean;
    has_stage_predictions: boolean;
    user: UserProfile;
    pre_race_predictions: Prediction[];
    stage_details: StageDetail[];
}

interface UserProfile {
    id: string;
    name: string;
    avatar?: string | null;
    rank: number | string;
    points: number;
    behind_leader: number;
}

const CATEGORY_LABELS: Record<string, string> = {
    gc_top_5: 'Top 5 General',
    points_winner: 'Maillot Verde',
    mountains_winner: 'Montaña',
    youth_winner: 'Maillot Blanco',
    teams_winner: 'Equipos',
    super_combativo: 'Supercombativo',
    stage_winner: 'Ganador de etapa',
    stage_second: '2º clasificado',
    stage_third: '3º clasificado',
    stage_leader: 'Líder general',
    stage_combativo: 'Combativo',
};

function RiderAvatar({ name, size = 'xs' }: { name: string; size?: 'xs' | 'sm' }) {
    const sizeClasses = {
        xs: 'h-5 w-5 text-[10px]',
        sm: 'h-6 w-6 text-xs',
    };

    const initials = name
        .split(' ')
        .slice(0, 2)
        .map((p) => p[0])
        .join('')
        .toUpperCase();

    return (
        <div
            className={cn(
                'flex shrink-0 items-center justify-center rounded-full bg-neutral-400 font-medium text-white',
                sizeClasses[size],
            )}
        >
            {initials}
        </div>
    );
}

export default function Show({
    league_id,
    league_name,
    competition_started,
    has_stage_predictions,
    user,
    pre_race_predictions,
    stage_details,
}: Props) {
    const [expandedStage, setExpandedStage] = useState<string | null>(null);
    const [expandedCategories, setExpandedCategories] = useState<Set<string>>(new Set());
    const totalPreRacePoints = pre_race_predictions.reduce((sum, p) => sum + p.points, 0);

    const toggleCategory = (category: string) => {
        setExpandedCategories((prev) => {
            const next = new Set(prev);
            if (next.has(category)) {
                next.delete(category);
            } else {
                next.add(category);
            }
            return next;
        });
    };

    return (
        <AppLayout>
            <Head title={`${user.name} — ${league_name}`} />

            <div className="mx-auto max-w-2xl space-y-6 px-4 py-6 sm:px-0">
                <Link
                    href={route('classification.index', league_id)}
                    className="inline-flex items-center gap-1 text-sm text-muted-foreground hover:text-foreground"
                >
                    <ArrowLeft className="h-4 w-4" />
                    Clasificación
                </Link>

                <Card>
                    <div className="flex items-center gap-4 p-6">
                        <Avatar user={{ name: user.name, avatar: user.avatar }} size="lg" />
                        <div className="min-w-0 flex-1">
                            <h1 className="text-xl font-bold truncate">{user.name}</h1>
                            <div className="mt-2 flex flex-wrap items-center gap-3">
                                <Badge variant="secondary" className="text-sm">
                                    {user.rank}º puesto
                                </Badge>
                                <span className="text-lg font-semibold tabular-nums">{user.points}</span>
                                {user.behind_leader > 0 && (
                                    <span className="text-sm text-muted-foreground">
                                        a {user.behind_leader} pts
                                    </span>
                                )}
                            </div>
                        </div>
                    </div>
                </Card>

                {competition_started && pre_race_predictions.length > 0 && (
                    <Card>
                        <CardHeader className="px-6 pb-3">
                            <CardTitle className="flex items-center gap-2 text-base">
                                <Target className="h-4 w-4" />
                                Pronósticos pre-race
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="px-6 pb-4">
                            <div className="divide-y divide-muted-100 dark:divide-muted-800">
                                {pre_race_predictions.map((p, i) => {
                                    const isMulti = p.items.length > 1;
                                    const isExpanded = expandedCategories.has(p.category);

                                    return (
                                        <div key={i}>
                                            <div className="flex items-center justify-between py-2">
                                                <span className="text-sm text-muted-foreground">
                                                    {CATEGORY_LABELS[p.category] ?? p.category}
                                                </span>
                                                <div className="flex items-center gap-2 text-right ml-4 min-w-0">
                                                    {isMulti ? (
                                                        <button
                                                            onClick={() => toggleCategory(p.category)}
                                                            className="flex items-center gap-1.5 text-right"
                                                        >
                                                            <span className="text-sm font-medium truncate max-w-[160px]">
                                                                {p.label}
                                                            </span>
                                                            {isExpanded ? (
                                                                <ChevronUp className="h-3.5 w-3.5 shrink-0 text-muted-foreground" />
                                                            ) : (
                                                                <ChevronDown className="h-3.5 w-3.5 shrink-0 text-muted-foreground" />
                                                            )}
                                                        </button>
                                                    ) : (
                                                        <div className="flex items-center gap-2">
                                                            {p.items[0]?.type === 'rider' && (
                                                                <RiderAvatar name={p.items[0].name} size="xs" />
                                                            )}
                                                            <span className="text-sm font-medium truncate max-w-[180px]">
                                                                {p.label}
                                                            </span>
                                                        </div>
                                                    )}
                                                    {p.points > 0 && (
                                                        <span className="text-xs font-semibold tabular-nums text-green-600 shrink-0">
                                                            +{p.points}
                                                        </span>
                                                    )}
                                                </div>
                                            </div>
                                            {isMulti && isExpanded && (
                                                <div className="pb-2 pl-4 space-y-1">
                                                    {p.items.map((item, idx) => (
                                                        <div
                                                            key={idx}
                                                            className="flex items-center gap-2 py-0.5"
                                                        >
                                                            <RiderAvatar name={item.name} size="xs" />
                                                            <span className="text-sm text-muted-foreground">
                                                                {item.name}
                                                            </span>
                                                        </div>
                                                    ))}
                                                </div>
                                            )}
                                        </div>
                                    );
                                })}
                            </div>
                            {totalPreRacePoints > 0 && (
                                <div className="mt-3 pt-3 border-t border-muted-200 dark:border-muted-700 flex items-center justify-between">
                                    <span className="text-sm font-semibold">Total pre-race</span>
                                    <span className="text-sm font-semibold tabular-nums">{totalPreRacePoints}</span>
                                </div>
                            )}
                        </CardContent>
                    </Card>
                )}

                {!competition_started && !has_stage_predictions && (
                    <Card>
                        <CardContent className="flex flex-col items-center justify-center py-12 text-center">
                            <EyeOff className="h-10 w-10 text-muted-foreground" />
                            <p className="mt-3 text-sm text-muted-foreground">
                                Los pronósticos se mostrarán cuando la competición comience
                            </p>
                        </CardContent>
                    </Card>
                )}

                {has_stage_predictions && stage_details.length > 0 && (
                    <Card>
                        <CardHeader className="px-6 pb-3">
                            <CardTitle className="flex items-center gap-2 text-base">
                                <Medal className="h-4 w-4" />
                                Pronósticos por etapa
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="p-0">
                            {stage_details.map((stage) => {
                                const isFinished = stage.stage_status === 'finished';

                                return (
                                    <div key={stage.stage_id}>
                                        <button
                                            onClick={() => setExpandedStage(expandedStage === stage.stage_id ? null : stage.stage_id)}
                                            className="flex w-full items-center justify-between gap-2 px-6 py-3 text-left hover:bg-muted/50 border-b border-muted-100 dark:border-muted-800 last:border-b-0"
                                        >
                                            <div className="flex items-center gap-2 min-w-0">
                                                <span className="text-sm font-medium shrink-0">
                                                    Etapa {stage.stage_number}
                                                </span>
                                                <span className="text-sm text-muted-foreground truncate">
                                                    {stage.stage_name}
                                                </span>
                                            </div>
                                            <div className="flex items-center gap-2 shrink-0">
                                                {(stage.points > 0 || isFinished) && (
                                                    <span
                                                        className={cn(
                                                            'text-xs font-semibold tabular-nums shrink-0',
                                                            stage.points > 0 ? 'text-green-600' : 'text-muted-foreground',
                                                        )}
                                                    >
                                                        {stage.points > 0 ? `+${stage.points}` : '0'}
                                                    </span>
                                                )}
                                                {expandedStage === stage.stage_id ? (
                                                    <ChevronUp className="h-4 w-4 text-muted-foreground" />
                                                ) : (
                                                    <ChevronDown className="h-4 w-4 text-muted-foreground" />
                                                )}
                                            </div>
                                        </button>
                                        {expandedStage === stage.stage_id && (
                                            <div className="divide-y divide-muted-100 dark:divide-muted-800 bg-muted/30">
                                                {stage.predictions.length === 0 ? (
                                                    <div className="px-10 py-3 text-sm text-muted-foreground">
                                                        Sin pronósticos para esta etapa
                                                    </div>
                                                ) : (
                                                    stage.predictions.map((p, j) => (
                                                        <div key={j} className="flex items-center justify-between px-10 py-2">
                                                            <span className="text-sm text-muted-foreground">
                                                                {CATEGORY_LABELS[p.category] ?? p.category}
                                                            </span>
                                                            <div className="flex items-center gap-2 text-right ml-4 min-w-0">
                                                                {p.items[0]?.type === 'rider' && (
                                                                    <RiderAvatar name={p.items[0].name} size="xs" />
                                                                )}
                                                                <span className="text-sm font-medium truncate max-w-[150px]">
                                                                    {p.label}
                                                                </span>
                                                                {(p.points > 0 || isFinished) && (
                                                                    <span
                                                                        className={cn(
                                                                            'text-xs font-semibold tabular-nums shrink-0',
                                                                            p.points > 0 ? 'text-green-600' : 'text-muted-foreground',
                                                                        )}
                                                                    >
                                                                        {p.points > 0 ? `+${p.points}` : '0'}
                                                                    </span>
                                                                )}
                                                            </div>
                                                        </div>
                                                    ))
                                                )}
                                            </div>
                                        )}
                                    </div>
                                );
                            })}
                        </CardContent>
                    </Card>
                )}
            </div>
        </AppLayout>
    );
}
