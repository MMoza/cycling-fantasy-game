import { useState } from 'react';
import { usePage } from '@inertiajs/react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Trophy, Users, ChevronDown } from 'lucide-react';
import { LeaderboardList } from './LeaderboardList';
import { buildVisibleLeaderboard, sortLeaderboard, type SortDir, type SortKey, type VisibleItem } from './leaderboard-utils';
import type { LeaderboardEntry } from './types';

interface LeagueLeaderboardProps {
    league_id: string;
    leaderboard: LeaderboardEntry[];
}

/**
 * Clasificación de la liga: tabla en escritorio y tarjetas en móvil.
 * Comparte estado de orden, ventana de visibles y datos entre ambas vistas.
 */
export function LeagueLeaderboard({ league_id, leaderboard }: LeagueLeaderboardProps) {
    const [collapsed, setCollapsed] = useState(false);
    const [showAll, setShowAll] = useState(false);
    const [sortKey, setSortKey] = useState<SortKey>('points');
    const [sortDir, setSortDir] = useState<SortDir>('desc');

    const sortedLeaderboard = sortLeaderboard(leaderboard, sortKey, sortDir);
    const currentUserId = (usePage().props as { auth?: { user?: { id?: string } } })?.auth?.user?.id;

    const items: VisibleItem[] = showAll
        ? sortedLeaderboard.map((entry) => ({ type: 'entry' as const, entry }))
        : buildVisibleLeaderboard(sortedLeaderboard, currentUserId);

    const toggleSort = (key: SortKey) => {
        if (sortKey === key) {
            setSortDir((d) => (d === 'asc' ? 'desc' : 'asc'));
        } else {
            setSortKey(key);
            setSortDir(key === 'rank' ? 'asc' : 'desc');
        }
    };

    const onMobileSortChange = (value: string) => {
        const [key, dir] = value.split(':') as [SortKey, SortDir];
        setSortKey(key);
        setSortDir(dir);
    };

    const sortIndicator = (key: SortKey) =>
        sortKey === key ? <span className="text-[10px]">{sortDir === 'asc' ? '▲' : '▼'}</span> : null;

    return (
        <Card className="border-emerald-200/60 bg-gradient-to-br from-emerald-50 to-white dark:border-emerald-800/30 dark:from-emerald-950/20 dark:to-transparent">
            <CardHeader className="pb-3 px-6 pt-6">
                <div className="flex items-center gap-2">
                    <button
                        type="button"
                        onClick={() => setCollapsed(!collapsed)}
                        className="flex min-w-0 flex-1 items-center gap-2 text-left"
                    >
                        <Trophy className="h-4 w-4 shrink-0 text-emerald-600 dark:text-emerald-400" />
                        <div className="min-w-0">
                            <CardTitle>Clasificación</CardTitle>
                            <p className="text-xs text-muted-foreground">
                                {leaderboard.length} participantes
                            </p>
                        </div>
                        <ChevronDown className={`ml-2 h-4 w-4 shrink-0 text-muted-foreground transition-transform ${collapsed ? '' : 'rotate-180'}`} />
                    </button>

                    {/* Orden en móvil: la tabla de escritorio ordena por cabeceras */}
                    <select
                        aria-label="Ordenar clasificación"
                        className="shrink-0 rounded-md border border-muted-200 bg-muted/50 px-2 py-1.5 text-xs font-medium text-foreground dark:border-muted-800 md:hidden"
                        value={`${sortKey}:${sortDir}`}
                        onChange={(e) => onMobileSortChange(e.target.value)}
                    >
                        <option value="points:desc">Puntos ↓</option>
                        <option value="points:asc">Puntos ↑</option>
                        <option value="rank:asc">Puesto ↑</option>
                        <option value="rank:desc">Puesto ↓</option>
                        <option value="user_name:asc">Nombre A-Z</option>
                        <option value="user_name:desc">Nombre Z-A</option>
                    </select>
                </div>
            </CardHeader>

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
                        <LeaderboardList
                            items={items}
                            hrefFor={(entry) => route('leagues.members.show', [league_id, entry.user_id])}
                            onExpand={() => setShowAll(true)}
                            desktopHeader={
                                <div className="flex items-center gap-3 px-6 py-2 text-xs font-semibold uppercase tracking-wider text-muted-foreground border-b border-muted-200 dark:border-muted-800">
                                    <button type="button" onClick={() => toggleSort('rank')} className="flex w-8 items-center justify-center gap-0.5 hover:text-foreground transition-colors">
                                        Puesto
                                        {sortIndicator('rank')}
                                    </button>
                                    <span className="w-8" />
                                    <button type="button" onClick={() => toggleSort('user_name')} className="flex flex-1 items-center gap-0.5 hover:text-foreground transition-colors text-left">
                                        Usuario
                                        {sortIndicator('user_name')}
                                    </button>
                                    <span className="w-8 text-center">Var.</span>
                                    <button type="button" onClick={() => toggleSort('points')} className="flex shrink-0 items-center gap-0.5 hover:text-foreground transition-colors">
                                        Puntos
                                        {sortIndicator('points')}
                                    </button>
                                </div>
                            }
                        />

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
