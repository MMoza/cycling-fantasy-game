import { useEffect, useRef, useState } from 'react';
import { Head, Link } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';
import Avatar from '@/components/Avatar';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { cn } from '@/lib/utils';
import { ArrowLeft, Target, Medal, ChevronDown, ChevronUp, EyeOff, Zap, Shirt, Calendar, Trophy, TrendingUp, Users } from 'lucide-react';
import { useCountUp } from '@/hooks/useCountUp';

interface PredictionItem {
    id: string;
    name: string;
    type: 'rider' | 'team';
}

interface Prediction {
    category: string;
    label: string;
    items: PredictionItem[];
    points: number;
}

interface StageDetail {
    stage_id: string;
    stage_number: number;
    stage_name: string;
    stage_status: string;
    points: number;
    predictions: Prediction[];
}

interface Props {
    league_id: string;
    league_name: string;
    competition_started: boolean;
    has_stage_predictions: boolean;
    user: UserProfile;
    global_stats: GlobalStats;
    points_history: PointsHistoryEntry[];
    pre_race_predictions: Prediction[];
    stage_details: StageDetail[];
}

interface UserProfile {
    id: string;
    name: string;
    avatar?: string | null;
    rank: number | string;
    points: number;
    behind_leader: number;
    is_online?: boolean;
    last_active_at?: string | null;
    member_since: string;
}

interface GlobalStats {
    stages_participated: number;
    stage_winners_guessed: number;
    best_stage: {
        stage_number: number;
        points: number;
        predictions: Prediction[];
    } | null;
}

interface PointsHistoryEntry {
    stage_number: number;
    points: number;
    total: number;
    leader_total: number;
}

const CATEGORY_LABELS: Record<string, string> = {
    gc_top_5: 'Top 5 General',
    points_winner: 'Maillot Verde',
    mountains_winner: 'Montaña',
    youth_winner: 'Maillot Blanco',
    teams_winner: 'Equipos',
    super_combativo: 'Supercombativo',
    stage_winner: 'Ganador de etapa',
    stage_second: '2º clasificado',
    stage_third: '3º clasificado',
    stage_leader: 'Líder general',
    stage_combativo: 'Combativo',
};

const STAGE_ICONS: Record<string, React.ReactNode> = {
    stage_winner: <Medal className="h-4 w-4 text-yellow-500 shrink-0" aria-label="1º" />,
    stage_second: <Medal className="h-4 w-4 text-slate-400 shrink-0" aria-label="2º" />,
    stage_third: <Medal className="h-4 w-4 text-amber-700 shrink-0" aria-label="3º" />,
    stage_combativo: <Zap className="h-4 w-4 text-red-500 shrink-0" aria-label="Combativo" />,
    stage_leader: <Shirt className="h-4 w-4 text-yellow-500 shrink-0" aria-label="Líder" />,
};

function formatMemberSince(iso: string): string {
    const d = new Date(iso);
    return d.toLocaleDateString('es-ES', { month: 'long', year: 'numeric' });
}

function CountUpStat({ icon, label, value, color = 'emerald' }: { icon: React.ReactNode; label: string; value: number; color?: string }) {
    const animated = useCountUp(value, 2000);
    const colorMap: Record<string, { bg: string; icon: string; value: string }> = {
        emerald: { bg: 'bg-emerald-500/5 dark:bg-emerald-500/5', icon: 'text-emerald-600 dark:text-emerald-400', value: 'text-foreground' },
        blue: { bg: 'bg-blue-500/5 dark:bg-blue-500/5', icon: 'text-blue-600 dark:text-blue-400', value: 'text-foreground' },
        amber: { bg: 'bg-amber-500/5 dark:bg-amber-500/5', icon: 'text-amber-600 dark:text-amber-400', value: 'text-foreground' },
    };
    const c = colorMap[color] ?? colorMap.emerald;

    return (
        <div className={cn('flex flex-col items-center gap-1.5 rounded-xl px-3 py-4 text-center', c.bg)}>
            <div className={cn('flex h-10 w-10 items-center justify-center', c.icon)}>
                {icon}
            </div>
            <span className="text-[11px] font-medium leading-tight text-muted-foreground">{label}</span>
            <span className={cn('text-2xl font-bold tabular-nums leading-none', c.value)}>{animated}</span>
        </div>
    );
}

