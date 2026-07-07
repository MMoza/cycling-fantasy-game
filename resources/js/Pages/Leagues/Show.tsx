import { useEffect, useState } from 'react';
import AppLayout from '@/Layouts/AppLayout';
import { Head, Link, router, usePage } from '@inertiajs/react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import Avatar from '@/components/Avatar';
import { StageTypeIcon } from '@/components/ui/stage-type-icon';
import { Trophy, Calendar, Route, ChevronRight, Users, Target, Settings, X, Save, Copy, Info, Flag, Play, CheckCheck, Award, Activity, ShieldCheck, ChevronUp, ChevronDown, Minus } from 'lucide-react';

interface League {
    id: string;
    name: string;
    invite_code: string;
    owner_id: string;
    competition: {
        name: string;
        year: number;
        coverImageUrl?: string | null;
        logoImageUrl?: string | null;
    };
    scoring_system: {
        name: string;
        type: string;
        description: string;
        rules: {
            type: string;
            label: string;
            context: string;
            points: number;
            difficulty: number | null;
            position: number | null;
        }[];
    };
    is_public: boolean;
    is_official: boolean;
    max_players: number;
    member_count: number;
    is_owner: boolean;
    progress: {
        current_stage: number;
        total_stages: number;
    };
}

interface Stage {
    id: string;
    number: number;
    name: string;
    date: string;
    type: string;
    distance: string | null;
    status: string;
}

interface LeaderboardEntry {
    rank: number;
    user_id: string;
    user_name: string;
    avatar?: string | null;
    points: number;
    behind_leader: number;
    is_current_user: boolean;
    previous_rank: number | null;
    rank_change: number | null;
}

interface ActivityLog {
    id: string;
    type: 'competition_start' | 'stage_start' | 'stage_end' | 'competition_end';
    title: string;
    description: string | null;
    data: Record<string, unknown> | null;
    created_at: string;
}

interface ShowProps {
    league: League;
    next_stage: {
        id: string;
        number: number;
        name: string;
        date: string;
        type: string;
        type_value: string;
        distance: string | null;
        distance_value: number | null;
        origin: string;
        destination: string;
        status: string;
        scheduled_start: string | null;
        difficulty: number | null;
        has_predictions: boolean;
    } | null;
    user_position: {
        rank: string;
        points: string;
        behind_leader: string;
    };
    stages: Stage[];
    leaderboard: LeaderboardEntry[];
    activity_logs: ActivityLog[];
}

function PositionChange({ change }: { change: number | null }) {
    if (change === null || change === 0) {
        return <Minus className="h-3 w-3 text-muted-foreground/40" />;
    }
    if (change > 0) {
        return (
            <span className="flex items-center gap-0.5 text-xs font-medium text-green-600">
                <ChevronUp className="h-3 w-3" />
                {change}
            </span>
        );
    }
    return (
        <span className="flex items-center gap-0.5 text-xs font-medium text-red-500">
            <ChevronDown className="h-3 w-3" />
            {Math.abs(change)}
        </span>
    );
}

function buildVisibleLeaderboard(
    leaderboard: LeaderboardEntry[],
    currentUserId: string,
): ({ type: 'entry'; entry: LeaderboardEntry } | { type: 'ellipsis' } | { type: 'separator' })[] {
    if (leaderboard.length === 0) return [];

    const maxVisible = 10;
    const currentIndex = leaderboard.findIndex((e) => e.user_id === currentUserId);

    if (currentIndex === -1 || currentIndex < maxVisible) {
        return leaderboard.slice(0, maxVisible).map((entry) => ({ type: 'entry' as const, entry }));
    }

    const windowSize = maxVisible - 1;
    const halfBefore = Math.ceil(windowSize / 2);
    const halfAfter = windowSize - halfBefore;

    let start = currentIndex - halfBefore;
    let end = currentIndex + halfAfter;

    if (start < 1) {
        start = 1;
        end = start + windowSize - 1;
    }
    if (end >= leaderboard.length) {
        end = leaderboard.length - 1;
        start = Math.max(1, end - windowSize + 1);
    }

    const result: ({ type: 'entry'; entry: LeaderboardEntry } | { type: 'ellipsis' } | { type: 'separator' })[] = [];
    result.push({ type: 'entry', entry: leaderboard[0] });

    if (start > 1) {
        result.push({ type: 'ellipsis' });
    }

    for (let i = start; i <= end; i++) {
        if (i === currentIndex) {
            result.push({ type: 'separator' });
        }
        result.push({ type: 'entry', entry: leaderboard[i] });
        if (i === currentIndex) {
            result.push({ type: 'separator' });
        }
    }

    return result;
}

