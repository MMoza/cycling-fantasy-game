import { Head, Link } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';
import { FileCheck, FileX, MapPin, Ruler, TrendingUp, Calendar, Trophy } from 'lucide-react';
import { StageTypeIcon } from '@/components/ui/stage-type-icon';
import { cn } from '@/lib/utils';

interface Stage {
    id: string;
    number: number;
    name: string;
    date: string;
    type: string;
    type_value: string;
    distance: number | null;
    elevation_gain: number | null;
    origin: string;
    destination: string;
    status: string;
    difficulty: number | null;
    profile_image: string | null;
    scheduled_start: string | null;
}

interface IndexProps {
    league_id: string;
    league_name: string;
    competition: string;
    year: number;
    stages: Stage[];
    predictionsPerStage: Record<string, boolean>;
    pointsPerStage: Record<string, number>;
}

const STATUS_STYLES: Record<string, { card: string; num: string }> = {
    finished: {
        card: 'bg-muted/20 border-muted/40 hover:bg-muted/30',
        num: 'bg-muted/50 text-muted-foreground',
    },
    ongoing: {
        card: 'uci-rainbow-border bg-accent/[0.04]',
        num: 'bg-accent text-accent-foreground',
    },
    upcoming: {
        card: 'bg-background border-border hover:bg-accent/5',
        num: 'bg-secondary/70 text-secondary-foreground',
    },
};

function DifficultyStars({ difficulty }: { difficulty: number | null }) {
    if (!difficulty) return null;
    return (
        <span className="ml-2 inline-flex items-center gap-0.5 rounded-full bg-black/85 px-2 py-0.5 text-xs leading-none">
            {Array.from({ length: 3 }, (_, i) => (
                <span key={i} className={cn(i < difficulty ? 'text-yellow-400' : 'text-white/20')}>★</span>
            ))}
        </span>
    );
}

export default function Index({ league_id, league_name, competition, year, stages, predictionsPerStage, pointsPerStage }: IndexProps) {
    const isOngoing = (stage: Stage) => stage.status === 'ongoing';
    const isFinished = (stage: Stage) => stage.status === 'finished';
    const hasPrediction = (stage: Stage) => predictionsPerStage[stage.id] === true;
    const points = (stage: Stage) => pointsPerStage[stage.id] ?? 0;
    const elevation = (stage: Stage) => stage.elevation_gain != null ? `${stage.elevation_gain.toLocaleString()} m` : null;

    return (
        <AppLayout>
            <Head title={`Etapas — ${competition} ${year}`} />

            <div className="space-y-6">
                <div>
                    <h1 className="text-2xl font-semibold tracking-tight">{competition} {year}</h1>
                    <p className="text-muted-foreground">{league_name}</p>
                </div>

                <div className="space-y-2">
                    {stages.map((stage) => {
                        const style = isOngoing(stage) ? STATUS_STYLES.ongoing : isFinished(stage) ? STATUS_STYLES.finished : STATUS_STYLES.upcoming;
                        const elev = elevation(stage);

                        return (
                            <Link
                                key={stage.id}
                                href={route('stages.show', [league_id, stage.id])}
                                className={cn(
                                    'flex items-start gap-4 rounded-xl border p-4 transition-colors',
                                    style.card,
                                    isFinished(stage) && 'opacity-70',
                                )}
                            >
                                <div className="flex shrink-0 flex-col items-center gap-0.5">
                                    <div className={cn(
                                        'flex h-9 w-9 items-center justify-center rounded-lg text-sm font-bold',
                                        style.num,
                                    )}>
                                        {stage.number}
                                    </div>
                                    <StageTypeIcon type={stage.type_value} className="h-3.5 w-3.5 text-muted-foreground/50" />
                                </div>

                                <div className="flex min-w-0 flex-1 flex-col gap-1">
                                    <div className="flex items-center gap-1.5">
                                        <span className={cn(
                                            'truncate text-sm font-medium',
                                            isOngoing(stage) && 'text-accent-foreground',
                                            isFinished(stage) && 'text-muted-foreground',
                                        )}>
                                            {stage.name || `Etapa ${stage.number}`}
                                        </span>
                                        <DifficultyStars difficulty={stage.difficulty} />
                                    </div>

                                    <div className="flex items-center gap-1.5 text-xs text-muted-foreground/70">
                                        <MapPin className="h-3 w-3 shrink-0" />
                                        <span className="truncate">{stage.origin} → {stage.destination}</span>
                                    </div>

                                    <div className="flex flex-wrap items-center gap-x-2 gap-y-0.5 text-xs text-muted-foreground/60">
                                        <span className="flex items-center gap-1">
                                            <Calendar className="h-3 w-3" />
                                            {stage.date}
                                            {stage.scheduled_start && (
                                                <span className="tabular-nums">
                                                    {new Date(stage.scheduled_start).toLocaleTimeString('es-ES', { hour: '2-digit', minute: '2-digit', timeZone: 'UTC' })}
                                                </span>
                                            )}
                                        </span>
                                        <span className="text-muted-foreground/30">|</span>
                                        {stage.distance && (
                                            <>
                                                <span className="flex items-center gap-1">
                                                    <Ruler className="h-3 w-3" />
                                                    {stage.distance} km
                                                </span>
                                                <span className="text-muted-foreground/30">|</span>
                                            </>
                                        )}
                                        {elev && (
                                            <span className="flex items-center gap-1">
                                                <TrendingUp className="h-3 w-3" />
                                                {elev}
                                            </span>
                                        )}
                                    </div>
                                </div>

                                <div className="flex shrink-0 items-center gap-3 self-center">
                                    {isFinished(stage) ? (
                                        <>
                                            <Trophy className={cn('h-4 w-4', points(stage) > 0 ? 'text-accent-500' : 'text-muted-foreground/30')} />
                                            <span className={cn(
                                                'text-sm font-semibold tabular-nums',
                                                points(stage) > 0 ? 'text-accent-foreground' : 'text-muted-foreground/40',
                                            )}>
                                                {points(stage)} pts
                                            </span>
                                        </>
                                    ) : isOngoing(stage) ? (
                                        <span className="inline-flex items-center rounded-full border border-accent/40 bg-accent/[0.08] px-2.5 py-0.5 text-xs font-medium text-accent-foreground">
                                            En curso
                                        </span>
                                    ) : (
                                        hasPrediction(stage) ? (
                                            <FileCheck className="h-4 w-4 text-green-500" />
                                        ) : (
                                            <FileX className="h-4 w-4 text-muted-foreground/30" />
                                        )
                                    )}
                                </div>
                            </Link>
                        );
                    })}

                    {stages.length === 0 && (
                        <div className="flex flex-col items-center justify-center py-12 text-center">
                            <p className="text-muted-foreground">No hay etapas disponibles</p>
                        </div>
                    )}
                </div>
            </div>
        </AppLayout>
    );
}
