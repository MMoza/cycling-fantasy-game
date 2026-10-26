import { Card, CardContent } from '@/components/ui/card';
import { Award } from 'lucide-react';
import type { BadgeItem } from '@/components/UserBadges';

interface BadgesCardProps {
    badges: {
        earned: BadgeItem[];
        pending: BadgeItem[];
    };
    className?: string;
}

const CATEGORY_LABELS: Record<string, string> = {
    etapas: 'Etapas',
    participacion: 'Participación',
    victorias: 'Victorias',
    hitos: 'Hitos',
    temporada: 'Temporada',
    especial: 'Especial',
};

function groupByCategory(items: BadgeItem[]): [string, BadgeItem[]][] {
    const groups = new Map<string, BadgeItem[]>();
    for (const item of items) {
        const list = groups.get(item.category) ?? [];
        list.push(item);
        groups.set(item.category, list);
    }
    return [...groups.entries()];
}

export default function BadgesCard({ badges, className }: BadgesCardProps) {
    const { earned, pending } = badges;

    return (
        <div className={className}>
            <div className="mb-4 flex items-center gap-2">
                <Award className="h-5 w-5 text-accent-500" />
                <h2 className="text-lg font-semibold">Mis badges</h2>
                <span className="text-sm text-muted-foreground">
                    {earned.length} conseguido{earned.length === 1 ? '' : 's'}
                </span>
            </div>

            {earned.length === 0 && pending.length === 0 && (
                <Card>
                    <CardContent className="py-10 text-center text-sm text-muted-foreground">
                        Aún no hay badges disponibles.
                    </CardContent>
                </Card>
            )}

            {earned.length > 0 && (
                <div className="space-y-5">
                    {groupByCategory(earned).map(([category, items]) => (
                        <div key={category}>
                            <p className="mb-2 text-xs font-semibold uppercase tracking-wide text-muted-foreground">
                                {CATEGORY_LABELS[category] ?? category}
                            </p>
                            <div className="grid grid-cols-2 gap-3 sm:grid-cols-3">
                                {items.map((badge) => (
                                    <div
                                        key={badge.code}
                                        className="flex items-center gap-3 rounded-xl border bg-card p-3"
                                        title={badge.description}
                                    >
                                        <img src={badge.svg} alt="" className="h-10 w-10 shrink-0" />
                                        <div className="min-w-0">
                                            <p className="truncate text-sm font-medium">{badge.name}</p>
                                            <p className="truncate text-xs text-accent-600 dark:text-accent-400">
                                                {badge.level_label ?? ''}
                                            </p>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        </div>
                    ))}
                </div>
            )}

            {pending.length > 0 && (
                <div className="mt-6">
                    <p className="mb-2 text-xs font-semibold uppercase tracking-wide text-muted-foreground">
                        En progreso
                    </p>
                    <div className="space-y-3">
                        {pending.map((badge) => {
                            const percent = badge.target > 0 ? Math.min(100, Math.round((badge.progress / badge.target) * 100)) : 0;

                            return (
                                <div key={badge.code} className="rounded-xl border bg-card p-3" title={badge.description}>
                                    <div className="flex items-center gap-3">
                                        <img src={badge.svg} alt="" className="h-8 w-8 shrink-0 opacity-70" />
                                        <div className="min-w-0 flex-1">
                                            <div className="flex items-center justify-between gap-2">
                                                <p className="truncate text-sm font-medium">{badge.name}</p>
                                                <span className="shrink-0 text-xs tabular-nums text-muted-foreground">
                                                    {badge.progress}/{badge.target}
                                                </span>
                                            </div>
                                            <div className="mt-1.5 h-1.5 w-full overflow-hidden rounded-full bg-muted">
                                                <div
                                                    className="h-full rounded-full bg-accent-500 transition-all"
                                                    style={{ width: `${percent}%` }}
                                                />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            );
                        })}
                    </div>
                </div>
            )}
        </div>
    );
}
