import { useState } from 'react';
import { Card, CardContent } from '@/components/ui/card';
import { Award, ChevronDown, ChevronUp } from 'lucide-react';
import { BadgeCard } from '@/components/BadgeCard';
import type { BadgeItem } from '@/components/badges';

interface BadgesCardProps {
    badges: {
        earned: BadgeItem[];
        pending?: BadgeItem[];
    };
    /**
     * Usuario dueño de los badges (para enlazar a la vista de detalle).
     */
    userId?: string;
    /**
     * Renderiza la sección colapsable (perfil público de miembro).
     */
    collapsible?: boolean;
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

function badgeHref(userId: string | undefined, code: string): string | undefined {
    return userId ? route('badges.show', [userId, code]) : undefined;
}

export default function BadgesCard({ badges, userId, collapsible = false, className }: BadgesCardProps) {
    const [collapsed, setCollapsed] = useState(collapsible);
    const earned = badges.earned ?? [];
    const pending = badges.pending ?? [];

    const header = (
        <button
            type="button"
            onClick={() => collapsible && setCollapsed((v) => !v)}
            className={`flex w-full items-center gap-2 ${collapsible ? 'cursor-pointer' : 'cursor-default'}`}
        >
            <Award className="h-5 w-5 text-accent-500" />
            <h2 className="text-lg font-semibold">Badges</h2>
            <span className="text-sm text-muted-foreground">
                {earned.length} conseguido{earned.length === 1 ? '' : 's'}
            </span>
            {collapsible &&
                (collapsed ? (
                    <ChevronDown className="ml-auto h-5 w-5 text-muted-foreground" />
                ) : (
                    <ChevronUp className="ml-auto h-5 w-5 text-muted-foreground" />
                ))}
        </button>
    );

    return (
        <div className={className}>
            {!collapsible && <div className="mb-4">{header}</div>}
            {collapsible && (
                <Card>
                    <CardContent className="p-4">{header}</CardContent>
                </Card>
            )}

            {!collapsed && (
                <div className={collapsible ? 'mt-4 space-y-5' : 'space-y-5'}>
                    {earned.length === 0 && pending.length === 0 && (
                        <Card>
                            <CardContent className="py-10 text-center text-sm text-muted-foreground">
                                Aún no hay badges conseguidos.
                            </CardContent>
                        </Card>
                    )}

                    {earned.length > 0 &&
                        groupByCategory(earned).map(([category, items]) => (
                            <div key={category}>
                                <p className="mb-2 text-xs font-semibold uppercase tracking-wide text-muted-foreground">
                                    {CATEGORY_LABELS[category] ?? category}
                                </p>
                                <div className="grid grid-cols-1 gap-3 sm:grid-cols-2">
                                    {items.map((badge) => (
                                        <BadgeCard
                                            key={badge.code}
                                            badge={badge}
                                            variant="earned"
                                            href={badgeHref(userId, badge.code)}
                                        />
                                    ))}
                                </div>
                            </div>
                        ))}

                    {pending.length > 0 && (
                        <div>
                            <p className="mb-2 text-xs font-semibold uppercase tracking-wide text-muted-foreground">
                                En progreso
                            </p>
                            <div className="grid grid-cols-1 gap-3 sm:grid-cols-2">
                                {pending.map((badge) => (
                                    <BadgeCard
                                        key={badge.code}
                                        badge={badge}
                                        variant="pending"
                                        href={badgeHref(userId, badge.code)}
                                    />
                                ))}
                            </div>
                        </div>
                    )}
                </div>
            )}
        </div>
    );
}
