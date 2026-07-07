import { useState } from 'react';
import AppLayout from '@/Layouts/AppLayout';
import { Head, Link } from '@inertiajs/react';
import { Card, CardContent } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Trophy, Route, ChevronRight, Target, Settings, ShieldCheck } from 'lucide-react';
import { LeagueSettingsModal } from './components/LeagueSettingsModal';
import { ScoringInfoModal } from './components/ScoringInfoModal';
import { LeagueStatsCards } from './components/LeagueStatsCards';
import { LeagueLeaderboard } from './components/LeagueLeaderboard';
import { ActivityFeed } from './components/ActivityFeed';
import type { League, NextStage, UserPosition, Stage, LeaderboardEntry, ActivityLog } from './components/types';

interface ShowProps {
    league: League;
    next_stage: NextStage | null;
    user_position: UserPosition;
    stages: Stage[];
    leaderboard: LeaderboardEntry[];
    activity_logs: ActivityLog[];
}

export default function Show({ league, next_stage, user_position, stages, leaderboard, activity_logs }: ShowProps) {
    const [settingsOpen, setSettingsOpen] = useState(false);
    const [scoringInfoOpen, setScoringInfoOpen] = useState(false);
    const [countdownExpired, setCountdownExpired] = useState(false);

    const isOngoing = next_stage?.status === 'ongoing' || (next_stage?.status === 'upcoming' && countdownExpired);

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

                <LeagueLeaderboard
                    league_id={league.id}
                    leaderboard={leaderboard}
                />

                <ActivityFeed activity_logs={activity_logs} />
            </div>
        </AppLayout>
    );
}