function PointsChart({ history }: { history: PointsHistoryEntry[] }) {
    const [isVisible, setIsVisible] = useState(false);
    const [hovered, setHovered] = useState<number | null>(null);
    const containerRef = useRef<HTMLDivElement>(null);

    useEffect(() => {
        const timer = setTimeout(() => setIsVisible(true), 200);
        return () => clearTimeout(timer);
    }, []);

    if (history.length === 0) return null;

    const width = 600;
    const height = 200;
    const padding = { top: 16, right: 20, bottom: 36, left: 50 };
    const chartWidth = width - padding.left - padding.right;
    const chartHeight = height - padding.top - padding.bottom;

    const maxTotal = Math.max(...history.map((h) => Math.max(h.total, h.leader_total ?? 0)), 1);

    const userPoints = history.map((h, i) => ({
        x: padding.left + (history.length === 1 ? chartWidth / 2 : (i / (history.length - 1)) * chartWidth),
        y: padding.top + chartHeight - (h.total / maxTotal) * chartHeight,
        ...h,
    }));

    const leaderPoints = history.map((h, i) => ({
        x: padding.left + (history.length === 1 ? chartWidth / 2 : (i / (history.length - 1)) * chartWidth),
        y: padding.top + chartHeight - ((h.leader_total ?? 0) / maxTotal) * chartHeight,
        ...h,
    }));

    const userLinePath = userPoints.map((p, i) => `${i === 0 ? 'M' : 'L'} ${p.x} ${p.y}`).join(' ');
    const leaderLinePath = leaderPoints.map((p, i) => `${i === 0 ? 'M' : 'L'} ${p.x} ${p.y}`).join(' ');

    const yTicks = 5;
    const yTickValues = Array.from({ length: yTicks + 1 }, (_, i) => Math.round((maxTotal / yTicks) * i));

    const tooltipData = hovered !== null ? userPoints[hovered] : null;

    return (
        <div className="w-full overflow-x-auto">
            <div ref={containerRef} className="relative inline-block w-full min-w-[300px]">
                <svg viewBox={`0 0 ${width} ${height}`} className="w-full" preserveAspectRatio="xMidYMid meet">
                    <defs>
                        <linearGradient id="areaGradient" x1="0" y1="0" x2="0" y2="1">
                            <stop offset="0%" stopColor="rgb(16, 185, 129)" stopOpacity="0.3" />
                            <stop offset="100%" stopColor="rgb(16, 185, 129)" stopOpacity="0.02" />
                        </linearGradient>
                    </defs>

                    {yTickValues.map((tick) => {
                        const y = padding.top + chartHeight - (tick / maxTotal) * chartHeight;
                        return (
                            <g key={tick}>
                                <line x1={padding.left} y1={y} x2={width - padding.right} y2={y} stroke="currentColor" className="text-muted-200 dark:text-muted-800" strokeDasharray="3,3" />
                                <text x={padding.left - 8} y={y + 4} textAnchor="end" className="fill-muted-foreground text-[10px]">{tick}</text>
                            </g>
                        );
                    })}

                    {userPoints.map((p, i) => (
                        <text key={`x-${i}`} x={p.x} y={height - 6} textAnchor="middle" className="fill-muted-foreground text-[10px]">{p.stage_number}ª</text>
                    ))}

                    {/* User area */}
                    <path
                        d={`${userLinePath} L ${userPoints[userPoints.length - 1].x} ${padding.top + chartHeight} L ${userPoints[0].x} ${padding.top + chartHeight} Z`}
                        fill="url(#areaGradient)"
                        className={cn('transition-opacity duration-1000', isVisible ? 'opacity-100' : 'opacity-0')}
                    />

                    {/* Leader line (dashed) */}
                    <path
                        d={leaderLinePath}
                        fill="none"
                        stroke="rgb(156, 163, 175)"
                        strokeWidth="2"
                        strokeLinecap="round"
                        strokeLinejoin="round"
                        strokeDasharray="6,4"
                        className={cn('transition-opacity duration-1000 delay-300', isVisible ? 'opacity-60' : 'opacity-0')}
                    />

                    {/* User line */}
                    <path
                        d={userLinePath}
                        fill="none"
                        stroke="rgb(16, 185, 129)"
                        strokeWidth="2.5"
                        strokeLinecap="round"
                        strokeLinejoin="round"
                        className={cn('transition-opacity duration-1000', isVisible ? 'opacity-100' : 'opacity-0')}
                    />

                    {/* Leader dots */}
                    {leaderPoints.map((p, i) => (
                        <circle
                            key={`ld-${i}`}
                            cx={p.x}
                            cy={p.y}
                            r={hovered === i ? 4 : 3}
                            fill="white"
                            stroke="rgb(156, 163, 175)"
                            strokeWidth="1.5"
                            className={cn('transition-all duration-200', isVisible ? 'opacity-100 scale-100' : 'opacity-0 scale-0')}
                            style={{ transformOrigin: `${p.x}px ${p.y}px` }}
                        />
                    ))}

                    {/* User dots */}
                    {userPoints.map((p, i) => (
                        <circle
                            key={`ud-${i}`}
                            cx={p.x}
                            cy={p.y}
                            r={hovered === i ? 6 : 4}
                            fill="white"
                            stroke="rgb(16, 185, 129)"
                            strokeWidth={hovered === i ? 3 : 2}
                            className={cn('transition-all duration-200', isVisible ? 'opacity-100 scale-100' : 'opacity-0 scale-0')}
                            style={{ transformOrigin: `${p.x}px ${p.y}px` }}
                        />
                    ))}

                    {/* Vertical line on hover */}
                    {hovered !== null && (
                        <line
                            x1={userPoints[hovered].x}
                            y1={padding.top}
                            x2={userPoints[hovered].x}
                            y2={padding.top + chartHeight}
                            stroke="rgb(156, 163, 175)"
                            strokeWidth="1"
                            strokeDasharray="4,3"
                            className="opacity-40"
                        />
                    )}

                    {/* Invisible hit areas for hover/touch */}
                    {userPoints.map((p, i) => (
                        <rect
                            key={`hit-${i}`}
                            x={p.x - (chartWidth / userPoints.length) / 2}
                            y={padding.top}
                            width={chartWidth / userPoints.length}
                            height={chartHeight}
                            fill="transparent"
                            onMouseEnter={() => setHovered(i)}
                            onMouseLeave={() => setHovered(null)}
                            onTouchStart={() => setHovered(i)}
                            onTouchEnd={() => setTimeout(() => setHovered(null), 1500)}
                        />
                    ))}
                </svg>

                {/* Tooltip */}
                {tooltipData && (
                    <div
                        className="pointer-events-none absolute z-10 -translate-x-1/2 rounded-lg border bg-white px-3 py-2 text-xs shadow-lg dark:border-muted-700 dark:bg-muted-900"
                        style={{
                            left: `${(tooltipData.x / width) * 100}%`,
                            top: `${(tooltipData.y / height) * 100 - 12}%`,
                            transform: 'translate(-50%, -100%)',
                        }}
                    >
                        <p className="font-semibold text-foreground">Etapa {tooltipData.stage_number}</p>
                        <div className="mt-1 space-y-0.5">
                            <p className="text-emerald-600 dark:text-emerald-400">
                                Tú: {tooltipData.total} pts
                                {tooltipData.points > 0 && <span className="ml-1 text-muted-foreground">(+{tooltipData.points})</span>}
                            </p>
                            <p className="text-muted-foreground">
                                Líder: {tooltipData.leader_total} pts
                            </p>
                        </div>
                    </div>
                )}
            </div>

            {/* Legend */}
            <div className="mt-2 flex items-center justify-center gap-4 text-xs text-muted-foreground">
                <span className="flex items-center gap-1.5">
                    <span className="inline-block h-0 w-5 border-t-2 border-emerald-500" />
                    Tú
                </span>
                <span className="flex items-center gap-1.5">
                    <span className="inline-block h-0 w-5 border-t-2 border-gray-400 border-dashed" />
                    Líder
                </span>
            </div>
        </div>
    );
}

