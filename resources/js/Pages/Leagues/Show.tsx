import { useEffect, useMemo, useState } from 'react';
import AppLayout from '@/Layouts/AppLayout';
import { Head, Link, router, usePage } from '@inertiajs/react';
import { Card, CardContent } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Dialog, DialogContent } from '@/components/ui/dialog';
import { Trophy, Route, ChevronRight, Target, Settings, ShieldCheck, Users, Shield, BarChart3 } from 'lucide-react';
import { LeagueSettingsModal } from './components/LeagueSettingsModal';
import { ScoringInfoModal } from './components/ScoringInfoModal';
import { LeagueStatsCards } from './components/LeagueStatsCards';
import { LeagueLeaderboard } from './components/LeagueLeaderboard';
import { ActivityFeed } from './components/ActivityFeed';
import { CompetitionEndedModal } from '@/components/CompetitionEndedModal';
import { InviteFriend } from './components/InviteFriend';
import { LeagueSwitcher, LeagueNavigationArrows } from './components/LeagueSwitcher';
import { ContentSkeleton } from './components/ContentSkeleton';
import type { League, NextStage, UserPosition, Stage, LeaderboardEntry, ActivityLog, SeasonLeague } from './components/types';

interface ShowProps {
    league_id: string;
    league: League;
    season_leagues: SeasonLeague[];
    next_stage: NextStage | null;
    user_position: UserPosition;
    stages: Stage[];
    leaderboard: LeaderboardEntry[];
    activity_logs: ActivityLog[];
}

const SWITCHED_PROPS = [
    'league_id',
    'league',
    'next_stage',
    'user_position',
    'stages',
    'leaderboard',
    'activity_logs',
    'season_leagues',
];

