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

const podiumBg: Record<number, string> = {
    1: 'border-yellow-200/70 bg-yellow-50/70 dark:border-yellow-800/40 dark:bg-yellow-950/20',
    2: 'border-gray-200/70 bg-gray-50/70 dark:border-gray-800/40 dark:bg-gray-950/20',
    3: 'border-amber-200/70 bg-amber-50/60 dark:border-amber-800/40 dark:bg-amber-950/20',
};

/**
 * Tarjeta de participante para móvil: puesto, avatar, nombre con racha,
 * badges (+N), variación de posición y puntuación.
 * Con `href` enlaza al perfil del miembro; sin él, es un contenedor simple.
 */
export function LeaderboardCard({
    entry,
    href,
}: {
    entry: LeaderboardEntry;
    href?: string;
}) {
    const isPodium = entry.rank <= 3;

    const className = `
        flex items-center gap-2 rounded-xl border px-2.5 py-2.5 transition-colors
        ${entry.is_current_user
            ? 'border-accent-400 bg-accent-100/60 dark:border-accent-700 dark:bg-accent-900/20'
            : isPodium
                ? podiumBg[entry.rank]
                : 'border-muted-200/70 bg-white dark:border-muted-800/50 dark:bg-background'
        }
    `;

    const content = (
        <>
            {/* Puesto */}
            <div className="flex w-8 shrink-0 flex-col items-center">
                {isPodium ? (
                    <>
                        <Trophy className={`h-5 w-5 ${podiumTrophy[entry.rank]}`} />
                        <span className={`text-xs font-bold ${podiumTrophy[entry.rank]}`}>{entry.rank}º</span>
                    </>
                ) : (
                    <span className="text-sm font-medium text-muted-foreground">{entry.rank}º</span>
                )}
            </div>

            {/* Avatar */}
            <Avatar
                user={{ name: entry.user_name, avatar: entry.avatar }}
                size="md"
                isOnline={entry.is_online}
            />

            {/* Nombre + variación (misma fila), racha y badges */}
            <div className="flex min-w-0 flex-1 flex-col gap-1">
                <div data-testid="entry-name-row" className="flex min-w-0 items-center gap-1.5">
                    <span className="min-w-0 flex-1 truncate text-sm font-semibold leading-tight sm:text-base" title={entry.user_name}>
                        {entry.user_name}
                    </span>
                    {entry.is_current_user && (
                        <span className="shrink-0 rounded-full bg-accent-500 px-1.5 py-0.5 text-[10px] font-bold uppercase text-white">
                            Tú
                        </span>
                    )}
                    <WinnerStreakChip streak={entry.winner_streak} />
                    <span className="shrink-0">
                        <PositionChange change={entry.rank_change} />
                    </span>
                </div>
                {entry.badges && entry.badges.length > 0 && (
                    <div className="flex items-center">
                        <UserBadges badges={entry.badges} max={3} />
                    </div>
                )}
            </div>

            {/* Puntuación */}
            <div className="flex w-14 shrink-0 flex-col items-end border-l border-muted-200/70 pl-2.5 dark:border-muted-800/50">
                <span className="text-lg font-bold leading-none tabular-nums sm:text-xl">{entry.points}</span>
                <span className="mt-0.5 text-[10px] text-muted-foreground">puntos</span>
            </div>
        </>
    );

    if (href === undefined) {
        return <div className={className}>{content}</div>;
    }

    return (
        <Link href={href} className={className}>
            {content}
        </Link>
    );
}
