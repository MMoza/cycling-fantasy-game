import AppLayout from '@/Layouts/AppLayout';
import { Head } from '@inertiajs/react';
import { Card, CardContent } from '@/components/ui/card';
import { BadgeIcon, BadgeLevelPips } from '@/components/BadgeCard';
import type { BadgeItem } from '@/components/badges';
import { ArrowLeft, Check, Minus } from 'lucide-react';

interface LevelDetail {
    value: number;
    level_label: string;
    reached: boolean;
    achieved_at: string | null;
}

interface HistoryEntry {
    event: string;
    level: number;
    progress: number;
    created_at: string | null;
}

interface BadgeShowProps {
    badge: BadgeItem;
    user: { id: string; name: string };
    level: number;
    level_index: number;
    progress: number;
    target: number;
    awarded_at: string | null;
    levels_detail: LevelDetail[];
    history: HistoryEntry[];
}

const formatDate = (iso: string | null) =>
    iso ? new Date(iso).toLocaleDateString('es-ES', { day: 'numeric', month: 'short', year: 'numeric' }) : '—';

export default function Show({ badge, user, level, level_index, progress, target, awarded_at, levels_detail, history }: BadgeShowProps) {
    const isTiered = badge.mode === 'cumulative';
    const earned = isTiered ? level > 0 : progress >= target;
    const percent = target > 0 ? Math.min(100, Math.round((progress / target) * 100)) : 0;

    return (
        <AppLayout>
            <Head title={`${badge.name} — Logro`} />

            <div className="mx-auto max-w-2xl space-y-6 px-4 py-6 sm:px-0">
                <button
                    type="button"
                    onClick={() => window.history.back()}
                    className="flex items-center gap-1.5 text-sm text-muted-foreground transition-colors hover:text-foreground"
                >
                    <ArrowLeft className="h-4 w-4" />
                    Volver
                </button>

                <Card className="overflow-hidden">
                    <div className="h-1 rounded-t-xl bg-gradient-to-r from-brand-600 to-accent-500" />
                    <CardContent className="space-y-8 p-6">
                        {/* Cabecera */}
                        <div className="flex items-start gap-4">
                            <BadgeIcon badge={badge} size={56} />
                            <div className="min-w-0 flex-1">
                                <h1 className="text-xl font-semibold tracking-tight">{badge.name}</h1>
                                <p className="mt-1 text-sm text-muted-foreground">{badge.description}</p>
                                <p className="mt-1 text-xs text-muted-foreground">Logro de {user.name}</p>
                            </div>
                            {earned ? (
                                <span className="shrink-0 rounded-full bg-green-100 px-2.5 py-1 text-xs font-medium text-green-700 dark:bg-green-950 dark:text-green-400">
                                    Conseguido
                                </span>
                            ) : (
                                <span className="shrink-0 rounded-full bg-muted px-2.5 py-1 text-xs font-medium text-muted-foreground">
                                    En progreso
                                </span>
                            )}
                        </div>

                        {/* Estado actual */}
                        <div className="rounded-xl bg-muted/40 p-4">
                            {isTiered ? (
                                <>
                                    <div className="flex items-center justify-between">
                                        <p className="text-sm font-medium">
                                            Nivel {level_index} de {badge.levels?.length ?? 0}
                                        </p>
                                        <p className="text-sm tabular-nums text-muted-foreground">
                                            {progress} · siguiente nivel: {target}
                                        </p>
                                    </div>
                                    <div className="mt-2">
                                        <BadgeLevelPips levels={badge.levels} levelIndex={level_index} />
                                    </div>
                                </>
                            ) : (
                                <>
                                    <div className="flex items-center justify-between">
                                        <p className="text-sm font-medium">
                                            {isTiered ? `Nivel ${level}` : `${progress}/${target}`}
                                        </p>
                                        {awarded_at && (
                                            <p className="text-sm text-muted-foreground">
                                                Conseguído el {formatDate(awarded_at)}
                                            </p>
                                        )}
                                    </div>
                                    <div className="mt-2 h-2 w-full overflow-hidden rounded-full bg-muted">
                                        <div
                                            className="h-full rounded-full bg-accent-500 transition-all"
                                            style={{ width: `${percent}%` }}
                                        />
                                    </div>
                                </>
                            )}
                        </div>

                        {/* Niveles */}
                        <div>
                            <h2 className="mb-3 text-xs font-semibold uppercase tracking-wide text-muted-foreground">
                                Niveles
                            </h2>
                            <div className="space-y-1.5">
                                {levels_detail.map((item) => (
                                    <div
                                        key={item.value}
                                        className={`flex items-center justify-between rounded-lg px-4 py-2.5 ${
                                            item.reached ? 'bg-accent-500/10' : 'bg-muted/30'
                                        }`}
                                    >
                                        <div className="flex items-center gap-2">
                                            {item.reached ? (
                                                <Check className="h-4 w-4 text-accent-600 dark:text-accent-400" />
                                            ) : (
                                                <Minus className="h-4 w-4 text-muted-foreground/50" />
                                            )}
                                            <span className="text-sm font-medium">{item.level_label}</span>
                                            <span className="text-sm tabular-nums text-muted-foreground">
                                                · {item.value}
                                            </span>
                                        </div>
                                        <span className="text-xs tabular-nums text-muted-foreground">
                                            {item.reached ? formatDate(item.achieved_at) : '—'}
                                        </span>
                                    </div>
                                ))}
                            </div>
                        </div>

                        {/* Histórico */}
                        <div>
                            <h2 className="mb-3 text-xs font-semibold uppercase tracking-wide text-muted-foreground">
                                Historial
                            </h2>
                            {history.length === 0 ? (
                                <p className="text-sm text-muted-foreground">Todavía no hay hitos registrados.</p>
                            ) : (
                                <div className="space-y-2">
                                    {history.map((entry, i) => (
                                        <div key={`${entry.created_at}-${i}`} className="flex items-center gap-3 text-sm">
                                            <span className="w-24 shrink-0 tabular-nums text-muted-foreground">
                                                {formatDate(entry.created_at)}
                                            </span>
                                            <span className="flex-1">
                                                {entry.event === 'level'
                                                    ? `Nuevo nivel alcanzado (valor ${entry.level})`
                                                    : `Progreso: ${entry.progress}`}
                                            </span>
                                        </div>
                                    ))}
                                </div>
                            )}
                        </div>
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}
