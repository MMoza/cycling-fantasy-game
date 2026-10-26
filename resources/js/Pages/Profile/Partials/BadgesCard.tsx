import { Card, CardContent } from '@/components/ui/card';
import { Award } from 'lucide-react';
import { BadgeCard } from '@/components/BadgeCard';
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
                            <div className="grid grid-cols-1 gap-3 sm:grid-cols-2">
                                {items.map((badge) => (
                                    <BadgeCard key={badge.code} badge={badge} variant="earned" />
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
                    <div className="grid grid-cols-1 gap-3 sm:grid-cols-2">
                        {pending.map((badge) => (
                            <BadgeCard key={badge.code} badge={badge} variant="pending" />
                        ))}
                    </div>
                </div>
            )}
        </div>
    );
}
