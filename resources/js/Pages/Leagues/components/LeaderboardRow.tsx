import { Link } from '@inertiajs/react';
import Avatar from '@/components/Avatar';
import { UserBadges } from '@/components/UserBadges';
import { Trophy } from 'lucide-react';
import { PositionChange } from './PositionChange';
import { WinnerStreakChip } from './WinnerStreakChip';
import type { LeaderboardEntry } from './types';

const podiumTrophy: Record<number, string> = {
    1: 'text-yellow-500',
    2: 'text-gray-400',
    3: 'text-amber-700',
};

/**
 * Fila de participante para escritorio (presentación tabular actual).
 */
export function LeaderboardRow({
    entry,
    leagueId,
}: {
    entry: LeaderboardEntry;
    leagueId: string;
}) {
    return (
        <Link
            href={route('leagues.members.show', [leagueId, entry.user_id])}
            className={`
                flex items-center gap-3 px-6 py-3 transition-colors hover:bg-muted/50
                ${entry.is_current_user
                    ? 'bg-accent-100/50 dark:bg-accent-900/10 border-y border-accent-200 dark:border-accent-800'
                    : 'border-b border-muted-100 dark:border-muted-800/50 last:border-b-0'
                }
            `}
        >
            <div className="flex h-8 w-8 shrink-0 items-center justify-center">
                {entry.rank <= 3 ? (
                    <Trophy className={`h-5 w-5 ${podiumTrophy[entry.rank]}`} />
                ) : (
                    <span className="w-6 text-center text-sm font-medium text-muted-foreground">
                        {entry.rank}º
                    </span>
                )}
            </div>
            <Avatar user={{ name: entry.user_name, avatar: entry.avatar }} size="sm" isOnline={entry.is_online} />
            <div className="flex min-w-0 flex-1 items-center gap-2">
                <span className="truncate text-sm" title={entry.user_name}>
                    {entry.user_name}
                </span>
                <WinnerStreakChip streak={entry.winner_streak} />
                <UserBadges badges={entry.badges} />
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
}