function formatDiff(ms: number): string {
    if (ms <= 0) return '00:00:00';
    const totalSeconds = Math.floor(ms / 1000);
    const hours = Math.floor(totalSeconds / 3600);
    const minutes = Math.floor((totalSeconds % 3600) / 60);
    const seconds = totalSeconds % 60;
    return `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
}

function Countdown({ scheduledStart, onExpired }: { scheduledStart: string; onExpired?: () => void }) {
    const [diff, setDiff] = useState(0);

    useEffect(() => {
        const update = () => {
            const d = new Date(scheduledStart).getTime() - Date.now();
            setDiff(d);
            if (d <= 0) onExpired?.();
        };
        update();
        const id = setInterval(update, 1000);
        return () => clearInterval(id);
    }, [scheduledStart, onExpired]);

    return (
        <span className="font-mono text-xs font-bold tabular-nums tracking-wider">
            {formatDiff(diff)}
        </span>
    );
}

export default function Show({ league, next_stage, user_position, stages, leaderboard, activity_logs }: ShowProps) {
    const [settingsOpen, setSettingsOpen] = useState(false);
    const [scoringInfoOpen, setScoringInfoOpen] = useState(false);
    const [formName, setFormName] = useState(league.name);
    const [formIsPublic, setFormIsPublic] = useState(league.is_public);
    const [saving, setSaving] = useState(false);
    const [copied, setCopied] = useState(false);
    const [showAllLeaderboard, setShowAllLeaderboard] = useState(false);
    const [sortKey, setSortKey] = useState<'rank' | 'user_name' | 'points'>('points');
    const [sortDir, setSortDir] = useState<'asc' | 'desc'>('desc');
    const [countdownExpired, setCountdownExpired] = useState(false);

    const isOngoing = next_stage?.status === 'ongoing' || (next_stage?.status === 'upcoming' && countdownExpired);

    const sortedLeaderboard = [...leaderboard].sort((a, b) => {
        let cmp = 0;
        if (sortKey === 'rank') cmp = a.rank - b.rank;
        else if (sortKey === 'user_name') cmp = a.user_name.localeCompare(b.user_name);
        else cmp = a.points - b.points;
        return sortDir === 'asc' ? cmp : -cmp;
    });

    const toggleSort = (key: typeof sortKey) => {
        if (sortKey === key) {
            setSortDir((d) => (d === 'asc' ? 'desc' : 'asc'));
        } else {
            setSortKey(key);
            setSortDir(key === 'rank' ? 'asc' : 'desc');
        }
    };

    const activityIcons: Record<string, React.ReactNode> = {
        competition_start: <Flag className="h-4 w-4" />,
        stage_start: <Play className="h-4 w-4" />,
        stage_end: <CheckCheck className="h-4 w-4" />,
        competition_end: <Award className="h-4 w-4" />,
    };

    const activityColors: Record<string, string> = {
        competition_start: 'bg-blue-100 text-blue-600 dark:bg-blue-900/20 dark:text-blue-400',
        stage_start: 'bg-amber-100 text-amber-600 dark:bg-amber-900/20 dark:text-amber-400',
        stage_end: 'bg-green-100 text-green-600 dark:bg-green-900/20 dark:text-green-400',
        competition_end: 'bg-purple-100 text-purple-600 dark:bg-purple-900/20 dark:text-purple-400',
    };

    const copyInviteCode = () => {
        navigator.clipboard.writeText(league.invite_code);
        setCopied(true);
        setTimeout(() => setCopied(false), 2000);
    };

    const saveSettings = () => {
        setSaving(true);
        router.put(
            route('leagues.update', league.id),
            {
                name: formName,
                is_public: formIsPublic,
            },
            {
                preserveScroll: true,
                onFinish: () => setSaving(false),
                onSuccess: () => setSettingsOpen(false),
            }
        );
    };

    return (
        <AppLayout>
            <Head title={league.name} />

            <div className="mx-auto max-w-2xl space-y-6 px-4 py-6 sm:px-0">
                <Card className="overflow-hidden">
                    <div className="relative flex h-44 items-end sm:h-52">
                        {league.competition.coverImageUrl ? (
                            <img
                                src={league.competition.coverImageUrl}
                                alt=""
                                className="absolute inset-0 h-full w-full object-cover"
                            />
                        ) : (
                            <div className="absolute inset-0 flex items-center justify-center bg-muted">
                                <Route className="h-16 w-16 text-muted-foreground/40" />
                            </div>
                        )}
                        <div className="absolute inset-0 bg-gradient-to-t from-black/70 via-black/20 to-transparent" />
                        <div className="relative z-10 flex w-full items-end gap-4 p-5">
                            <div className="flex h-14 w-14 shrink-0 items-center justify-center overflow-hidden rounded-full border-2 border-white/60 bg-black/40">
                                {league.competition.logoImageUrl ? (
                                    <img
                                        src={league.competition.logoImageUrl}
                                        alt=""
                                        className="h-full w-full object-cover"
                                    />
                                ) : (
                                    <Trophy className="h-6 w-6 text-muted-foreground" />
                                )}
                            </div>
                            <div className="min-w-0 flex-1">
                                <h1 className="text-lg font-bold text-white truncate drop-shadow-sm">
                                    {league.competition.name} {league.competition.year}
                                </h1>
                                <div className="flex items-center gap-2">
                                    <span className="text-sm font-medium text-white/90 truncate">
                                        {league.name}
                                    </span>
                                    {league.is_official && (
                                        <Badge variant="default" className="gap-1 rounded-full bg-brand-600 text-white text-[10px] h-5 px-2 border-0 shadow-sm">
                                            <ShieldCheck className="h-2.5 w-2.5" />
                                            Oficial
                                        </Badge>
                                    )}
                                </div>
                            </div>
                            <button
                                type="button"
                                onClick={() => setSettingsOpen(true)}
                                className="shrink-0 rounded-lg p-2 text-white/80 transition-colors hover:bg-white/10 hover:text-white"
                                title="Ajustes de la liga"
                            >
                                <Settings className="h-5 w-5" />
                            </button>
                        </div>
                    </div>
                    <CardContent className="p-5">
                        <p className="text-sm text-muted-foreground">
                            {league.member_count} participantes{league.is_official ? '' : ` · ${league.max_players} máx`}
                        </p>
                    </CardContent>
                </Card>

                {settingsOpen && (
                    <div className="fixed inset-0 z-50 flex items-end sm:items-center justify-center">
                        <div className="fixed inset-0 bg-black/50" onClick={() => setSettingsOpen(false)} />
                        <div className="relative z-10 w-full max-w-lg rounded-t-xl bg-popover p-6 shadow-lg sm:rounded-xl animate-in fade-in-0 zoom-in-95 slide-in-from-bottom-8 sm:slide-in-from-bottom-0">
                            <div className="mb-6 flex items-center justify-between">
                                <h2 className="text-lg font-semibold">Ajustes de la liga</h2>
                                <button
                                    type="button"
                                    onClick={() => setSettingsOpen(false)}
                                    className="rounded-lg p-1.5 text-muted-foreground transition-colors hover:bg-accent hover:text-accent-foreground"
                                >
                                    <X className="h-5 w-5" />
                                </button>
                            </div>

                            <div className="space-y-6">
                                <div className="space-y-3">
                                    <div className="flex items-center justify-between">
                                        <Label className="text-base font-medium">Sistema de puntuación</Label>
                                        <Button
                                            type="button"
                                            variant="ghost"
                                            size="sm"
                                            onClick={() => setScoringInfoOpen(true)}
                                            className="gap-1"
                                        >
                                            <Info className="h-4 w-4" />
                                            Detalles
                                        </Button>
                                    </div>
                                    <div className="rounded-lg border bg-muted/50 p-4">
                                        <p className="font-medium">{league.scoring_system.name}</p>
                                        <p className="mt-1 text-sm text-muted-foreground">{league.scoring_system.description}</p>
                                    </div>
                                </div>

                                <div className="space-y-3">
                                    <Label className="text-base font-medium">Código de invitación</Label>
                                    <div className="flex items-center gap-2">
                                        <code className="flex-1 rounded-lg border bg-muted px-3 py-2 text-sm font-mono">
                                            {league.invite_code}
                                        </code>
                                        <Button
                                            type="button"
                                            variant="outline"
                                            size="sm"
                                            onClick={copyInviteCode}
                                            className="shrink-0"
                                        >
                                            {copied ? (
                                                <span className="text-xs">Copiado</span>
                                            ) : (
                                                <Copy className="h-4 w-4" />
                                            )}
                                        </Button>
                                    </div>
                                </div>

                                {league.is_owner ? (
                                    <>
                                        <div className="space-y-3">
                                            <Label htmlFor="settings-name" className="text-base font-medium">Nombre de la liga</Label>
                                            <Input
                                                id="settings-name"
                                                value={formName}
                                                onChange={(e) => setFormName(e.target.value)}
                                            />
                                        </div>

                                        {!league.is_official && (
                                            <div className="flex items-center justify-between">
                                                <div>
                                                    <Label className="text-base font-medium">Liga pública</Label>
                                                    <p className="text-sm text-muted-foreground">Cualquier usuario puede encontrar y unirse</p>
                                                </div>
                                                <Button
                                                    type="button"
                                                    variant={formIsPublic ? 'default' : 'secondary'}
                                                    size="sm"
                                                    onClick={() => setFormIsPublic(!formIsPublic)}
                                                >
                                                    {formIsPublic ? 'Pública' : 'Privada'}
                                                </Button>
                                            </div>
                                        )}

                                        <div className="flex justify-end gap-3 pt-2">
                                            <Button variant="outline" onClick={() => setSettingsOpen(false)}>
                                                Cancelar
                                            </Button>
                                            <Button onClick={saveSettings} disabled={saving}>
                                                <Save className="mr-2 h-4 w-4" />
                                                {saving ? 'Guardando...' : 'Guardar cambios'}
                                            </Button>
                                        </div>
                                    </>
                                ) : (
                                    <div className="space-y-4 border-t pt-4">
                                        <div>
                                            <Label className="text-base font-medium">Nombre</Label>
                                            <p className="mt-1 text-sm">{league.name}</p>
                                        </div>
                                        <div>
                                            <Label className="text-base font-medium">Participantes</Label>
                                            <p className="mt-1 text-sm">
                                                {league.member_count}
                                                {league.is_official ? '' : ' / 20 máx'}
                                            </p>
                                        </div>
                                        <div>
                                            <Label className="text-base font-medium">Tipo</Label>
                                            <p className="mt-1 text-sm">{league.is_public ? 'Pública' : 'Privada'}</p>
                                        </div>
                                    </div>
                                )}
                            </div>
                        </div>
                    </div>
                )}

                {scoringInfoOpen && (
                    <div className="fixed inset-0 z-50 flex items-end sm:items-center justify-center">
                        <div className="fixed inset-0 bg-black/50" onClick={() => setScoringInfoOpen(false)} />
                        <div className="relative z-10 w-full max-w-lg max-h-[85vh] overflow-y-auto rounded-t-xl bg-popover p-6 shadow-lg sm:rounded-xl animate-in fade-in-0 zoom-in-95 slide-in-from-bottom-8 sm:slide-in-from-bottom-0">
                            <div className="mb-6 flex items-center justify-between">
                                <h2 className="text-lg font-semibold">Sistema de puntuación</h2>
                                <button
                                    type="button"
                                    onClick={() => setScoringInfoOpen(false)}
                                    className="rounded-lg p-1.5 text-muted-foreground transition-colors hover:bg-accent hover:text-accent-foreground"
                                >
                                    <X className="h-5 w-5" />
                                </button>
                            </div>

                            <p className="mb-6 text-sm text-muted-foreground">
                                {league.scoring_system.name}: {league.scoring_system.description}
                            </p>

                            <div className="space-y-8">
                                <div>
                                    <h3 className="mb-4 flex items-center gap-2 text-sm font-semibold uppercase tracking-wide text-muted-foreground">
                                        <span className="h-px flex-1 bg-border" />
                                        Clasificación general
                                        <span className="h-px flex-1 bg-border" />
                                    </h3>
                                    <div className="space-y-1.5">
                                        {[1, 2, 3, 4, 5].map((pos) => {
                                            const rule = league.scoring_system.rules.find((r) => r.type === 'gc_top_5' && r.position === pos);
                                            return (
                                                <div key={pos} className="flex items-center justify-between rounded-lg bg-muted/30 px-4 py-2.5">
                                                    <span className="text-sm font-medium">{pos}º clasificado</span>
                                                    <span className="text-sm font-semibold text-accent-500">{rule?.points ?? '-'} pts</span>
                                                </div>
                                            );
                                        })}
                                    </div>
                                    {(() => {
                                        const partial = league.scoring_system.rules.find((r) => r.type === 'gc_top_5_partial');
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
                                    {['points_winner', 'mountains_winner', 'youth_winner'].map((type) => {
                                        const podium = [1, 2, 3].map((pos) =>
                                            league.scoring_system.rules.find((r) => r.type === type && r.position === pos)
                                        ).filter(Boolean);
                                        const label = type === 'points_winner' ? 'Maillot verde' : type === 'mountains_winner' ? 'Maillot montaña' : 'Maillot blanco';
                                        const partial = league.scoring_system.rules.find((r) => r.type === `${type}_partial`);
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
                                            const teamRule = league.scoring_system.rules.find((r) => r.type === 'teams_winner');
                                            return (
                                                <div className="flex items-center justify-between rounded-lg bg-muted/30 px-4 py-2.5">
                                                    <span className="text-sm font-medium">Ganador clasificación equipos</span>
                                                    <span className="text-sm font-semibold text-accent-500">{teamRule?.points ?? '-'} pts</span>
                                                </div>
                                            );
                                        })()}
                                        {(() => {
                                            const scRule = league.scoring_system.rules.find((r) => r.type === 'super_combativo');
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
                                        const rules = league.scoring_system.rules.filter(
                                            (r) => r.context === 'pre_stage' && r.difficulty === diff && r.type !== 'stage_leader'
                                        );
                                        if (rules.length === 0) return null;
                                        const stars = '★'.repeat(diff) + '☆'.repeat(3 - diff);
                                        return (
                                            <div key={diff} className="mb-4 last:mb-0">
                                                <div className="mb-2 flex items-center gap-2">
                                                    <span className="text-sm font-medium">Dificultad {stars}</span>
                                                </div>
                                                <div className="space-y-1.5">
                                                    {rules.map((rule) => (
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
                                        const leaderRule = league.scoring_system.rules.find((r) => r.type === 'stage_leader');
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
                        </div>
                    </div>
                )}

                <div className="grid grid-cols-3 gap-2 sm:gap-4">
                    <Link href={route('stages.index', league.id)} className="block">
                        <Card className="cursor-pointer transition-colors hover:bg-muted/50 h-full">
                            <CardContent className="flex flex-col items-center justify-center px-3 py-5 sm:px-4 sm:py-6">
                                <Route className="mb-1 h-4 w-4 text-brand-600" />
                                <div className="text-lg font-bold sm:text-xl">
                                    {league.progress.current_stage}/{league.progress.total_stages}
                                </div>
                                <p className="text-[11px] text-muted-foreground leading-tight text-center">
                                    {league.competition.name}
                                </p>
                            </CardContent>
                        </Card>
                    </Link>

                    <Link href={route('stages.show', [league.id, next_stage?.id ?? ''])} className="block">
                        <Card className={`cursor-pointer transition-all h-full ${
                            isOngoing
                                ? 'border-amber-400 dark:border-amber-600 shadow-amber-200/50 dark:shadow-amber-900/30 shadow-md ring-1 ring-amber-300/50 dark:ring-amber-700/50'
                                : next_stage?.has_predictions
                                    ? 'border-green-300 dark:border-green-700 hover:bg-muted/50'
                                    : next_stage
                                        ? 'animate-pulse border-amber-400 hover:bg-muted/50'
                                        : 'hover:bg-muted/50'
                        }`}>
                            <CardContent className="flex flex-col items-center justify-center px-3 py-5 sm:px-4 sm:py-6">
                                {next_stage ? (
                                    <>
                                        {isOngoing ? (
                                            <div className="relative mb-1">
                                                <Play className="h-4 w-4 text-amber-600 dark:text-amber-400" />
                                                <span className="absolute -top-0.5 -right-0.5 flex h-2 w-2">
                                                    <span className="absolute inline-flex h-full w-full animate-ping rounded-full bg-amber-400 opacity-75" />
                                                    <span className="relative inline-flex h-2 w-2 rounded-full bg-amber-500" />
                                                </span>
                                            </div>
                                        ) : (
                                            <Calendar className="mb-1 h-4 w-4 text-brand-600" />
                                        )}
                                        <div className="flex items-center gap-1">
                                            <span className={`text-lg font-bold sm:text-xl ${isOngoing ? 'text-amber-800 dark:text-amber-200' : ''}`}>
                                                Etapa {next_stage.number}
                                            </span>
                                        </div>
                                        {isOngoing ? (
                                            <span className="text-[11px] font-medium text-amber-700 dark:text-amber-400 tracking-wide uppercase">En curso</span>
                                        ) : next_stage.scheduled_start ? (
                                            <Countdown scheduledStart={next_stage.scheduled_start} onExpired={() => setCountdownExpired(true)} />
                                        ) : (
                                            <p className="text-[11px] text-muted-foreground">{next_stage.date}</p>
                                        )}
                                    </>
                                ) : (
                                    <>
                                        <Calendar className="mb-1 h-4 w-4 text-accent-500" />
                                        <span className="text-lg font-bold sm:text-xl">-</span>
                                        <p className="text-[11px] text-muted-foreground">Sin etapa</p>
                                    </>
                                )}
                            </CardContent>
                        </Card>
                    </Link>

                    <Link href={route('classification.index', league.id)} className="block">
                        <Card className="cursor-pointer transition-colors hover:bg-muted/50 h-full">
                            <CardContent className="flex flex-col items-center justify-center px-3 py-5 sm:px-4 sm:py-6">
                                <Trophy className="mb-1 h-4 w-4 text-green-600" />
                                <div className="text-lg font-bold sm:text-xl">
                                    {user_position.rank}º
                                </div>
                                <p className="text-[11px] text-muted-foreground leading-tight text-center">
                                    {user_position.points} pts
                                </p>
                            </CardContent>
                        </Card>
                    </Link>
                </div>

                <Link href={route('predictions.pre-race', league.id)} className="block">
                    <Card className="cursor-pointer transition-colors hover:bg-muted/50">
                        <CardContent className="flex items-center gap-3 p-4">
                            <div className="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-accent-100 dark:bg-accent-900/20">
                                <Target className="h-5 w-5 text-accent-500" />
                            </div>
                            <div className="flex-1">
                                <p className="font-medium">Pronósticos pre-carrera</p>
                                <p className="text-sm text-muted-foreground">
                                    Top 5 GC, maillots y supercombativo
                                </p>
                            </div>
                            <ChevronRight className="h-5 w-5 text-muted-foreground" />
                        </CardContent>
                    </Card>
                </Link>

                <Card>
                    <CardHeader className="pb-3 px-6 pt-6">
                        <CardTitle>Clasificación</CardTitle>
                    </CardHeader>
                    <CardContent className="p-0">
                        {leaderboard.length === 0 ? (
                            <div className="flex flex-col items-center justify-center py-12 text-center px-6">
                                <Users className="h-12 w-12 text-muted-foreground" />
                                <p className="mt-4 text-sm text-muted-foreground">
                                    Aún no hay participantes
                                </p>
                            </div>
                        ) : (
                            <div>
                                <div className="flex items-center gap-3 px-6 py-2 text-xs font-semibold uppercase tracking-wider text-muted-foreground border-b border-muted-200 dark:border-muted-800">
                                    <button type="button" onClick={() => toggleSort('rank')} className="flex w-8 items-center justify-center gap-0.5 hover:text-foreground transition-colors">
                                        Puesto
                                        {sortKey === 'rank' && <span className="text-[10px]">{sortDir === 'asc' ? '▲' : '▼'}</span>}
                                    </button>
                                    <span className="w-8" />
                                    <button type="button" onClick={() => toggleSort('user_name')} className="flex flex-1 items-center gap-0.5 hover:text-foreground transition-colors text-left">
                                        Usuario
                                        {sortKey === 'user_name' && <span className="text-[10px]">{sortDir === 'asc' ? '▲' : '▼'}</span>}
                                    </button>
                                    <span className="w-8 text-center">Var.</span>
                                    <button type="button" onClick={() => toggleSort('points')} className="flex shrink-0 items-center gap-0.5 hover:text-foreground transition-colors">
                                        Puntos
                                        {sortKey === 'points' && <span className="text-[10px]">{sortDir === 'asc' ? '▲' : '▼'}</span>}
                                    </button>
                                </div>
                                {showAllLeaderboard ? (
                                    sortedLeaderboard.map((entry) => (
                                        <Link
                                            key={entry.user_id}
                                            href={route('leagues.members.show', [league.id, entry.user_id])}
                                            className={`
                                                flex items-center gap-3 px-6 py-3 transition-colors hover:bg-muted/50
                                                ${entry.is_current_user
                                                    ? 'bg-accent-100/50 dark:bg-accent-900/10 border-y border-accent-200 dark:border-accent-800'
                                                    : 'border-b border-muted-100 dark:border-muted-800/50 last:border-b-0'
                                                }
                                            `}
                                        >
                                            <div className="flex h-8 w-8 shrink-0 items-center justify-center">
                                                {entry.rank === 1 ? (
                                                    <Trophy className="h-5 w-5 text-yellow-500" />
                                                ) : entry.rank === 2 ? (
                                                    <Trophy className="h-5 w-5 text-gray-400" />
                                                ) : entry.rank === 3 ? (
                                                    <Trophy className="h-5 w-5 text-amber-700" />
                                                ) : (
                                                    <span className="w-6 text-center text-sm font-medium text-muted-foreground">
                                                        {entry.rank}º
                                                    </span>
                                                )}
                                            </div>
                                            <Avatar user={{ name: entry.user_name, avatar: entry.avatar }} size="sm" />
                                            <div className="flex min-w-0 flex-1 items-center gap-2">
                                                <span className="truncate text-sm">
                                                    {entry.user_name}
                                                </span>
                                                {entry.is_current_user && (
                                                    <span className="shrink-0 text-xs text-muted-foreground">(tú)</span>
                                                )}
                                            </div>
                                            <div className="flex items-center gap-3">
                                                <PositionChange change={entry.rank_change} />
                                                <span className="text-sm font-medium tabular-nums">
                                                    {entry.points}
                                                </span>
                                            </div>
                                        </Link>
                                    ))
                                ) : (
                                    buildVisibleLeaderboard(sortedLeaderboard, (usePage().props as any)?.auth?.user?.id).map((item, i) => {
                                        if (item.type === 'ellipsis') {
                                            return (
                                                <div key={`ellipsis-${i}`} className="flex items-center justify-center py-2 text-muted-foreground">
                                                    <span className="text-sm tracking-widest">...</span>
                                                </div>
                                            );
                                        }
                                        if (item.type === 'separator') {
                                            return (
                                                <div key={`sep-${i}`} className="border-t border-muted-200 dark:border-muted-800" />
                                            );
                                        }
                                        const entry = item.entry;
                                        return (
                                            <Link
                                                key={entry.user_id}
                                                href={route('leagues.members.show', [league.id, entry.user_id])}
                                                className={`
                                                    flex items-center gap-3 px-6 py-3 transition-colors hover:bg-muted/50
                                                    ${entry.is_current_user
                                                        ? 'bg-accent-100/50 dark:bg-accent-900/10 border-y border-accent-200 dark:border-accent-800'
                                                        : 'border-b border-muted-100 dark:border-muted-800/50 last:border-b-0'
                                                    }
                                                `}
                                            >
                                                <div className="flex h-8 w-8 shrink-0 items-center justify-center">
                                                    {entry.rank === 1 ? (
                                                        <Trophy className="h-5 w-5 text-yellow-500" />
                                                    ) : entry.rank === 2 ? (
                                                        <Trophy className="h-5 w-5 text-gray-400" />
                                                    ) : entry.rank === 3 ? (
                                                        <Trophy className="h-5 w-5 text-amber-700" />
                                                    ) : (
                                                        <span className="w-6 text-center text-sm font-medium text-muted-foreground">
                                                            {entry.rank}º
                                                        </span>
                                                    )}
                                                </div>
                                                <Avatar user={{ name: entry.user_name, avatar: entry.avatar }} size="sm" />
                                                <div className="flex min-w-0 flex-1 items-center gap-2">
                                                    <span className="truncate text-sm">
                                                        {entry.user_name}
                                                    </span>
                                                    {entry.is_current_user && (
                                                        <span className="shrink-0 text-xs text-muted-foreground">(tú)</span>
                                                    )}
                                                </div>
                                                <div className="flex items-center gap-3">
                                                    <PositionChange change={entry.rank_change} />
                                                    <span className="text-sm font-medium tabular-nums">
                                                        {entry.points}
                                                    </span>
                                                </div>
                                            </Link>
                                        );
                                    })
                                )}
                                {leaderboard.length > 10 && (
                                    <div className="border-t border-muted-200 dark:border-muted-800 px-6 py-3">
                                        <button
                                            type="button"
                                            onClick={() => setShowAllLeaderboard(!showAllLeaderboard)}
                                            className="flex w-full items-center justify-center gap-1.5 text-sm font-medium text-muted-foreground hover:text-foreground transition-colors"
                                        >
                                            {showAllLeaderboard ? 'Mostrar menos' : `Ver todos (${leaderboard.length})`}
                                        </button>
                                    </div>
                                )}
                            </div>
                        )}
                    </CardContent>
                </Card>

                {activity_logs.length > 0 && (
                    <Card>
                        <CardHeader className="pb-3 px-6 pt-6">
                            <CardTitle className="flex items-center gap-2">
                                <Activity className="h-4 w-4 text-muted-foreground" />
                                Actividad
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="px-6 pb-6">
                            <div className="relative space-y-0">
                                {activity_logs.map((log, i) => (
                                    <div key={log.id} className="flex gap-3 pb-4 last:pb-0">
                                        <div className="flex flex-col items-center">
                                            <div className={`flex h-7 w-7 items-center justify-center rounded-full ${activityColors[log.type] ?? 'bg-muted text-muted-foreground'}`}>
                                                {activityIcons[log.type] ?? <Info className="h-4 w-4" />}
                                            </div>
                                            {i < activity_logs.length - 1 && (
                                                <div className="mt-1 h-full w-px bg-border" />
                                            )}
                                        </div>
                                        <div className="flex-1 min-w-0 pt-0.5">
                                            <p className="text-sm font-medium">{log.title}</p>
                                            {log.description && (
                                                <p className="text-xs text-muted-foreground mt-0.5">{log.description}</p>
                                            )}
                                            <p className="text-xs text-muted-foreground/60 mt-0.5">{log.created_at}</p>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        </CardContent>
                    </Card>
                )}
            </div>
        </AppLayout>
    );
}
