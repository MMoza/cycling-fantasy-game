import { useState } from 'react';
import AppLayout from '@/Layouts/AppLayout';
import { Head, Link } from '@inertiajs/react';
import { Card, CardContent } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Trophy, Route, ChevronRight, Target, Settings, ShieldCheck, Calendar, Users, Shield } from 'lucide-react';
import { LeagueSettingsModal } from './components/LeagueSettingsModal';
import { ScoringInfoModal } from './components/ScoringInfoModal';
import { LeagueStatsCards } from './components/LeagueStatsCards';
import { LeagueLeaderboard } from './components/LeagueLeaderboard';
import { ActivityFeed } from './components/ActivityFeed';
import type { League, NextStage, UserPosition, Stage, LeaderboardEntry, ActivityLog } from './components/types';

interface SeasonSummary {
    year: number;
    joined_count: number;
    total_competitions: number;
}

interface ShowProps {
    league: League;
    next_stage: NextStage | null;
    user_position: UserPosition;
    stages: Stage[];
    leaderboard: LeaderboardEntry[];
    activity_logs: ActivityLog[];
    season: SeasonSummary;
}

export default function Show({ league, next_stage, user_position, stages, leaderboard, activity_logs, season }: ShowProps) {
    const [settingsOpen, setSettingsOpen] = useState(false);
    const [scoringInfoOpen, setScoringInfoOpen] = useState(false);
    const [countdownExpired, setCountdownExpired] = useState(false);

    const isOngoing = next_stage?.status === 'ongoing' || (next_stage?.status === 'upcoming' && countdownExpired);

    return (
        <AppLayout>
            <Head title={league.name} />

            {/* Mobile sticky header */}
            <div className="sticky top-[88px] z-40 bg-background/95 backdrop-blur md:hidden">
                <div className="flex items-center gap-2 px-4 py-2">
                    <div className="flex h-8 w-8 shrink-0 items-center justify-center overflow-hidden rounded-full border border-border bg-muted">
                        {league.competition.logoImageUrl ? (
                            <img src={league.competition.logoImageUrl} alt="" className="h-full w-full object-cover bg-black" />
                        ) : (
                            <Trophy className="h-4 w-4 text-muted-foreground" />
                        )}
                    </div>
                    <div className="min-w-0 flex-1">
                        <div className="flex items-center gap-1.5">
                            <span className="truncate text-sm font-semibold">
                                {league.competition.name} {league.competition.year}
                            </span>
                            {league.is_official && (
                                <Badge variant="default" className="gap-0.5 rounded-full bg-brand-600 text-white text-[9px] h-4 px-1.5 border-0">
                                    <ShieldCheck className="h-2 w-2" />
                                    Oficial
                                </Badge>
                            )}
                        </div>
                        <span className="block truncate text-xs text-muted-foreground">{league.name}</span>
                    </div>
                    <button
                        type="button"
                        onClick={() => setSettingsOpen(true)}
                        className="shrink-0 rounded-md p-1.5 text-muted-foreground transition-colors hover:bg-muted hover:text-foreground"
                    >
                        <Settings className="h-4 w-4" />
                    </button>
                </div>
                <div className="uci-rainbow-stripe" />
            </div>

            <div className="mx-auto max-w-2xl space-y-6 px-4 py-6 sm:px-0">
                {/* Desktop hero card */}
                <Card className="hidden overflow-hidden sm:block">
                    <div className="relative flex h-36 items-end">
                        {league.competition.coverImageUrl ? (
                            <img
                                src={league.competition.coverImageUrl}
                                alt=""
                                className="absolute inset-0 h-full w-full object-cover"
                            />
                        ) : (
                            <div className="absolute inset-0 flex items-center justify-center bg-muted">
                                <Route className="h-12 w-12 text-muted-foreground/40" />
                            </div>
                        )}
                        <div className="absolute inset-0 bg-gradient-to-t from-black/70 via-black/20 to-transparent" />
                        <div className="relative z-10 flex w-full items-center gap-3 p-4">
                            <div className="flex h-12 w-12 shrink-0 items-center justify-center overflow-hidden rounded-full border-2 border-white/60 bg-black/40">
                                {league.competition.logoImageUrl ? (
                                    <img src={league.competition.logoImageUrl} alt="" className="h-full w-full object-cover" />
                                ) : (
                                    <Trophy className="h-5 w-5 text-muted-foreground" />
                                )}
                            </div>
                            <div className="min-w-0 flex-1">
                                <h1 className="text-base font-bold text-white truncate drop-shadow-sm">
                                    {league.competition.name} {league.competition.year}
                                </h1>
                                <div className="flex items-center gap-2">
                                    <span className="text-sm font-medium text-white/90 truncate">{league.name}</span>
                                    {league.is_official && (
                                        <Badge variant="default" className="gap-1 rounded-full bg-brand-600 text-white text-[10px] h-5 px-2 border-0 shadow-sm">
                                            <ShieldCheck className="h-2.5 w-2.5" />
                                            Oficial
                                        </Badge>
                                    )}
                                </div>
                            </div>
                            <div className="flex items-center gap-3">
                                <span className="flex items-center gap-1 text-xs text-white/80">
                                    <Users className="h-3.5 w-3.5" />
                                    {league.member_count}
                                </span>
                                <button
                                    type="button"
                                    onClick={() => setSettingsOpen(true)}
                                    className="shrink-0 rounded-lg p-2 text-white/80 transition-colors hover:bg-white/10 hover:text-white"
                                >
                                    <Settings className="h-5 w-5" />
                                </button>
                            </div>
                        </div>
                    </div>
                    <div className="uci-rainbow-stripe" />
                </Card>

                <LeagueSettingsModal
                    league={league}
                    open={settingsOpen}
                    onClose={() => setSettingsOpen(false)}
                    onScoringInfoOpen={() => setScoringInfoOpen(true)}
                />

                <ScoringInfoModal
                    league={league}
                    open={scoringInfoOpen}
                    onClose={() => setScoringInfoOpen(false)}
                />

                <LeagueStatsCards
                    league={league}
                    next_stage={next_stage}
                    user_position={user_position}
                    isOngoing={isOngoing}
                    onCountdownExpired={() => setCountdownExpired(true)}
                />

                <Link href={route('predictions.pre-race', league.id)} className="block">
                    <Card className="cursor-pointer border-amber-200/60 bg-gradient-to-br from-amber-50 to-white transition-colors hover:from-amber-100/70 dark:border-amber-800/30 dark:from-amber-950/20 dark:to-transparent dark:hover:from-amber-950/30">
                        <CardContent className="flex items-center gap-3 p-4">
                            <div className="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-amber-100 dark:bg-amber-900/30">
                                <Target className="h-5 w-5 text-amber-600 dark:text-amber-400" />
                            </div>
                            <div className="flex-1">
                                <p className="font-medium">PREDICTOR</p>
                                <p className="text-sm text-muted-foreground">
                                    Predicción de la clasificación general
                                </p>
                            </div>
                            <ChevronRight className="h-5 w-5 text-muted-foreground" />
                        </CardContent>
                    </Card>
                </Link>

                <Link href={route('leagues.teams', league.id)} className="block">
                    <Card className="cursor-pointer border-teal-200/60 bg-gradient-to-br from-teal-50 to-white transition-colors hover:from-teal-100/70 dark:border-teal-800/30 dark:from-teal-950/20 dark:to-transparent dark:hover:from-teal-950/30">
                        <CardContent className="flex items-center gap-3 p-4">
                            <div className="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-teal-100 dark:bg-teal-900/30">
                                <Shield className="h-5 w-5 text-teal-600 dark:text-teal-400" />
                            </div>
                            <div className="flex-1">
                                <p className="font-medium">Equipos</p>
                                <p className="text-sm text-muted-foreground">
                                    Plantillas y ciclistas de la carrera
                                </p>
                            </div>
                            <ChevronRight className="h-5 w-5 text-muted-foreground" />
                        </CardContent>
                    </Card>
                </Link>

                <Link href={route('season.index')} className="block">
                    <Card className="cursor-pointer border-blue-200/60 bg-gradient-to-br from-blue-50 to-white transition-colors hover:from-blue-100/70 dark:border-blue-800/30 dark:from-blue-950/20 dark:to-transparent dark:hover:from-blue-950/30">
                        <CardContent className="flex items-center gap-3 p-4">
                            <div className="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-blue-100 dark:bg-blue-900/30">
                                <Calendar className="h-5 w-5 text-blue-600 dark:text-blue-400" />
                            </div>
                            <div className="flex-1">
                                <p className="font-medium">Temporada {season.year}</p>
                                <p className="text-sm text-muted-foreground">
                                    {season.joined_count} de {season.total_competitions} competiciones oficiales
                                </p>
                            </div>
                            <ChevronRight className="h-5 w-5 text-muted-foreground" />
                        </CardContent>
                    </Card>
                </Link>

                <LeagueLeaderboard
                    league_id={league.id}
                    leaderboard={leaderboard}
                />

                <ActivityFeed activity_logs={activity_logs} />
            </div>
        </AppLayout>
    );
}
