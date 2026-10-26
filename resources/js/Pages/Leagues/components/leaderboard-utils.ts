import type { LeaderboardEntry } from './types';

export type SortKey = 'rank' | 'user_name' | 'points';
export type SortDir = 'asc' | 'desc';

export type VisibleItem =
    | { type: 'entry'; entry: LeaderboardEntry }
    | { type: 'ellipsis' }
    | { type: 'separator' };

/**
 * Ordena la clasificación por puesto, nombre o puntos.
 */
export function sortLeaderboard(
    leaderboard: LeaderboardEntry[],
    sortKey: SortKey,
    sortDir: SortDir,
): LeaderboardEntry[] {
    return [...leaderboard].sort((a, b) => {
        let cmp = 0;
        if (sortKey === 'rank') cmp = a.rank - b.rank;
        else if (sortKey === 'user_name') cmp = a.user_name.localeCompare(b.user_name);
        else cmp = a.points - b.points;
        return sortDir === 'asc' ? cmp : -cmp;
    });
}

/**
 * Ventana visible de la clasificación: primeros `maxVisible`, o una ventana
 * alrededor del usuario actual con el líder siempre visible, un separador
 * marcando su fila y "..." si se saltan puestos.
 */
export function buildVisibleLeaderboard(
    leaderboard: LeaderboardEntry[],
    currentUserId: string | undefined,
    maxVisible = 10,
): VisibleItem[] {
    if (leaderboard.length === 0) return [];

    const currentIndex = currentUserId
        ? leaderboard.findIndex((e) => e.user_id === currentUserId)
        : -1;

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

    const result: VisibleItem[] = [{ type: 'entry', entry: leaderboard[0] }];

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
