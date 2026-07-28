import { X } from 'lucide-react';
import type { League } from './types';

interface ScoringInfoModalProps {
    league: League;
    open: boolean;
    onClose: () => void;
}

const ONE_DAY_POSITIONS = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10];

const POSITION_LABELS: Record<number, string> = {
    1: '1º clasificado',
    2: '2º clasificado',
    3: '3º clasificado',
    4: '4º clasificado',
    5: '5º clasificado',
    6: '6º clasificado',
    7: '7º clasificado',
    8: '8º clasificado',
    9: '9º clasificado',
    10: '10º clasificado',
};

function findRule(rules: League['scoring_system']['rules'], type: string, position?: number) {
    return rules.find((r) => r.type === type && (position === undefined || r.position === position));
}

function findPartialRule(rules: League['scoring_system']['rules'], type: string) {
    return rules.find((r) => r.type === `${type}_partial`);
}

function OneDayTemplate({ league }: { league: League }) {
    const rules = league.scoring_system.rules;

    return (
        <div className="space-y-8">
            <div>
                <h3 className="mb-4 flex items-center gap-2 text-sm font-semibold uppercase tracking-wide text-muted-foreground">
                    <span className="h-px flex-1 bg-border" />
                    Posiciones exactas
                    <span className="h-px flex-1 bg-border" />
                </h3>
                <p className="mb-3 text-xs text-muted-foreground">
                    Predice los 10 primeros clasificados de la carrera. Cuanto más preciso, más puntos.
                </p>
                <div className="space-y-1.5">
                    {ONE_DAY_POSITIONS.map((pos) => {
                        const exactType = `stage_exact_pos_${pos}`;
                        const rule = findRule(rules, exactType);
                        return (
                            <div key={pos} className="flex items-center justify-between rounded-lg bg-muted/30 px-4 py-2.5">
                                <span className="text-sm font-medium">{POSITION_LABELS[pos]}</span>
                                <span className="text-sm font-semibold text-accent-500">{rule?.points ?? '-'} pts</span>
                            </div>
                        );
                    })}
                </div>
            </div>

            <div>
                <h3 className="mb-4 flex items-center gap-2 text-sm font-semibold uppercase tracking-wide text-muted-foreground">
                    <span className="h-px flex-1 bg-border" />
                    Acierto parcial
                    <span className="h-px flex-1 bg-border" />
                </h3>
                <p className="mb-3 text-xs text-muted-foreground">
                    Si aciertas un corredor que entra en el top 10 pero en posición incorrecta, obtienes puntos parciales.
                </p>
                <div className="space-y-1.5">
                    {ONE_DAY_POSITIONS.map((pos) => {
                        const partialType = `stage_partial_pos_${pos}`;
                        const rule = findRule(rules, partialType);
                        return (
                            <div key={pos} className="flex items-center justify-between rounded-lg bg-muted/30 px-4 py-2">
                                <span className="text-sm">{POSITION_LABELS[pos]}</span>
                                <span className="text-sm font-semibold text-accent-500">{rule?.points ?? '-'} pts</span>
                            </div>
                        );
                    })}
                </div>
            </div>
        </div>
    );
}