export default function Show({ league, season_leagues, next_stage, user_position, stages, leaderboard, activity_logs }: ShowProps) {
    const [settingsOpen, setSettingsOpen] = useState(false);
    const [scoringInfoOpen, setScoringInfoOpen] = useState(false);
    const [countdownExpired, setCountdownExpired] = useState(false);
    const [finishedOpen, setFinishedOpen] = useState(false);
    const [switchTargetId, setSwitchTargetId] = useState<string | null>(null);

    // Hoisted so the hook order never changes when props (or isFinished) change
    // on a preserved-state partial reload.
    const pageProps = usePage().props as any;

    const isSwitching = switchTargetId !== null;
    const isOngoing = next_stage?.status === 'ongoing' || (next_stage?.status === 'upcoming' && countdownExpired);
    const isFinished = league.edition_status === 'finished';
    const isOneDayRace = ['monument', 'championship', 'classic'].includes(league.competition.type);

    // While the partial reload is in flight the hero card already shows the
    // target league using the season list, so the switch feels instant.
    const displayLeague = useMemo<League>(() => {
        if (!switchTargetId) {
            return league;
        }

        const target = season_leagues.find((item) => item.id === switchTargetId);

        if (!target) {
            return league;
        }

        return {
            ...league,
            name: target.name,
            is_official: target.is_official,
            member_count: target.member_count,
            edition_status: target.edition_status,
            competition: {
                ...league.competition,
                name: target.competition.name,
                year: target.year,
                type: target.competition.type,
                coverImageUrl: target.competition.coverImageUrl,
                logoImageUrl: target.competition.logoImageUrl,
            },
        };
    }, [league, season_leagues, switchTargetId]);

    const switchLeague = (leagueId: string) => {
        if (leagueId === league.id || isSwitching) {
            return;
        }

        setCountdownExpired(false);
        setFinishedOpen(false);
        setSettingsOpen(false);
        setScoringInfoOpen(false);
        setSwitchTargetId(leagueId);

        router.get(
            route('leagues.show', leagueId),
            {},
            {
                only: SWITCHED_PROPS,
                preserveState: true,
                onFinish: () => setSwitchTargetId(null),
            },
        );
    };

    const goRelative = (delta: number) => {
        if (season_leagues.length < 2 || isSwitching) {
            return;
        }

        const index = season_leagues.findIndex((item) => item.id === league.id);

        if (index === -1) {
            return;
        }

        const next = (index + delta + season_leagues.length) % season_leagues.length;
        switchLeague(season_leagues[next].id);
    };

    useEffect(() => {
        if (season_leagues.length < 2) {
            return;
        }

        const handler = (event: KeyboardEvent) => {
            const target = event.target as HTMLElement | null;

            if (
                target &&
                (target.tagName === 'INPUT' ||
                    target.tagName === 'TEXTAREA' ||
                    target.tagName === 'SELECT' ||
                    target.isContentEditable)
            ) {
                return;
            }

            if (settingsOpen || scoringInfoOpen || finishedOpen || isSwitching) {
                return;
            }

            if (event.key === 'ArrowLeft') {
                event.preventDefault();
                goRelative(-1);
            } else if (event.key === 'ArrowRight') {
                event.preventDefault();
                goRelative(1);
            }
        };

        window.addEventListener('keydown', handler);

        return () => window.removeEventListener('keydown', handler);
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [season_leagues, league.id, isSwitching, settingsOpen, scoringInfoOpen, finishedOpen]);

    return (
        <AppLayout>
            <Head title={displayLeague.name} />

            {/* Mobile sticky header with league dropdown */}
            <LeagueSwitcher
                league={displayLeague}
                seasonLeagues={season_leagues}
                activeLeagueId={switchTargetId ?? league.id}
                onSwitch={switchLeague}
                onSettingsOpen={() => setSettingsOpen(true)}
            />

            <div className="mx-auto max-w-2xl space-y-6 px-4 py-6 sm:px-0">
                {/* Desktop hero card */}
                <div className="relative hidden sm:block">
                    <LeagueNavigationArrows
                        seasonLeagues={season_leagues}
                        activeLeagueId={switchTargetId ?? league.id}
                        onSwitch={switchLeague}
                        disabled={isSwitching}
                    />
                    <Card className="overflow-hidden">
                        <div className="relative flex h-36 items-end">
                            {displayLeague.competition.coverImageUrl ? (
                                <img
                                    src={displayLeague.competition.coverImageUrl}
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
                                    {displayLeague.competition.logoImageUrl ? (
                                        <img src={displayLeague.competition.logoImageUrl} alt="" className="h-full w-full object-cover" />
                                    ) : (
                                        <Trophy className="h-5 w-5 text-muted-foreground" />
                                    )}
                                </div>
                                <div className="min-w-0 flex-1">
                                    <h1 className="text-base font-bold text-white truncate drop-shadow-sm">
                                        {displayLeague.competition.name} {displayLeague.competition.year}
                                    </h1>
                                    <div className="flex items-center gap-2">
                                        <span className="text-sm font-medium text-white/90 truncate">{displayLeague.name}</span>
                                        {displayLeague.is_official && (
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
                                        {displayLeague.member_count}
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
                </div>

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

                {isFinished && (
                    <Dialog open={finishedOpen} onOpenChange={setFinishedOpen}>
                        <DialogContent className="sm:max-w-lg p-0 gap-0 overflow-hidden max-h-[90vh] overflow-y-auto" showCloseButton={true}>
                            <CompetitionEndedModal
                                data={{
                                    competition_name: league.competition.name,
                                    competition_year: league.competition.year,
                                    user_name: pageProps?.auth?.user?.name ?? 'Tú',
                                    user_avatar: pageProps?.auth?.user?.avatar ?? null,
                                    position: parseInt(user_position.rank) || 0,
                                    total_points: parseInt(user_position.points) || 0,
                                    stages_won: 0,
                                    best_stage: null,
                                    is_official: league.is_official,
                                    league_id: league.id,
                                    leaderboard,
                                }}
                                onDismiss={() => setFinishedOpen(false)}
                            />
                        </DialogContent>
                    </Dialog>
                )}

                {isSwitching ? (
                    <ContentSkeleton />
                ) : (
                    <>
                        <LeagueStatsCards
                            league={league}
                            next_stage={next_stage}
                            user_position={user_position}
                            isOngoing={isOngoing}
                            isFinished={isFinished}
                            onCountdownExpired={() => setCountdownExpired(true)}
                            onFinishedClick={() => setFinishedOpen(true)}
                        />

                        <Link href={route('season.classification')} className="block">
                            <Card className="relative cursor-pointer overflow-hidden border-accent-200/60 bg-gradient-to-br from-accent-50 to-white transition-colors hover:from-accent-100/70 dark:border-accent-800/30 dark:from-accent-950/20 dark:to-transparent dark:hover:from-accent-950/30">
                                <div className="absolute -right-4 -top-4 h-24 w-24 rounded-full bg-accent-100/50 dark:bg-accent-900/20" />
                                <CardContent className="relative flex items-center gap-3 p-4">
                                    <div className="relative flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-accent-100 dark:bg-accent-900/30">
                                        <BarChart3 className="h-5 w-5 text-accent-600 dark:text-accent-400" />
                                        <span className="absolute -right-1 -top-1 flex h-4 w-4 items-center justify-center rounded-full bg-accent-600 text-[10px] font-bold text-white dark:bg-accent-400 dark:text-accent-950">
                                            N
                                        </span>
                                    </div>
                                    <div className="flex-1">
                                        <div className="flex items-center gap-2">
                                            <p className="font-medium">Clasificación Temporada</p>
                                            <span className="inline-flex items-center rounded-full bg-accent-600 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-white animate-pulse dark:bg-accent-400 dark:text-accent-950">
                                                NEW
                                            </span>
                                        </div>
                                        <p className="text-sm text-muted-foreground">
                                            Compite contra todos los jugadores de la temporada
                                        </p>
                                    </div>
                                    <ChevronRight className="h-5 w-5 text-muted-foreground" />
                                </CardContent>
                            </Card>
                        </Link>

                        {!isOneDayRace && (
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
                        )}

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

                        <InviteFriend />

                        <LeagueLeaderboard
                            league_id={league.id}
                            leaderboard={leaderboard}
                        />

                        <ActivityFeed activity_logs={activity_logs} is_official={league.is_official} />
                    </>
                )}
            </div>
        </AppLayout>
    );
}
