import { useState, useCallback, useRef, useEffect } from 'react';
import { Link } from '@inertiajs/react';
import { Trophy, Share2, ExternalLink } from 'lucide-react';
import confetti from 'canvas-confetti';
import html2canvas from 'html2canvas';
import PedalesLogo from '@/components/PedalesLogo';
import { LeagueLeaderboard } from '@/Pages/Leagues/components/LeagueLeaderboard';
import type { LeaderboardEntry } from '@/Pages/Leagues/components/types';

interface CompetitionEndedData {
    competition_name: string;
    competition_year: number;
    user_name: string;
    user_avatar: string | null;
    position: number;
    total_points: number;
    stages_won: number;
    best_stage: { number: number; name: string; points: number } | null;
    is_official: boolean;
    league_id: string;
    leaderboard: LeaderboardEntry[];
}

export function CompetitionEndedModal({
    data,
    onDismiss,
}: {
    data: CompetitionEndedData;
    onDismiss: () => void;
}) {
    const leaderboard: LeaderboardEntry[] = data.leaderboard ?? [];

    useEffect(() => {
        const timer = setTimeout(() => {
            confetti({ particleCount: 100, spread: 70, origin: { y: 0.6 } });
            confetti({ particleCount: 50, angle: 60, spread: 55, origin: { x: 0 } });
            confetti({ particleCount: 50, angle: 120, spread: 55, origin: { x: 1 } });
        }, 400);
        return () => clearTimeout(timer);
    }, []);

    return (
        <div className="w-full">
            <div className="px-5 pt-5 pb-3 text-center">
                <p className="text-2xl mb-1">🏁</p>
                <p className="text-base font-bold text-gray-900">
                    La {data.competition_name} {data.competition_year} ha terminado
                </p>
                <p className="text-sm text-gray-500 mt-0.5">
                    Mira los resultados de la clasificación final
                </p>
            </div>

            <PlayerResultCard data={data} />

            {leaderboard.length > 0 && (
                <div className="px-4 py-4">
                    <div className="flex items-center justify-between mb-2">
                        <div className="flex items-center gap-2">
                            <Trophy className="h-4 w-4 text-emerald-600" />
                            <p className="text-xs font-semibold text-gray-500 uppercase tracking-wide">
                                Clasificación general
                            </p>
                        </div>
                        <PedalesLogo className="h-5 w-5 opacity-30" />
                    </div>
                    <div className="max-h-64 overflow-y-auto rounded-lg border border-gray-100">
                        <LeagueLeaderboard
                            league_id={data.league_id}
                            leaderboard={leaderboard}
                        />
                    </div>
                </div>
            )}

            {data.league_id && (
                <div className="px-4 pb-4">
                    <Link
                        href={route('classification.index', data.league_id)}
                        onClick={onDismiss}
                        className="flex items-center justify-center gap-1.5 w-full rounded-lg bg-gray-100 px-3 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-200 transition-colors"
                    >
                        <Trophy className="h-4 w-4" />
                        Ver clasificación completa
                        <ExternalLink className="h-3 w-3" />
                    </Link>
                </div>
            )}
        </div>
    );
}