function OneWeekTemplate({ league }: { league: League }) {
    const rules = league.scoring_system.rules;

    return (
        <div className="space-y-8">
            <div>
                <h3 className="mb-4 flex items-center gap-2 text-sm font-semibold uppercase tracking-wide text-muted-foreground">
                    <span className="h-px flex-1 bg-border" />
                    Clasificación general
                    <span className="h-px flex-1 bg-border" />
                </h3>
                <div className="space-y-1.5">
                    {[1, 2, 3].map((pos) => {
                        const rule = findRule(rules, 'gc_top_5', pos);
                        return (
                            <div key={pos} className="flex items-center justify-between rounded-lg bg-muted/30 px-4 py-2.5">
                                <span className="text-sm font-medium">{pos}º clasificado</span>
                                <span className="text-sm font-semibold text-accent-500">{rule?.points ?? '-'} pts</span>
                            </div>
                        );
                    })}
                </div>
                {(() => {
                    const partial = findPartialRule(rules, 'gc_top_5');
                    return partial ? (
                        <p className="mt-2 text-xs text-muted-foreground">
                            Si aciertas un corredor del Top 3 pero en posición incorrecta: <span className="font-semibold text-accent-500">{partial.points} pts</span>
                        </p>
                    ) : null;
                })()}
            </div>

            <div>
                <h3 className="mb-4 flex items-center gap-2 text-sm font-semibold uppercase tracking-wide text-muted-foreground">
                    <span className="h-px flex-1 bg-border" />
                    Pronósticos por etapa
                    <span className="h-px flex-1 bg-border" />
                </h3>

                {[1, 2, 3].map((diff) => {
                    const stageRules = rules.filter(
                        (r) => r.context === 'pre_stage' && r.difficulty === diff && r.type !== 'stage_leader'
                    );
                    if (stageRules.length === 0) return null;
                    const stars = '★'.repeat(diff) + '☆'.repeat(3 - diff);
                    return (
                        <div key={diff} className="mb-4 last:mb-0">
                            <div className="mb-2 flex items-center gap-2">
                                <span className="text-sm font-medium">Dificultad {stars}</span>
                            </div>
                            <div className="space-y-1.5">
                                {stageRules.map((rule) => (
                                    <div key={rule.type} className="flex items-center justify-between rounded-lg bg-muted/30 px-4 py-2">
                                        <span className="text-sm">{rule.label}</span>
                                        <span className="text-sm font-semibold text-accent-500">{rule.points} pts</span>
                                    </div>
                                ))}
                            </div>
                        </div>
                    );
                })}

                {(() => {
                    const leaderRule = findRule(rules, 'stage_leader');
                    return leaderRule ? (
                        <div className="mt-2">
                            <div className="flex items-center justify-between rounded-lg bg-muted/30 px-4 py-2.5">
                                <span className="text-sm font-medium">Líder de la general tras la etapa</span>
                                <span className="text-sm font-semibold text-accent-500">{leaderRule.points} pts</span>
                            </div>
                            <p className="mt-1.5 text-xs text-muted-foreground">
                                Se puntúa si aciertas el corredor que lidera la clasificación general al final de la etapa.
                            </p>
                        </div>
                    ) : null;
                })()}
            </div>
        </div>
    );
}

