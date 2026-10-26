import type { BadgeItem } from '@/components/badges';

/**
 * Icono de badge con el número superpuesto (veces conseguido o nivel).
 */
export function BadgeIcon({
    badge,
    size = 40,
    showOverlay = true,
}: {
    badge: BadgeItem;
    size?: number;
    showOverlay?: boolean;
}) {
    const overlay = badge.overlay ?? null;

    return (
        <span className="relative inline-flex shrink-0" style={{ width: size, height: size }}>
            <img src={badge.svg} alt="" className="h-full w-full" />
            {showOverlay && overlay !== null && (
                <span
                    className={`absolute -bottom-1 -right-1 flex items-center justify-center rounded-full bg-foreground font-bold text-background ring-2 ring-background ${
                        size >= 32 ? 'h-4 min-w-4 px-1 text-[10px]' : 'h-3.5 min-w-3.5 px-0.5 text-[9px]'
                    }`}
                >
                    {overlay}
                </span>
            )}
        </span>
    );
}

/**
 * Niveles de un badge como slots rellenos/huecos (p.ej. ●●○○○).
 */
export function BadgeLevelPips({ levels, levelIndex }: { levels?: number[]; levelIndex?: number }) {
    if (!levels || levels.length === 0) {
        return null;
    }

    const filled = levelIndex ?? 0;

    return (
        <div className="flex items-center gap-1" title={`Nivel ${filled} de ${levels.length}`}>
            {levels.map((tier, i) => (
                <span
                    key={tier}
                    className={`h-1.5 w-5 rounded-full transition-colors ${i < filled ? 'bg-accent-500' : 'bg-muted'}`}
                />
            ))}
        </div>
    );
}

interface BadgeCardProps {
    badge: BadgeItem;
    variant?: 'earned' | 'pending';
    href?: string;
}

/**
 * Fila reutilizable de un badge: icono (con número superpuesto), nombre y
 * estado de progreso.
 * - Por niveles (cumulative): slots rellenos/huecos por nivel alcanzado
 * - Por objetivo (once): barra de progreso hacia el objetivo
 * - Especiales: nunca llegan aquí como pendientes; si están ganados muestran "Conseguido"
 */
export function BadgeCard({ badge, variant = 'earned', href }: BadgeCardProps) {
    const isPending = variant === 'pending';
    const isTiered = badge.mode === 'cumulative';
    const percent = badge.target > 0 ? Math.min(100, Math.round((badge.progress / badge.target) * 100)) : 0;

    const body = (
        <div className="flex items-center gap-3">
            <BadgeIcon badge={badge} size={40} />

            <div className="min-w-0 flex-1">
                <p className="truncate text-sm font-medium">{badge.name}</p>

                {isTiered ? (
                    <div className="mt-1.5">
                        <BadgeLevelPips levels={badge.levels} levelIndex={badge.level_index} />
                        {isPending && (
                            <p className="mt-1 text-xs tabular-nums text-muted-foreground">
                                {badge.progress}/{badge.target}
                            </p>
                        )}
                    </div>
                ) : isPending ? (
                    <div className="mt-1.5">
                        <div className="h-1.5 w-full overflow-hidden rounded-full bg-muted">
                            <div
                                className="h-full rounded-full bg-accent-500 transition-all"
                                style={{ width: `${percent}%` }}
                            />
                        </div>
                        <p className="mt-1 text-xs tabular-nums text-muted-foreground">
                            {badge.progress}/{badge.target}
                        </p>
                    </div>
                ) : (
                    <p className="mt-1 text-xs font-medium text-green-600 dark:text-green-400">
                        Conseguido
                    </p>
                )}
            </div>
        </div>
    );

    const className = 'block rounded-xl border bg-card p-3 transition-colors';

    if (href) {
        return (
            <a href={href} className={`${className} hover:bg-muted/50`} title={badge.description}>
                {body}
            </a>
        );
    }

    return (
        <div className={className} title={badge.description}>
            {body}
        </div>
    );
}
