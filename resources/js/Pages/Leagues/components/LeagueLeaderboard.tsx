import { useState } from 'react';
import { Link, usePage } from '@inertiajs/react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import Avatar from '@/components/Avatar';
import { Trophy, Users, ChevronDown } from 'lucide-react';
import { PositionChange } from './PositionChange';
import type { LeaderboardEntry } from './types';

interface LeagueLeaderboardProps {
    league_id: string;
    leaderboard: LeaderboardEntry[];
}

function buildVisibleLeaderboard(
    leaderboard: LeaderboardEntry[],
    currentUserId: string,
): ({ type: 'entry'; entry: LeaderboardEntry } | { type: 'ellipsis' } | { type: 'separator' })[] {
    if (leaderboard.length === 0) return [];

    const maxVisible = 10;
    const currentIndex = leaderboard.findIndex((e) => e.user_id === currentUserId);

    if (currentIndex === -1 || currentIndex < maxVisible) {
        return leaderboard.slice(0, maxVisible).map((entry) => ({ type: 'entry' as const, entry }));
    }

    const windowSize = maxVisible - 1;
    const halfBefore = Math.ceil(windowSize / 2);
    const halfAfter = windowSize - halfBefore;

    let start = currentIndex - halfBefore;
    let end = currentIndex + halfAfter;

    if (start < 1) {
        start = 1;
        end = start + windowSize - 1;
    }
    if (end >= leaderboard.length) {
        end = leaderboard.length - 1;
        start = Math.max(1, end - windowSize + 1);
    }

    const result: ({ type: 'entry'; entry: LeaderboardEntry } | { type: 'ellipsis' } | { type: 'separator' })[] = [];
    result.push({ type: 'entry', entry: leaderboard[0] });

    if (start > 1) {
        result.push({ type: 'ellipsis' });
    }

    for (let i = start; i <= end; i++) {
        if (i === currentIndex) {
            result.push({ type: 'separator' });
        }
        result.push({ type: 'entry', entry: leaderboard[i] });
        if (i === currentIndex) {
            result.push({ type: 'separator' });
        }
    }

    return result;
}

