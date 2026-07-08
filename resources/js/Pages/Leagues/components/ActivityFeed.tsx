import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Flag, Play, CheckCheck, Award, Activity, Info } from 'lucide-react';
import type { ActivityLog } from './types';

interface ActivityFeedProps {
    activity_logs: ActivityLog[];
}

const activityIcons: Record<string, React.ReactNode> = {
    competition_start: <Flag className="h-4 w-4" />,
    stage_start: <Play className="h-4 w-4" />,
    stage_end: <CheckCheck className="h-4 w-4" />,
    competition_end: <Award className="h-4 w-4" />,
};

const activityColors: Record<string, string> = {
    competition_start: 'bg-blue-100 text-blue-600 dark:bg-blue-900/20 dark:text-blue-400',
    stage_start: 'bg-amber-100 text-amber-600 dark:bg-amber-900/20 dark:text-amber-400',
    stage_end: 'bg-green-100 text-green-600 dark:bg-green-900/20 dark:text-green-400',
    competition_end: 'bg-purple-100 text-purple-600 dark:bg-purple-900/20 dark:text-purple-400',
};

export function ActivityFeed({ activity_logs }: ActivityFeedProps) {
    if (activity_logs.length === 0) return null;

    return (
        <Card className="border-purple-200/60 bg-gradient-to-br from-purple-50 to-white dark:border-purple-800/30 dark:from-purple-950/20 dark:to-transparent">
            <CardHeader className="pb-3 px-6 pt-6">
                <CardTitle className="flex items-center gap-2">
                    <Activity className="h-4 w-4 text-purple-600 dark:text-purple-400" />
                    Actividad
                </CardTitle>
            </CardHeader>
            <CardContent className="px-6 pb-6">
                <div className="relative space-y-0">
                    {activity_logs.map((log, i) => (
                        <div key={log.id} className="flex gap-3 pb-4 last:pb-0">
                            <div className="flex flex-col items-center">
                                <div className={`flex h-7 w-7 items-center justify-center rounded-full ${activityColors[log.type] ?? 'bg-muted text-muted-foreground'}`}>
                                    {activityIcons[log.type] ?? <Info className="h-4 w-4" />}
                                </div>
                                {i < activity_logs.length - 1 && (
                                    <div className="mt-1 h-full w-px bg-border" />
                                )}
                            </div>
                            <div className="flex-1 min-w-0 pt-0.5">
                                <p className="text-sm font-medium">{log.title}</p>
                                {log.description && (
                                    <p className="text-xs text-muted-foreground mt-0.5">{log.description}</p>
                                )}
                                <p className="text-xs text-muted-foreground/60 mt-0.5">{log.created_at}</p>
                            </div>
                        </div>
                    ))}
                </div>
            </CardContent>
        </Card>
    );
}
