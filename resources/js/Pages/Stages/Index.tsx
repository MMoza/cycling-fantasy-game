import { Head, Link } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';
import { Badge } from '@/components/ui/badge';
import { Calendar, FileCheck, FileX, MapPin, Ruler, Star, Trophy, ArrowRight } from 'lucide-react';
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
    origin: string;
    destination: string;
    status: string;
    difficulty: number | null;
    profile_image: string | null;
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

const STATUS_STYLES: Record<string, { bg: string; border: string; text: string; badge: string }> = {
    finished: { bg: 'bg-muted/30', border: 'border-muted', text: 'text-muted-foreground', badge: 'bg-muted-foreground/10 text-muted-foreground border-muted-foreground/20' },
    ongoing: { bg: 'bg-accent/10', border: 'border-accent', text: 'text-accent-foreground', badge: 'bg-accent/20 text-accent-foreground border-accent/30' },
    upcoming: { bg: 'bg-background', border: 'border-border', text: 'text-foreground', badge: 'bg-secondary text-secondary-foreground border-border' },
};

export default function Index({ league_id, league_name, competition, year, stages, predictionsPerStage, pointsPerStage }: IndexProps) {
    const isOngoing = (stage: Stage) => stage.status === 'ongoing';
    const isFinished = (stage: Stage) => stage.status === 'finished';
    const hasPrediction = (stage: Stage) => predictionsPerStage[stage.id] === true;
    const points = (stage: Stage) => pointsPerStage[stage.id] ?? 0;

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

                        return (
                            <Link
                                key={stage.id}
                                href={route('stages.show', [league_id, stage.id])}
                                className={cn(
                                    'flex items-center gap-4 rounded-xl border p-4 transition-colors hover:bg-accent/5',
                                    style.bg,
                                    style.border,
                                )}
                            >
                                <div className={cn(
                                    'flex flex-col items-center justify-center shrink-0',
                                )}>
                                    <div className={cn(
                                        'flex h-10 w-10 items-center justify-center rounded-lg font-bold text-base',
                                        isOngoing(stage) ? 'bg-accent text-accent-foreground' : isFinished(stage) ? 'bg-muted text-muted-foreground' : 'bg-secondary text-secondary-foreground',
                                    )}>
                                        {stage.number}
                                    </div>
                                    <StageTypeIcon type={stage.type_value} className="mt-0.5 h-3.5 w-3.5 text-muted-foreground/60" />
                                </div>

                                <div className="flex-1 min-w-0">
                                    <div className="flex items-center gap-2">
                                        <span className={cn('font-medium truncate', style.text)}>{stage.name || `Etapa ${stage.number}`}</span>
                                    </div>
                                    <div className={cn('flex flex-wrap items-center gap-x-3 gap-y-0.5 text-xs mt-0.5', isFinished(stage) ? 'text-muted-foreground/70' : 'text-muted-foreground')}>
                                        <span className="flex items-center gap-1">
                                            <Calendar className="h-3 w-3" />
                                            {stage.date}
                                        </span>
                                        <span className="flex items-center gap-1">
                                            <MapPin className="h-3 w-3" />
                                            {stage.origin} → {stage.destination}
                                        </span>
                                        {stage.distance && (
                                            <span className="flex items-center gap-1">
                                                <Ruler className="h-3 w-3" />
                                                {stage.distance} km
                                            </span>
                                        )}
                                        {stage.difficulty && (
                                            <span className="flex items-center gap-0.5">
                                                <Star className="h-3 w-3 fill-yellow-500 text-yellow-500" />
                                                {'★'.repeat(stage.difficulty)}
                                            </span>
                                        )}
                                    </div>
                                </div>

                                <div className="flex shrink-0 items-center gap-3">
                                    {isFinished(stage) && (
                                        <div className="flex items-center gap-1.5">
                                            <Trophy className="h-4 w-4 text-muted-foreground" />
                                            <span className={cn('font-semibold tabular-nums', points(stage) > 0 ? 'text-accent-foreground' : 'text-muted-foreground')}>
                                                {points(stage)} pts
                                            </span>
                                        </div>
                                    )}
                                    {!isFinished(stage) && (
                                        <div className="flex items-center gap-1.5">
                                            {hasPrediction(stage) ? (
                                                <FileCheck className="h-4 w-4 text-green-500" />
                                            ) : (
                                                <FileX className="h-4 w-4 text-muted-foreground/50" />
                                            )}
                                            <span className={cn(
                                                'text-xs',
                                                hasPrediction(stage) ? 'text-green-600' : 'text-muted-foreground/50',
                                            )}>
                                                {hasPrediction(stage) ? 'Pronosticado' : 'Sin pronóstico'}
                                            </span>
                                        </div>
                                    )}
                                    <ArrowRight className={cn('h-4 w-4', isFinished(stage) ? 'text-muted-foreground/30' : 'text-muted-foreground/50')} />
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