export function LeagueLeaderboard({ league_id, leaderboard }: LeagueLeaderboardProps) {
    const [collapsed, setCollapsed] = useState(false);
    const [showAll, setShowAll] = useState(false);
    const [sortKey, setSortKey] = useState<'rank' | 'user_name' | 'points'>('points');
    const [sortDir, setSortDir] = useState<'asc' | 'desc'>('desc');

    const sortedLeaderboard = [...leaderboard].sort((a, b) => {
        let cmp = 0;
        if (sortKey === 'rank') cmp = a.rank - b.rank;
        else if (sortKey === 'user_name') cmp = a.user_name.localeCompare(b.user_name);
        else cmp = a.points - b.points;
        return sortDir === 'asc' ? cmp : -cmp;
    });

    const toggleSort = (key: typeof sortKey) => {
        if (sortKey === key) {
            setSortDir((d) => (d === 'asc' ? 'desc' : 'asc'));
        } else {
            setSortKey(key);
            setSortDir(key === 'rank' ? 'asc' : 'desc');
        }
    };

    const renderEntry = (entry: LeaderboardEntry) => (
        <Link
            key={entry.user_id}
            href={route('leagues.members.show', [league_id, entry.user_id])}
            className={`
                flex items-center gap-3 px-6 py-3 transition-colors hover:bg-muted/50
                ${entry.is_current_user
                    ? 'bg-accent-100/50 dark:bg-accent-900/10 border-y border-accent-200 dark:border-accent-800'
                    : 'border-b border-muted-100 dark:border-muted-800/50 last:border-b-0'
                }
            `}
        >
            <div className="flex h-8 w-8 shrink-0 items-center justify-center">
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
            <Avatar user={{ name: entry.user_name, avatar: entry.avatar }} size="sm" />
            <div className="flex min-w-0 flex-1 items-center gap-2">
                <span className="truncate text-sm">
                    {entry.user_name}
                </span>
                {entry.is_current_user && (
                    <span className="shrink-0 text-xs text-muted-foreground">(tú)</span>
                )}
            </div>
            <div className="flex items-center gap-3">
                <PositionChange change={entry.rank_change} />
                <span className="text-sm font-medium tabular-nums">
                    {entry.points}
                </span>
            </div>
        </Link>
    );

    return (
        <Card className="border-emerald-200/60 bg-gradient-to-br from-emerald-50 to-white dark:border-emerald-800/30 dark:from-emerald-950/20 dark:to-transparent">
            <button
                type="button"
                onClick={() => setCollapsed(!collapsed)}
                className="w-full"
            >
                <CardHeader className="pb-3 px-6 pt-6">
                    <CardTitle className="flex items-center gap-2">
                        <Trophy className="h-4 w-4 text-emerald-600 dark:text-emerald-400" />
                        Clasificación
                        <ChevronDown className={`ml-auto h-4 w-4 text-muted-foreground transition-transform ${collapsed ? '' : 'rotate-180'}`} />
                    </CardTitle>
                </CardHeader>
            </button>
            {!collapsed && (
            <CardContent className="p-0 bg-white/80">
                {leaderboard.length === 0 ? (
                    <div className="flex flex-col items-center justify-center py-12 text-center px-6">
                        <Users className="h-12 w-12 text-muted-foreground" />
                        <p className="mt-4 text-sm text-muted-foreground">
                            Aún no hay participantes
                        </p>
                    </div>
                ) : (
                    <div>
                        <div className="flex items-center gap-3 px-6 py-2 text-xs font-semibold uppercase tracking-wider text-muted-foreground border-b border-muted-200 dark:border-muted-800">
                            <button type="button" onClick={() => toggleSort('rank')} className="flex w-8 items-center justify-center gap-0.5 hover:text-foreground transition-colors">
                                Puesto
                                {sortKey === 'rank' && <span className="text-[10px]">{sortDir === 'asc' ? '▲' : '▼'}</span>}
                            </button>
                            <span className="w-8" />
                            <button type="button" onClick={() => toggleSort('user_name')} className="flex flex-1 items-center gap-0.5 hover:text-foreground transition-colors text-left">
                                Usuario
                                {sortKey === 'user_name' && <span className="text-[10px]">{sortDir === 'asc' ? '▲' : '▼'}</span>}
                            </button>
                            <span className="w-8 text-center">Var.</span>
                            <button type="button" onClick={() => toggleSort('points')} className="flex shrink-0 items-center gap-0.5 hover:text-foreground transition-colors">
                                Puntos
                                {sortKey === 'points' && <span className="text-[10px]">{sortDir === 'asc' ? '▲' : '▼'}</span>}
                            </button>
                        </div>
                        {showAll ? (
                            sortedLeaderboard.map(renderEntry)
                        ) : (
                            buildVisibleLeaderboard(sortedLeaderboard, (usePage().props as any)?.auth?.user?.id).map((item, i) => {
                                if (item.type === 'ellipsis') {
                                    return (
                                        <div key={`ellipsis-${i}`} className="flex items-center justify-center py-2 text-muted-foreground">
                                            <span className="text-sm tracking-widest">...</span>
                                        </div>
                                    );
                                }
                                if (item.type === 'separator') {
                                    return (
                                        <div key={`sep-${i}`} className="border-t border-muted-200 dark:border-muted-800" />
                                    );
                                }
                                return renderEntry(item.entry);
                            })
                        )}
                        {leaderboard.length > 10 && (
                            <div className="border-t border-muted-200 dark:border-muted-800 px-6 py-3">
                                <button
                                    type="button"
                                    onClick={() => setShowAll(!showAll)}
                                    className="flex w-full items-center justify-center gap-1.5 text-sm font-medium text-muted-foreground hover:text-foreground transition-colors"
                                >
                                    {showAll ? 'Mostrar menos' : `Ver todos (${leaderboard.length})`}
                                </button>
                            </div>
                        )}
                    </div>
                )}
            </CardContent>
            )}
        </Card>
    );
}