function PlayerResultCard({ data }: { data: CompetitionEndedData }) {
    const cardRef = useRef<HTMLDivElement>(null);
    const [isSharing, setIsSharing] = useState(false);

    const handleShare = useCallback(async () => {
        if (!cardRef.current || isSharing) return;

        setIsSharing(true);
        try {
            const canvas = await html2canvas(cardRef.current, {
                backgroundColor: null,
                scale: 2,
                logging: false,
                useCORS: true,
            });

            canvas.toBlob(async (blob) => {
                if (!blob) { setIsSharing(false); return; }

                const file = new File([blob], `mi-resultado-${data.competition_name.toLowerCase().replace(/\s+/g, '-')}.png`, {
                    type: 'image/png',
                });

                if (navigator.share && navigator.canShare({ files: [file] })) {
                    try {
                        await navigator.share({
                            files: [file],
                            title: `${data.competition_name} ${data.competition_year} - Mi resultado`,
                            text: `#${data.position} en ${data.competition_name} ${data.competition_year} con ${data.total_points} puntos — ${window.location.origin}`,
                        });
                    } catch (err) {
                        if ((err as Error).name !== 'AbortError') {
                            console.error('Error sharing:', err);
                        }
                    }
                } else {
                    const url = URL.createObjectURL(blob);
                    const link = document.createElement('a');
                    link.href = url;
                    link.download = file.name;
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                    URL.revokeObjectURL(url);
                }

                setIsSharing(false);
            }, 'image/png');
        } catch (err) {
            console.error('Error capturing card:', err);
            setIsSharing(false);
        }
    }, [data.competition_name, data.competition_year, data.position, data.total_points, isSharing]);

    return (
        <div>
            <div ref={cardRef}>
                <div className="relative h-56 bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center overflow-hidden">
                    {data.user_avatar ? (
                        <img
                            src={data.user_avatar}
                            alt={data.user_name}
                            className="h-full w-full object-cover"
                        />
                    ) : (
                        <div className="h-32 w-32 rounded-full bg-white/20 flex items-center justify-center text-white text-5xl font-bold">
                            {data.user_name.split(' ').slice(0, 2).map((p: string) => p[0]).join('').toUpperCase()}
                        </div>
                    )}

                    <div className="absolute bottom-0 left-0 right-0 h-32 bg-gradient-to-t from-black/70 via-black/40 to-transparent" />

                    <div className="absolute bottom-0 left-0 right-0 p-5">
                        <div className="flex items-end justify-between gap-3">
                            <div className="flex-1 min-w-0 text-left">
                                <p className="text-lg font-bold text-white text-left leading-tight">
                                    {data.user_name}
                                </p>
                                <p className="text-sm text-white/90 mt-0.5 text-left">
                                    {data.competition_name} {data.competition_year}
                                </p>
                            </div>
                            <div className="text-right shrink-0 flex flex-col items-end gap-1">
                                <p className="text-3xl font-bold text-white">#{data.position}</p>
                                <PedalesLogo className="h-10 w-10 opacity-60" />
                            </div>
                        </div>
                    </div>
                </div>

                <div className="px-4 py-3 bg-white">
                    <div className="grid grid-cols-3 gap-3">
                        <div className="text-center">
                            <p className="text-xl font-bold text-gray-900">
                                {data.total_points}
                            </p>
                            <p className="text-[10px] font-medium text-gray-500 uppercase tracking-wide mt-0.5">
                                Puntos
                            </p>
                        </div>
                        <div className="text-center border-x border-gray-200">
                            <p className="text-xl font-bold text-gray-900">
                                {data.stages_won > 0 ? data.stages_won : '-'}
                            </p>
                            <p className="text-[10px] font-medium text-gray-500 uppercase tracking-wide mt-0.5">
                                Victorias
                            </p>
                        </div>
                        <div className="text-center">
                            <p className="text-xl font-bold text-gray-900">
                                {data.best_stage ? `Etapa ${data.best_stage.number}` : '-'}
                            </p>
                            <p className="text-[10px] font-medium text-gray-500 uppercase tracking-wide mt-0.5">
                                {data.best_stage ? `${data.best_stage.points} pts` : 'Mejor etapa'}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div className="px-4 py-2 border-t border-gray-100 bg-white">
                <button
                    type="button"
                    onClick={handleShare}
                    disabled={isSharing}
                    className="w-full flex items-center justify-center gap-2 py-2 text-sm font-medium text-gray-500 hover:text-gray-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                >
                    <Share2 className="h-4 w-4" />
                    {isSharing ? 'Compartiendo...' : 'Compartir resultado'}
                </button>
            </div>
        </div>
    );
}
