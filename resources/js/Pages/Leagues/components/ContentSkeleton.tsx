const pulse = 'animate-pulse rounded bg-muted';

/**
 * Placeholder shown while the dashboard content reloads after switching
 * to another league. Mimics the layout of the sections below the hero card.
 */
export function ContentSkeleton() {
    return (
        <div className="space-y-6" aria-hidden="true">
            {/* Stats cards */}
            <div className="grid grid-cols-3 gap-2 sm:gap-4">
                {[0, 1, 2].map((i) => (
                    <div key={i} className="h-[92px] animate-pulse rounded-xl bg-muted ring-1 ring-foreground/10 sm:h-[104px]" />
                ))}
            </div>

            {/* Access cards: Clasificación Temporada / PREDICTOR / Equipos */}
            {[0, 1, 2].map((i) => (
                <div key={i} className="flex items-center gap-3 rounded-xl bg-card p-4 ring-1 ring-foreground/10">
                    <div className="h-10 w-10 shrink-0 animate-pulse rounded-full bg-muted/70" />
                    <div className="flex-1 space-y-2">
                        <div className={`${pulse} h-4 w-2/5`} />
                        <div className={`${pulse} h-3 w-3/5 opacity-70`} />
                    </div>
                    <div className={`${pulse} h-5 w-5 opacity-70`} />
                </div>
            ))}

            {/* Invite friend card */}
            <div className="h-24 animate-pulse rounded-xl bg-muted ring-1 ring-foreground/10" />

            {/* Leaderboard */}
            <div className="rounded-xl bg-card p-4 ring-1 ring-foreground/10">
                <div className={`${pulse} mb-4 h-4 w-1/3`} />
                <div className="space-y-2">
                    {[0, 1, 2, 3, 4].map((i) => (
                        <div key={i} className="h-9 animate-pulse rounded-lg bg-muted/60" />
                    ))}
                </div>
            </div>

            {/* Activity feed */}
            <div className="rounded-xl bg-card p-4 ring-1 ring-foreground/10">
                <div className={`${pulse} mb-4 h-4 w-1/4`} />
                <div className="space-y-3">
                    {[0, 1, 2].map((i) => (
                        <div key={i} className="h-6 animate-pulse rounded bg-muted/60" />
                    ))}
                </div>
            </div>
        </div>
    );
}
