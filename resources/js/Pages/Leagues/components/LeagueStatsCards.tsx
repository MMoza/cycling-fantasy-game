import { Link } from '@inertiajs/react';
import { Card, CardContent } from '@/components/ui/card';
import { Trophy, Calendar, Route, Play } from 'lucide-react';
import { Countdown } from './Countdown';
import type { League, NextStage, UserPosition } from './types';

interface LeagueStatsCardsProps {
    league: League;
    next_stage: NextStage | null;
    user_position: UserPosition;
    isOngoing: boolean;
    onCountdownExpired: () => void;
}

export function LeagueStatsCards({ league, next_stage, user_position, isOngoing, onCountdownExpired }: LeagueStatsCardsProps) {
    return (
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
                                    <Countdown scheduledStart={next_stage.scheduled_start} onExpired={onCountdownExpired} />
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
    );
}