function GrandTourTemplate({ league }: { league: League }) {
    const rules = league.scoring_system.rules;

    return (
        <div className="space-y-8">
            <div>
                <h3 className="mb-4 flex items-center gap-2 text-sm font-semibold uppercase tracking-wide text-muted-foreground">
                    <span className="h-px flex-1 bg-border" />
                    Clasificación general
                    <span className="h-px flex-1 bg-border" />
                </h3>
                <div className="space-y-1.5">
                    {[1, 2, 3, 4, 5].map((pos) => {
                        const rule = findRule(rules, 'gc_top_5', pos);
                        return (
                            <div key={pos} className="flex items-center justify-between rounded-lg bg-muted/30 px-4 py-2.5">
                                <span className="text-sm font-medium">{pos}º clasificado</span>
                                <span className="text-sm font-semibold text-accent-500">{rule?.points ?? '-'} pts</span>
                            </div>
                        );
                    })}
                </div>
                {(() => {
                    const partial = findPartialRule(rules, 'gc_top_5');
                    return partial ? (
                        <p className="mt-2 text-xs text-muted-foreground">
                            Si aciertas un corredor del Top 5 pero en posición incorrecta: <span className="font-semibold text-accent-500">{partial.points} pts</span>
                        </p>
                    ) : null;
                })()}
            </div>

            <div>
                <h3 className="mb-4 flex items-center gap-2 text-sm font-semibold uppercase tracking-wide text-muted-foreground">
                    <span className="h-px flex-1 bg-border" />
                    Clasificaciones secundarias
                    <span className="h-px flex-1 bg-border" />
                </h3>
                {(['points_winner', 'mountains_winner', 'youth_winner'] as const).map((type) => {
                    const podium = [1, 2, 3].map((pos) => findRule(rules, type, pos)).filter(Boolean);
                    const label = type === 'points_winner' ? 'Maillot verde' : type === 'mountains_winner' ? 'Maillot montaña' : 'Maillot blanco';
                    const partial = findPartialRule(rules, type);
                    return (
                        <div key={type} className="mb-4 last:mb-0">
                            <p className="mb-2 text-sm font-medium">{label}</p>
                            <div className="space-y-1.5">
                                {podium.map((rule) => (
                                    <div key={rule!.position} className="flex items-center justify-between rounded-lg bg-muted/30 px-4 py-2">
                                        <span className="text-sm">{rule!.position}º clasificado</span>
                                        <span className="text-sm font-semibold text-accent-500">{rule!.points} pts</span>
                                    </div>
                                ))}
                            </div>
                            {partial && (
                                <p className="mt-1.5 text-xs text-muted-foreground">
                                    Acierto parcial (corredor en el podio pero posición incorrecta): <span className="font-semibold text-accent-500">{partial.points} pts</span>
                                </p>
                            )}
                        </div>
                    );
                })}
            </div>

            <div>
                <h3 className="mb-4 flex items-center gap-2 text-sm font-semibold uppercase tracking-wide text-muted-foreground">
                    <span className="h-px flex-1 bg-border" />
                    Otras predicciones pre-carrera
                    <span className="h-px flex-1 bg-border" />
                </h3>
                <div className="space-y-1.5">
                    {(() => {
                        const teamRule = findRule(rules, 'teams_winner');
                        return (
                            <div className="flex items-center justify-between rounded-lg bg-muted/30 px-4 py-2.5">
                                <span className="text-sm font-medium">Ganador clasificación equipos</span>
                                <span className="text-sm font-semibold text-accent-500">{teamRule?.points ?? '-'} pts</span>
                            </div>
                        );
                    })()}
                    {(() => {
                        const scRule = findRule(rules, 'super_combativo');
                        return (
                            <div className="flex items-center justify-between rounded-lg bg-muted/30 px-4 py-2.5">
                                <span className="text-sm font-medium">Supercombativo final</span>
                                <span className="text-sm font-semibold text-accent-500">{scRule?.points ?? '-'} pts</span>
                            </div>
                        );
                    })()}
                </div>
            </div>

            <div>
                <h3 className="mb-4 flex items-center gap-2 text-sm font-semibold uppercase tracking-wide text-muted-foreground">
                    <span className="h-px flex-1 bg-border" />
                    Pronósticos por etapa
                    <span className="h-px flex-1 bg-border" />
                </h3>

                {[1, 2, 3].map((diff) => {
                    const stageRules = rules.filter(
                        (r) => r.context === 'pre_stage' && r.difficulty === diff && r.type !== 'stage_leader'
                    );
                    if (stageRules.length === 0) return null;
                    const stars = '★'.repeat(diff) + '☆'.repeat(3 - diff);
                    return (
                        <div key={diff} className="mb-4 last:mb-0">
                            <div className="mb-2 flex items-center gap-2">
                                <span className="text-sm font-medium">Dificultad {stars}</span>
                            </div>
                            <div className="space-y-1.5">
                                {stageRules.map((rule) => (
                                    <div key={rule.type} className="flex items-center justify-between rounded-lg bg-muted/30 px-4 py-2">
                                        <span className="text-sm">{rule.label}</span>
                                        <span className="text-sm font-semibold text-accent-500">{rule.points} pts</span>
                                    </div>
                                ))}
                            </div>
                        </div>
                    );
                })}

                {(() => {
                    const leaderRule = findRule(rules, 'stage_leader');
                    return leaderRule ? (
                        <div className="mt-2">
                            <div className="flex items-center justify-between rounded-lg bg-muted/30 px-4 py-2.5">
                                <span className="text-sm font-medium">Líder de la general tras la etapa</span>
                                <span className="text-sm font-semibold text-accent-500">{leaderRule.points} pts</span>
                            </div>
                            <p className="mt-1.5 text-xs text-muted-foreground">
                                Se puntúa si aciertas el corredor que lidera la clasificación general al final de la etapa.
                            </p>
                        </div>
                    ) : null;
                })()}
            </div>
        </div>
    );
}

function ScoringTemplate({ league }: { league: League }) {
    const type = league.competition.type;

    if (type === 'monument' || type === 'classic' || type === 'championship') {
        return <OneDayTemplate league={league} />;
    }

    if (type === 'major') {
        return <OneWeekTemplate league={league} />;
    }

    return <GrandTourTemplate league={league} />;
}

export function ScoringInfoModal({ league, open, onClose }: ScoringInfoModalProps) {
    if (!open) return null;

    return (
        <div className="fixed inset-0 z-50 flex items-end sm:items-center justify-center">
            <div className="fixed inset-0 bg-black/50" onClick={onClose} />
            <div className="relative z-10 w-full max-w-lg max-h-[85vh] overflow-y-auto rounded-t-xl bg-popover p-6 shadow-lg sm:rounded-xl animate-in fade-in-0 zoom-in-95 slide-in-from-bottom-8 sm:slide-in-from-bottom-0">
                <div className="mb-6 flex items-center justify-between">
                    <h2 className="text-lg font-semibold">Sistema de puntuación</h2>
                    <button
                        type="button"
                        onClick={onClose}
                        className="rounded-lg p-1.5 text-muted-foreground transition-colors hover:bg-accent hover:text-accent-foreground"
                    >
                        <X className="h-5 w-5" />
                    </button>
                </div>

                <p className="mb-6 text-sm text-muted-foreground">
                    {league.scoring_system.name}: {league.scoring_system.description}
                </p>

                <ScoringTemplate league={league} />
            </div>
        </div>
    );
}
