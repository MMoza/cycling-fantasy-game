import { useState, type ReactNode } from 'react';
import { LeaderboardRow } from './LeaderboardRow';
import { LeaderboardCard } from './LeaderboardCard';
import type { VisibleItem } from './leaderboard-utils';
import type { LeaderboardEntry } from './types';

interface LeaderboardListProps {
    items: VisibleItem[];
    /** Enlace por entrada (perfil de miembro); sin él, la fila no navega */
    hrefFor?: (entry: LeaderboardEntry) => string | undefined;
    /** Overlay por entrada (p.ej. desglose); se muestra al hover o al pulsar */
    renderOverlay?: (entry: LeaderboardEntry) => ReactNode;
    /** Al pulsar "..." de la ventana */
    onExpand?: () => void;
    /** Cabecera de columna de la vista de escritorio */
    desktopHeader?: ReactNode;
}

/**
 * Lista de clasificación responsive: tabla (escritorio) + tarjetas (móvil).
 * Compartida por la clasificación de liga y la de temporada para no duplicar
 * la lógica de presentación.
 */
export function LeaderboardList({
    items,
    hrefFor,
    renderOverlay,
    onExpand,
    desktopHeader,
}: LeaderboardListProps) {
    const [openOverlay, setOpenOverlay] = useState<string | null>(null);

    const renderItems = (view: 'desktop' | 'mobile') =>
        items.map((item, i) => {
            if (item.type === 'ellipsis') {
                return (
                    <button
                        key={`ellipsis-${i}`}
                        type="button"
                        onClick={onExpand}
                        className="flex w-full items-center justify-center py-2 text-muted-foreground hover:text-foreground transition-colors"
                    >
                        <span className="text-sm tracking-widest">...</span>
                    </button>
                );
            }
            if (item.type === 'separator') {
                return view === 'desktop' ? (
                    <div key={`sep-${i}`} className="border-t border-muted-200 dark:border-muted-800" />
                ) : (
                    <div key={`sep-${i}`} className="h-px bg-muted-200 dark:bg-muted-800" />
                );
            }

            const entry = item.entry;
            const overlay = renderOverlay?.(entry);

            return (
                <div
                    key={entry.user_id}
                    className={overlay ? 'group relative' : undefined}
                    onClick={
                        overlay
                            ? () => setOpenOverlay(openOverlay === entry.user_id ? null : entry.user_id)
                            : undefined
                    }
                >
                    {view === 'desktop' ? (
                        <LeaderboardRow entry={entry} href={hrefFor?.(entry)} />
                    ) : (
                        <LeaderboardCard entry={entry} href={hrefFor?.(entry)} />
                    )}
                    {overlay && (
                        <div
                            className={`absolute left-0 top-full z-50 mt-1 w-72 rounded-lg border bg-background shadow-lg ${
                                openOverlay === entry.user_id
                                    ? 'visible'
                                    : 'invisible group-hover:visible'
                            }`}
                        >
                            {overlay}
                        </div>
                    )}
                </div>
            );
        });

    return (
        <>
            {/* Escritorio: tabla */}
            <div className="hidden md:block" data-testid="leaderboard-table">
                {desktopHeader}
                {renderItems('desktop')}
            </div>

            {/* Móvil: tarjetas */}
            <div className="space-y-2 p-1.5 md:hidden" data-testid="leaderboard-cards">
                {renderItems('mobile')}
            </div>
        </>
    );
}
