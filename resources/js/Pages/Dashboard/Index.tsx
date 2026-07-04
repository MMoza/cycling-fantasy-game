import { useEffect, useState } from 'react';
import AppLayout from '@/Layouts/AppLayout';
import { Head, Link } from '@inertiajs/react';
import { Card, CardContent } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { StageTypeIcon } from '@/components/ui/stage-type-icon';
import { Plus, Search, Bike, Clock, Play, MapPin, Gauge } from 'lucide-react';

interface League {
    id: string;
    name: string;
    competition: string;
    year: number;
}

interface Stage {
    id: string;
    number: number;
    name: string;
    type: string;
    type_value: string;
    origin: string;
    destination: string;
    distance: number;
    difficulty: number;
    scheduled_start: string;
    status: string;
    is_ongoing: boolean;
}

interface Props {
    league: League | null;
    stage: Stage | null;
}

function formatDiff(ms: number): string {
    if (ms <= 0) return '00:00:00';
    const totalSeconds = Math.floor(ms / 1000);
    const hours = Math.floor(totalSeconds / 3600);
    const minutes = Math.floor((totalSeconds % 3600) / 60);
    const seconds = totalSeconds % 60;
    return `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
}

function Countdown({ scheduledStart }: { scheduledStart: string }) {
    const [diff, setDiff] = useState(0);

    useEffect(() => {
        const update = () => setDiff(new Date(scheduledStart).getTime() - Date.now());
        update();
        const id = setInterval(update, 1000);
        return () => clearInterval(id);
    }, [scheduledStart]);

    return (
        <span className="font-mono text-2xl font-bold tabular-nums tracking-wider">
            {formatDiff(diff)}
        </span>
    );
}

function DifficultyStars({ level }: { level: number }) {
    return (
        <span className="text-amber-500">
            {'★'.repeat(level)}{'☆'.repeat(3 - level)}
        </span>
    );
}

function StageDistance({ value }: { value: number }) {
    return `${Number(value).toFixed(0)} km`;
}

export default function DashboardIndex({ league, stage }: Props) {
    const stageUrl = stage && league
        ? route('stages.show', [league.id, stage.id])
        : null;

    return (
        <AppLayout>
            <Head title="Dashboard" />

            <div className="space-y-6">
                <div>
                    <h1 className="text-2xl font-semibold tracking-tight">Dashboard</h1>
                    <p className="text-sm text-muted-foreground">
                        Bienvenido a Pedales Fantasy Cycling
                    </p>
                </div>

                {!league ? (
                    <Card className="overflow-hidden">
                        <div className="h-1 bg-gradient-to-r from-brand-600 to-accent-500" />
                        <CardContent className="flex flex-col items-center justify-center px-6 py-16 text-center">
                            <div className="mb-6 flex h-16 w-16 items-center justify-center rounded-full bg-accent-100 dark:bg-accent-900/20">
                                <Bike className="h-8 w-8 text-accent-500" />
                            </div>
                            <h3 className="text-xl font-medium">No tienes ligas aún</h3>
                            <p className="mt-2 text-sm text-muted-foreground">
                                Crea una liga o únete a una existente para empezar a competir
                            </p>
                            <div className="mt-8 flex gap-3">
                                <Button asChild>
                                    <Link href={route('leagues.create')}>
                                        <Plus className="mr-2 h-4 w-4" />
                                        Crear liga
                                    </Link>
                                </Button>
                                <Button variant="outline" asChild>
                                    <Link href={route('leagues.index')}>
                                        <Search className="mr-2 h-4 w-4" />
                                        Unirse con código
                                    </Link>
                                </Button>
                            </div>
                        </CardContent>
                    </Card>
                ) : (
                    <div className="mx-auto max-w-lg space-y-4">
                        <Link
                            href={route('leagues.show', league.id)}
                            className="block rounded-xl bg-muted/50 p-4 transition-colors hover:bg-muted"
                        >
                            <p className="text-sm text-muted-foreground">{league.competition} {league.year}</p>
                            <p className="text-lg font-semibold">{league.name}</p>
                        </Link>

                        {stage && (
                            <Link
                                href={stageUrl ?? '#'}
                                className={`block rounded-xl border-2 p-5 transition-colors hover:bg-muted/50 ${
                                    stage.is_ongoing
                                        ? 'border-green-500 bg-green-50/50 dark:bg-green-950/10'
                                        : 'border-brand-600 bg-brand-50/50 dark:bg-brand-950/10'
                                }`}
                            >
                                <div className="mb-3 flex items-center justify-between">
                                    <div className="flex items-center gap-2">
                                        <Badge
                                            variant="outline"
                                            className="flex h-8 w-8 items-center justify-center rounded-full p-0 text-sm font-bold"
                                        >
                                            {stage.number}
                                        </Badge>
                                        <div>
                                            <p className="font-semibold">{stage.name}</p>
                                            <div className="flex items-center gap-2 text-xs text-muted-foreground">
                                                <StageTypeIcon type={stage.type_value} className="h-3.5 w-3.5" />
                                                <span>{stage.type}</span>
                                                <span>·</span>
                                                <Gauge className="h-3 w-3" />
                                                <DifficultyStars level={stage.difficulty} />
                                                <span>·</span>
                                                <MapPin className="h-3 w-3" />
                                                <StageDistance value={stage.distance} />
                                            </div>
                                        </div>
                                    </div>

                                    {stage.is_ongoing ? (
                                        <Badge variant="default" className="animate-pulse gap-1 bg-green-600 hover:bg-green-600">
                                            <Play className="h-3 w-3 fill-current" />
                                            En curso
                                        </Badge>
                                    ) : (
                                        <Badge variant="default" className="gap-1 bg-brand-600 hover:bg-brand-600">
                                            <Clock className="h-3 w-3" />
                                            Próxima
                                        </Badge>
                                    )}
                                </div>

                                <div className="flex items-center justify-between rounded-lg bg-background p-4">
                                    <div className="flex items-center gap-2 text-sm text-muted-foreground">
                                        <MapPin className="h-4 w-4" />
                                        {stage.origin} → {stage.destination}
                                    </div>
                                    {stage.is_ongoing ? (
                                        <span className="text-sm font-medium text-green-600">En vivo</span>
                                    ) : (
                                        <Countdown scheduledStart={stage.scheduled_start} />
                                    )}
                                </div>
                            </Link>
                        )}
                    </div>
                )}
            </div>
        </AppLayout>
    );
}
