import { useState } from 'react';

export interface BadgeItem {
    code: string;
    name: string;
    description: string;
    category: string;
    mode: string;
    svg: string;
    level?: number;
    progress: number;
    target: number;
    level_label?: string | null;
    awarded_at?: string | null;
}

/**
 * Muestra los badges de un usuario en clasificaciones: hasta `max` iconos
 * + chip "+N" con popup (hover o tap) que lista todos.
 */
export function UserBadges({ badges, max = 2 }: { badges?: BadgeItem[] | null; max?: number }) {
    const [open, setOpen] = useState(false);

    if (!badges || badges.length === 0) {
        return null;
    }

    const shown = badges.slice(0, max);
    const extra = badges.length - shown.length;

    return (
        <span
            className="relative inline-flex items-center gap-1 align-middle"
            onMouseEnter={() => setOpen(true)}
            onMouseLeave={() => setOpen(false)}
            onClick={(e) => {
                e.stopPropagation();
                setOpen((v) => !v);
            }}
        >
            {shown.map((badge) => (
                <img
                    key={badge.code}
                    src={badge.svg}
                    alt={badge.name}
                    title={`${badge.name}${badge.level_label ? ` — ${badge.level_label}` : ''}`}
                    className="h-5 w-5 shrink-0"
                />
            ))}
            {extra > 0 && (
                <span className="rounded-full bg-muted px-1.5 py-0.5 text-[10px] font-semibold text-muted-foreground">
                    +{extra}
                </span>
            )}

            {open && (
                <div className="absolute left-0 top-full z-50 mt-1 w-72 rounded-lg border bg-background p-3 shadow-lg">
                    <p className="mb-2 text-xs font-medium text-muted-foreground">Badges</p>
                    <div className="max-h-60 space-y-2 overflow-y-auto">
                        {badges.map((badge) => (
                            <div key={badge.code} className="flex items-center gap-2">
                                <img src={badge.svg} alt="" className="h-7 w-7 shrink-0" />
                                <div className="min-w-0 flex-1">
                                    <p className="truncate text-sm font-medium">
                                        {badge.name}
                                        {badge.level_label ? ` · ${badge.level_label}` : ''}
                                    </p>
                                    <p className="truncate text-xs text-muted-foreground">{badge.description}</p>
                                </div>
                            </div>
                        ))}
                    </div>
                </div>
            )}
        </span>
    );
}
