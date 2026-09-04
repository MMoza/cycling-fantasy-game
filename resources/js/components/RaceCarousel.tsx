import { useEffect, useRef } from 'react';
import { Link } from '@inertiajs/react';
import { CheckCircle, Clock, Timer, MapPin, Route, Calendar } from 'lucide-react';

interface Race {
    editionId: string;
    name: string;
    year: number;
    status: string;
    startDate: string;
    endDate: string;
    countryId: string | null;
    countryName: string | null;
    logoImageUrl: string | null;
    coverImageUrl: string | null;
    officialLeagueId: string | null;
}

interface CurrentStage {
    number: number;
    name: string;
    distance: number;
    date: string;
}

interface RaceCarouselProps {
    races: Race[];
    activeRaceId: string | null;
    currentStage: CurrentStage | null;
}

export default function RaceCarousel({ races, activeRaceId, currentStage }: RaceCarouselProps) {
    const scrollRef = useRef<HTMLDivElement>(null);

    // Auto-scroll to the active card on mount
    useEffect(() => {
        if (!scrollRef.current || !activeRaceId) return;
        const container = scrollRef.current;
        const cards = Array.from(container.querySelectorAll('[data-race-card]'));
        const activeCard = cards.find((el) => el.getAttribute('data-race-id') === activeRaceId);
        if (!activeCard) return;

        const scrollLeft = (activeCard as HTMLElement).offsetLeft - container.clientWidth / 2 + (activeCard as HTMLElement).clientWidth / 2;
        container.scrollTo({ left: scrollLeft, behavior: 'smooth' });
    }, [activeRaceId]);

    if (races.length === 0) return null;

    return (
        <div className="w-full">
            <div
                ref={scrollRef}
                className="flex gap-4 overflow-x-auto scroll-smooth px-4 pb-4 snap-x snap-mandatory scrollbar-hide"
                style={{ scrollbarWidth: 'none', msOverflowStyle: 'none' }}
            >
                {races.map((race) => {
                    const isActive = race.editionId === activeRaceId;
                    const isFinished = race.status === 'finished';
                    const isOngoing = race.status === 'ongoing';
                    const isUpcoming = race.status === 'upcoming';

                    return (
                        <Link
                            key={race.editionId}
                            href={route('competitions.show', race.editionId)}
                            data-race-card
                            data-race-id={race.editionId}
                            className={`relative w-[280px] shrink-0 snap-center border transition-all duration-300 hover:scale-[1.02] hover:border-white/20 ${
                                isActive
                                    ? 'border-accent-500/50 bg-black/60 shadow-2xl shadow-accent-500/20 backdrop-blur-md'
                                    : 'border-white/10 bg-black/30 backdrop-blur-sm opacity-70'
                            }`}
                        >
                            <div className="p-4">
                                {/* Top row: status + logo */}
                                <div className="flex items-center justify-between">
                                    <div className="flex items-center gap-1.5">
                                        {isFinished && <CheckCircle className="h-3.5 w-3.5 text-gray-400" />}
                                        {isOngoing && <Clock className="h-3.5 w-3.5 text-green-400" />}
                                        {isUpcoming && <Timer className="h-3.5 w-3.5 text-accent-500" />}
                                        <span className={`text-[10px] font-bold tracking-wider ${
                                            isFinished ? 'text-gray-400' : isOngoing ? 'text-green-400' : 'text-accent-500'
                                        }`}>
                                            {isFinished ? 'FINALIZADA' : isOngoing ? 'EN CURSO' : 'PRÓXIMA'}
                                        </span>
                                    </div>

                                    {race.logoImageUrl && (
                                        <div className="h-8 w-8 overflow-hidden rounded-full border border-white/20 bg-white/10">
                                            <img src={race.logoImageUrl} alt="" className="h-full w-full object-cover" />
                                        </div>
                                    )}
                                </div>

                                {/* Race name */}
                                <h3 className="mt-2 text-base font-bold text-white leading-tight">{race.name}</h3>

                                {/* Stage info (only for ongoing + active) */}
                                {isOngoing && isActive && currentStage && (
                                    <div className="mt-2 space-y-1">
                                        <div className="flex items-center gap-1.5 text-xs text-white/70">
                                            <MapPin className="h-3 w-3 text-accent-500" />
                                            <span>Etapa {currentStage.number} · {currentStage.name}</span>
                                        </div>
                                        <div className="flex items-center gap-3 text-xs text-white/70">
                                            <span className="flex items-center gap-1">
                                                <Calendar className="h-3 w-3" />
                                                Hoy
                                            </span>
                                            <span className="flex items-center gap-1">
                                                <Route className="h-3 w-3" />
                                                {currentStage.distance} km
                                            </span>
                                        </div>
                                    </div>
                                )}

                                {/* Countdown (only for upcoming + active) */}
                                {isUpcoming && isActive && (
                                    <div className="mt-2">
                                        <p className="text-[10px] text-white/50 mb-1">Comienza en</p>
                                        <div className="flex gap-2">
                                            {(() => {
                                                const diff = new Date(race.startDate).getTime() - Date.now();
                                                if (diff <= 0) return null;
                                                const totalSeconds = Math.floor(diff / 1000);
                                                const days = Math.floor(totalSeconds / 86400);
                                                const hours = Math.floor((totalSeconds % 86400) / 3600);
                                                const minutes = Math.floor((totalSeconds % 3600) / 60);
                                                return [
                                                    { value: days, label: 'DÍAS' },
                                                    { value: hours, label: 'HORAS' },
                                                    { value: minutes, label: 'MIN' },
                                                ].map((p) => (
                                                    <div key={p.label} className="text-center">
                                                        <div className="flex h-10 w-10 items-center justify-center rounded-lg bg-white/10 text-lg font-bold text-white tabular-nums">
                                                            {String(p.value).padStart(2, '0')}
                                                        </div>
                                                        <span className="mt-0.5 text-[8px] font-medium text-white/40">{p.label}</span>
                                                    </div>
                                                ));
                                            })()}
                                        </div>
                                    </div>
                                )}

                                {/* Year (for non-active cards) */}
                                {!isActive && (
                                    <p className="mt-1 text-xs text-white/40">{race.year}</p>
                                )}
                            </div>
                        </Link>
                    );
                })}
            </div>
        </div>
    );
}
