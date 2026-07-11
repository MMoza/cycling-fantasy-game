import { useState } from 'react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Flag, Play, CheckCheck, Award, Activity, Info, ChevronDown, Lock } from 'lucide-react';
import type { ActivityLog } from './types';

interface ActivityFeedProps {
    activity_logs: ActivityLog[];
}

interface TopRider {
    rider_id: string;
    name: string;
    count: number;
}

const activityIcons: Record<string, React.ReactNode> = {
    competition_start: <Flag className="h-4 w-4" />,
    stage_start: <Play className="h-4 w-4" />,
    stage_end: <CheckCheck className="h-4 w-4" />,
    competition_end: <Award className="h-4 w-4" />,
    predictions_locked: <Lock className="h-4 w-4" />,
};

const activityColors: Record<string, string> = {
    competition_start: 'bg-blue-100 text-blue-600 dark:bg-blue-900/20 dark:text-blue-400',
    stage_start: 'bg-amber-100 text-amber-600 dark:bg-amber-900/20 dark:text-amber-400',
    stage_end: 'bg-green-100 text-green-600 dark:bg-green-900/20 dark:text-green-400',
    competition_end: 'bg-purple-100 text-purple-600 dark:bg-purple-900/20 dark:text-purple-400',
    predictions_locked: 'bg-slate-100 text-slate-600 dark:bg-slate-900/20 dark:text-slate-400',
};

const INITIAL_COUNT = 5;

function getInitials(name: string): string {
    return name.split(' ').slice(0, 2).map((p) => p[0]).join('').toUpperCase();
}

function PredictionsLockedContent({ topRiders }: { topRiders: TopRider[] }) {
    const total = topRiders.reduce((sum, r) => sum + r.count, 0);

    const podiumOrder = topRiders.length >= 3
        ? [topRiders[1], topRiders[0], topRiders[2]]
        : topRiders;

    const podiumSizes = ['h-11 w-11', 'h-14 w-14', 'h-9 w-9'];
    const badgeSizes = ['h-4.5 w-4.5 text-[8px]', 'h-5 w-5 text-[9px]', 'h-4 w-4 text-[8px]'];
    const podiumBg = [
        'bg-slate-400 dark:bg-slate-500',
        'bg-amber-500 dark:bg-amber-600',
        'bg-amber-700 dark:bg-amber-800',
    ];
    const podiumLabels = ['2.º', '1.º', '3.º'];
    const podiumLift = ['mt-3', 'mt-0', 'mt-5'];

    return (
        <div className="flex items-end justify-center gap-4 mt-2">
            {podiumOrder.map((rider, i) => {
                const pct = total > 0 ? Math.round((rider.count / total) * 100) : 0;

                return (
                    <div key={rider.rider_id} className={`flex flex-col items-center gap-1 ${podiumLift[i]}`}>
                        <div className="relative">
                            <div className={`${podiumSizes[i]} ${podiumBg[i]} rounded-full flex items-center justify-center text-white font-semibold shadow-sm`}>
                                {getInitials(rider.name)}
                            </div>
                            <span className={`absolute -bottom-1 -right-1 ${badgeSizes[i]} rounded-full bg-background border-2 border-background text-foreground font-bold flex items-center justify-center leading-none`}>
                                {pct}%
                            </span>
                        </div>
                        <span className="text-[10px] font-bold text-muted-foreground">{podiumLabels[i]}</span>
                        <span className="text-xs text-muted-foreground font-medium truncate max-w-[64px]">
                            {rider.name}
                        </span>
                    </div>
                );
            })}
        </div>
    );
}

export function ActivityFeed({ activity_logs }: ActivityFeedProps) {
    const [collapsed, setCollapsed] = useState(false);
    const [showAll, setShowAll] = useState(false);

    if (activity_logs.length === 0) return null;

    const visibleLogs = showAll ? activity_logs : activity_logs.slice(0, INITIAL_COUNT);
    const hasMore = activity_logs.length > INITIAL_COUNT;

    return (
        <Card className="border-purple-200/60 bg-gradient-to-br from-purple-50 to-white dark:border-purple-800/30 dark:from-purple-950/20 dark:to-transparent">
            <button
                type="button"
                onClick={() => setCollapsed(!collapsed)}
                className="w-full"
            >
                <CardHeader className="pb-3 px-6 pt-6">
                    <CardTitle className="flex items-center gap-2">
                        <Activity className="h-4 w-4 text-purple-600 dark:text-purple-400" />
                        Actividad
                        <ChevronDown className={`ml-auto h-4 w-4 text-muted-foreground transition-transform ${collapsed ? '' : 'rotate-180'}`} />
                    </CardTitle>
                </CardHeader>
            </button>
            {!collapsed && (
                <CardContent className="px-6 pb-6 pt-4 bg-white/80">
                    <div className="relative space-y-0">
                        {visibleLogs.map((log, i) => (
                            <div key={log.id} className="flex gap-3 pb-4 last:pb-0">
                                <div className="flex flex-col items-center">
                                    <div className={`flex h-7 w-7 items-center justify-center rounded-full ${activityColors[log.type] ?? 'bg-muted text-muted-foreground'}`}>
                                        {activityIcons[log.type] ?? <Info className="h-4 w-4" />}
                                    </div>
                                    {i < visibleLogs.length - 1 && (
                                        <div className="mt-1 h-full w-px bg-border" />
                                    )}
                                </div>
                                <div className="flex-1 min-w-0 pt-0.5">
                                    <p className="text-sm font-medium">{log.title}</p>
                                    {log.type === 'predictions_locked' && log.data?.top_riders ? (
                                        <PredictionsLockedContent topRiders={log.data.top_riders as TopRider[]} />
                                    ) : log.description ? (
                                        <p className="text-xs text-muted-foreground mt-0.5">{log.description}</p>
                                    ) : null}
                                    <p className="text-xs text-muted-foreground/60 mt-0.5">{log.created_at}</p>
                                </div>
                            </div>
                        ))}
                    </div>
                    {hasMore && (
                        <div className="border-t border-purple-200/40 dark:border-purple-800/20 mt-4 pt-3">
                            <button
                                type="button"
                                onClick={() => setShowAll(!showAll)}
                                className="flex w-full items-center justify-center gap-1.5 text-sm font-medium text-purple-600 dark:text-purple-400 hover:text-purple-700 dark:hover:text-purple-300 transition-colors"
                            >
                                {showAll ? 'Mostrar menos' : `Ver más (${activity_logs.length})`}
                            </button>
                        </div>
                    )}
                </CardContent>
            )}
        </Card>
    );
}
