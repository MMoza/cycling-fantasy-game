import type { BadgeItem } from '@/components/UserBadges';

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
}

/**
 * Fila reutilizable de un badge: icono, nombre y estado de progreso.
 * - Por niveles (cumulative): slots rellenos/huecos por nivel alcanzado
 * - Por objetivo (once): barra de progreso hacia el objetivo
 * - Especiales: nunca llegan aquí como pendientes; si están ganados muestran "Conseguido"
 */
export function BadgeCard({ badge, variant = 'earned' }: BadgeCardProps) {
    const isPending = variant === 'pending';
    const isTiered = badge.mode === 'cumulative';
    const percent = badge.target > 0 ? Math.min(100, Math.round((badge.progress / badge.target) * 100)) : 0;

    return (
        <div
            className="flex items-center gap-3 rounded-xl border bg-card p-3"
            title={badge.description}
        >
            <img src={badge.svg} alt="" className={`h-10 w-10 shrink-0 ${isPending ? 'opacity-70' : ''}`} />

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
}