function formatLastActive(isOnline?: boolean, lastActiveAt?: string | null): string {
    if (isOnline) return 'Online';
    if (!lastActiveAt) return '';

    const diff = Date.now() - new Date(lastActiveAt).getTime();
    const minutes = Math.floor(diff / 60000);
    if (minutes < 1) return 'Hace un momento';
    if (minutes < 60) return `Hace ${minutes} min`;

    const hours = Math.floor(minutes / 60);
    if (hours < 24) return `Hace ${hours}h`;

    const days = Math.floor(hours / 24);
    return `Hace ${days}d`;
}

function RiderAvatar({ name, size = 'xs' }: { name: string; size?: 'xs' | 'sm' }) {
    const sizeClasses = {
        xs: 'h-5 w-5 text-[10px]',
        sm: 'h-6 w-6 text-xs',
    };

    const initials = name
        .split(' ')
        .slice(0, 2)
        .map((p) => p[0])
        .join('')
        .toUpperCase();

    return (
        <div
            className={cn(
                'flex shrink-0 items-center justify-center rounded-full bg-neutral-400 font-medium text-white',
                sizeClasses[size],
            )}
        >
            {initials}
        </div>
    );
}

export default function Show({
    league_id,
    league_name,
    competition_started,
    has_stage_predictions,
    user,
    global_stats,
    points_history,
    pre_race_predictions,
    stage_details,
}: Props) {
    const [expandedStage, setExpandedStage] = useState<string | null>(null);
    const [expandedCategories, setExpandedCategories] = useState<Set<string>>(new Set());
    const [expandedBestStage, setExpandedBestStage] = useState(false);
    const [expandedPreRace, setExpandedPreRace] = useState(true);
    const [expandedStagePredictions, setExpandedStagePredictions] = useState(true);
    const totalPreRacePoints = pre_race_predictions.reduce((sum, p) => sum + p.points, 0);

    const toggleCategory = (category: string) => {
        setExpandedCategories((prev) => {
            const next = new Set(prev);
            if (next.has(category)) {
                next.delete(category);
            } else {
                next.add(category);
            }
            return next;
        });
    };

    return (
        <AppLayout>
            <Head title={`${user.name} — ${league_name}`} />

            <div className="mx-auto max-w-2xl space-y-6 px-4 py-6 sm:px-0">
                <Link
                    href={route('classification.index', league_id)}
                    className="inline-flex items-center gap-1 text-sm text-muted-foreground hover:text-foreground"
                >
                    <ArrowLeft className="h-4 w-4" />
                    Clasificación
                </Link>

                <Card>
                    <div className="flex items-center gap-4 p-6">
                        <Avatar user={{ name: user.name, avatar: user.avatar }} size="lg" isOnline={user.is_online} />
                        <div className="min-w-0 flex-1">
                            <h1 className="text-xl font-bold truncate">{user.name}</h1>
                            <div className="mt-1 flex items-center gap-2">
                                {user.is_online ? (
                                    <span className="flex items-center gap-1 text-xs text-green-600">
                                        <span className="inline-block h-1.5 w-1.5 rounded-full bg-green-500" />
                                        Online
                                    </span>
                                ) : user.last_active_at ? (
                                    <span className="text-xs text-muted-foreground">
                                        Última conexión: {formatLastActive(user.is_online, user.last_active_at)}
                                    </span>
                                ) : null}
                            </div>
                            <div className="mt-1 flex items-center gap-2">
                                <Calendar className="h-3.5 w-3.5 text-muted-foreground" />
                                <span className="text-sm text-muted-foreground">
                                    Miembro desde {formatMemberSince(user.member_since)}
                                </span>
                            </div>
                            <div className="mt-2 flex flex-wrap items-center gap-3">
                                <Badge variant="secondary" className="text-sm">
                                    {user.rank}º puesto
                                </Badge>
                                <span className="text-lg font-semibold tabular-nums">{user.points}</span>
                                {user.behind_leader > 0 && (
                                    <span className="text-sm text-muted-foreground">
                                        a {user.behind_leader} pts
                                    </span>
                                )}
                            </div>
                        </div>
                    </div>
                </Card>

                {/* Global Stats */}
                <div className="grid grid-cols-3 gap-3">
                    <CountUpStat icon={<Medal className="h-7 w-7" />} label="Etapas" value={global_stats.stages_participated} color="blue" />
                    <CountUpStat icon={<Target className="h-7 w-7" />} label="Aciertos" value={global_stats.stage_winners_guessed} color="amber" />
                    <CountUpStat icon={<TrendingUp className="h-7 w-7" />} label="Puntos" value={user.points} color="emerald" />
                </div>

                {/* Best Stage */}
                {global_stats.best_stage && (
                    <Card className="overflow-hidden">
                        <button
                            type="button"
                            onClick={() => setExpandedBestStage(!expandedBestStage)}
                            className="w-full"
                        >
                            <CardContent className="p-4">
                                <div className="flex items-center gap-3">
                                    <div className="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-yellow-100 text-yellow-600 dark:bg-yellow-900/30 dark:text-yellow-400">
                                        <Trophy className="h-6 w-6" />
                                    </div>
                                    <div className="min-w-0 flex-1 text-left">
                                        <p className="text-xs text-muted-foreground">Mejor etapa</p>
                                        <p className="text-lg font-bold">
                                            Etapa {global_stats.best_stage.stage_number}
                                            <span className="ml-2 text-sm font-semibold text-emerald-600 dark:text-emerald-400">
                                                +{global_stats.best_stage.points} pts
                                            </span>
                                        </p>
                                    </div>
                                    <ChevronDown className={cn(
                                        'h-5 w-5 text-muted-foreground transition-transform duration-300',
                                        expandedBestStage && 'rotate-180',
                                    )} />
                                </div>
                            </CardContent>
                        </button>

                        <div className={cn(
                            'grid transition-all duration-300 ease-in-out',
                            expandedBestStage ? 'grid-rows-[1fr]' : 'grid-rows-[0fr]',
                        )}>
                            <div className="overflow-hidden">
                                <div className="border-t border-muted-200 dark:border-muted-700 px-4 py-3 space-y-2">
                                    {global_stats.best_stage.predictions.map((p, i) => {
                                        const isMulti = p.items.length > 1;

                                        return (
                                            <div
                                                key={i}
                                                className={cn(
                                                    'flex items-center gap-3 rounded-lg px-3 py-2 transition-all duration-200',
                                                    'bg-muted/40 hover:bg-muted/70',
                                                )}
                                                style={{ animationDelay: `${i * 50}ms` }}
                                            >
                                                <span className="w-5 shrink-0">
                                                    {STAGE_ICONS[p.category] ?? (
                                                        <span className="text-[10px] text-muted-foreground">
                                                            {CATEGORY_LABELS[p.category]?.split(' ')[0]}
                                                        </span>
                                                    )}
                                                </span>
                                                <div className="min-w-0 flex-1">
                                                    <p className="text-[10px] text-muted-foreground leading-tight">
                                                        {CATEGORY_LABELS[p.category] ?? p.category}
                                                    </p>
                                                    {isMulti ? (
                                                        <div className="flex flex-wrap gap-1 mt-0.5">
                                                            {p.items.map((item, idx) => (
                                                                <span key={idx} className="flex items-center gap-1">
                                                                    <RiderAvatar name={item.name} size="xs" />
                                                                    <span className="text-xs font-medium truncate max-w-[120px]">
                                                                        {item.name}
                                                                    </span>
                                                                </span>
                                                            ))}
                                                        </div>
                                                    ) : (
                                                        <div className="flex items-center gap-1.5 mt-0.5">
                                                            {p.items[0] && <RiderAvatar name={p.items[0].name} size="xs" />}
                                                            <span className="text-xs font-medium truncate">
                                                                {p.label}
                                                            </span>
                                                        </div>
                                                    )}
                                                </div>
                                                <span className={cn(
                                                    'text-xs font-semibold tabular-nums shrink-0',
                                                    p.points > 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-muted-foreground',
                                                )}>
                                                    {p.points > 0 ? `+${p.points}` : '0'}
                                                </span>
                                            </div>
                                        );
                                    })}
                                </div>
                            </div>
                        </div>
                    </Card>
                )}

                {/* Points History Chart */}
                {points_history.length > 0 && (
                    <Card>
                        <CardHeader className="px-6 pt-1 pb-3">
                            <CardTitle className="flex items-center gap-2 text-base">
                                <TrendingUp className="h-4 w-4" />
                                Histórico de puntos
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="px-6 pb-4">
                            <PointsChart history={points_history} />
                        </CardContent>
                    </Card>
                )}

                {competition_started && pre_race_predictions.length > 0 && (
                    <Card className="overflow-hidden">
                        <button
                            type="button"
                            onClick={() => setExpandedPreRace(!expandedPreRace)}
                            className="w-full"
                        >
                            <CardHeader className="px-6 pt-5 pb-4">
                                <CardTitle className="flex items-center gap-2 text-lg font-bold">
                                    <Target className="h-5 w-5 text-emerald-600 dark:text-emerald-400" />
                                    Predicciones Generales
                                    <span className="ml-auto text-sm font-medium text-muted-foreground tabular-nums">
                                        {totalPreRacePoints} pts
                                    </span>
                                    <ChevronDown className={cn(
                                        'h-5 w-5 text-muted-foreground transition-transform duration-300',
                                        expandedPreRace && 'rotate-180',
                                    )} />
                                </CardTitle>
                            </CardHeader>
                        </button>

                        <div className={cn(
                            'grid transition-all duration-300 ease-in-out',
                            expandedPreRace ? 'grid-rows-[1fr]' : 'grid-rows-[0fr]',
                        )}>
                            <div className="overflow-hidden">
                                <CardContent className="px-6 pb-4 pt-0">
                                    <div className="divide-y divide-muted-100 dark:divide-muted-800">
                                        {pre_race_predictions.map((p, i) => {
                                            const isMulti = p.items.length > 1;
                                            const isExpanded = expandedCategories.has(p.category);

                                            return (
                                                <div key={i}>
                                                    <div className="flex items-center justify-between py-2.5">
                                                        <span className="text-sm text-muted-foreground">
                                                            {CATEGORY_LABELS[p.category] ?? p.category}
                                                        </span>
                                                        <div className="flex items-center gap-2 text-right ml-4 min-w-0">
                                                            {isMulti ? (
                                                                <button
                                                                    onClick={(e) => { e.stopPropagation(); toggleCategory(p.category); }}
                                                                    className="flex items-center text-right"
                                                                >
                                                                    {isExpanded ? (
                                                                        <ChevronUp className="h-3.5 w-3.5 shrink-0 text-muted-foreground" />
                                                                    ) : (
                                                                        <ChevronDown className="h-3.5 w-3.5 shrink-0 text-muted-foreground" />
                                                                    )}
                                                                </button>
                                                            ) : (
                                                                <div className="flex items-center gap-2">
                                                                    {p.items[0]?.type === 'rider' && (
                                                                        <RiderAvatar name={p.items[0].name} size="xs" />
                                                                    )}
                                                                    <span className="text-sm font-medium truncate max-w-[180px]">
                                                                        {p.label}
                                                                    </span>
                                                                </div>
                                                            )}
                                                            {p.points > 0 && (
                                                                <span className="text-xs font-semibold tabular-nums text-green-600 shrink-0">
                                                                    +{p.points}
                                                                </span>
                                                            )}
                                                        </div>
                                                    </div>
                                                    {isMulti && isExpanded && (
                                                        <div className="pb-2 pl-4 space-y-1">
                                                            {p.items.map((item, idx) => (
                                                                <div
                                                                    key={idx}
                                                                    className="flex items-center gap-2 py-0.5"
                                                                >
                                                                    <RiderAvatar name={item.name} size="xs" />
                                                                    <span className="text-sm text-muted-foreground">
                                                                        {item.name}
                                                                    </span>
                                                                </div>
                                                            ))}
                                                        </div>
                                                    )}
                                                </div>
                                            );
                                        })}
                                    </div>
                                </CardContent>
                            </div>
                        </div>
                    </Card>
                )}

                {!competition_started && !has_stage_predictions && (
                    <Card>
                        <CardContent className="flex flex-col items-center justify-center py-12 text-center">
                            <EyeOff className="h-10 w-10 text-muted-foreground" />
                            <p className="mt-3 text-sm text-muted-foreground">
                                Las predicciones se mostrarán cuando la competición comience
                            </p>
                        </CardContent>
                    </Card>
                )}

                {has_stage_predictions && stage_details.length > 0 && (
                    <Card className="overflow-hidden">
                        <button
                            type="button"
                            onClick={() => setExpandedStagePredictions(!expandedStagePredictions)}
                            className="w-full"
                        >
                            <CardHeader className="px-6 pt-5 pb-4">
                                <CardTitle className="flex items-center gap-2 text-lg font-bold">
                                    <Medal className="h-5 w-5 text-emerald-600 dark:text-emerald-400" />
                                    Predicciones por Etapas
                                    <span className="ml-auto text-sm font-medium text-muted-foreground tabular-nums">
                                        {stage_details.filter((s) => s.points > 0).length} etapas
                                    </span>
                                    <ChevronDown className={cn(
                                        'h-5 w-5 text-muted-foreground transition-transform duration-300',
                                        expandedStagePredictions && 'rotate-180',
                                    )} />
                                </CardTitle>
                            </CardHeader>
                        </button>

                        <div className={cn(
                            'grid transition-all duration-300 ease-in-out',
                            expandedStagePredictions ? 'grid-rows-[1fr]' : 'grid-rows-[0fr]',
                        )}>
                            <div className="overflow-hidden">
                                <CardContent className="p-0">
                                    {stage_details.map((stage) => {
                                        const isFinished = stage.stage_status === 'finished';

                                        return (
                                            <div key={stage.stage_id}>
                                                <button
                                                    onClick={() => setExpandedStage(expandedStage === stage.stage_id ? null : stage.stage_id)}
                                                    className="flex w-full items-center justify-between gap-2 px-6 py-3 text-left hover:bg-muted/50 border-b border-muted-100 dark:border-muted-800 last:border-b-0"
                                                >
                                                    <div className="flex items-center gap-2 min-w-0">
                                                        <span className="text-sm font-medium shrink-0">
                                                            Etapa {stage.stage_number}
                                                        </span>
                                                        <span className="text-sm text-muted-foreground truncate">
                                                            {stage.stage_name}
                                                        </span>
                                                    </div>
                                                    <div className="flex items-center gap-2 shrink-0">
                                                        {(stage.points > 0 || isFinished) && (
                                                            <span
                                                                className={cn(
                                                                    'text-xs font-semibold tabular-nums shrink-0',
                                                                    stage.points > 0 ? 'text-green-600' : 'text-muted-foreground',
                                                                )}
                                                            >
                                                                {stage.points > 0 ? `+${stage.points}` : '0'}
                                                            </span>
                                                        )}
                                                        {expandedStage === stage.stage_id ? (
                                                            <ChevronUp className="h-4 w-4 text-muted-foreground" />
                                                        ) : (
                                                            <ChevronDown className="h-4 w-4 text-muted-foreground" />
                                                        )}
                                                    </div>
                                                </button>
                                                {expandedStage === stage.stage_id && (
                                                    <div className="divide-y divide-muted-100 dark:divide-muted-800 bg-muted/30">
                                                        {stage.predictions.length === 0 ? (
                                                            <div className="px-10 py-3 text-sm text-muted-foreground">
                                                                Sin predicciones para esta etapa
                                                            </div>
                                                        ) : (
                                                            stage.predictions.map((p, j) => (
                                                                <div key={j} className="flex items-center gap-3 px-10 py-2">
                                                                    <span className="w-6 shrink-0">
                                                                        {STAGE_ICONS[p.category] ?? (
                                                                            <span className="text-sm text-muted-foreground">
                                                                                {CATEGORY_LABELS[p.category] ?? p.category}
                                                                            </span>
                                                                        )}
                                                                    </span>
                                                                    {p.items[0]?.type === 'rider' && (
                                                                        <RiderAvatar name={p.items[0].name} size="xs" />
                                                                    )}
                                                                    <span className="text-sm font-medium truncate min-w-0">
                                                                        {p.label}
                                                                    </span>
                                                                    {(p.points > 0 || isFinished) && (
                                                                        <span
                                                                            className={cn(
                                                                                'text-xs font-semibold tabular-nums shrink-0 ml-auto',
                                                                                p.points > 0 ? 'text-green-600' : 'text-muted-foreground',
                                                                            )}
                                                                        >
                                                                            {p.points > 0 ? `+${p.points}` : '0'}
                                                                        </span>
                                                                    )}
                                                                </div>
                                                            ))
                                                        )}
                                                    </div>
                                                )}
                                            </div>
                                        );
                                    })}
                                </CardContent>
                            </div>
                        </div>
                    </Card>
                )}
            </div>
        </AppLayout>
    );
}
