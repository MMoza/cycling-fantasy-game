import { useEffect, useRef } from 'react';
import { motion } from 'framer-motion';
import { CheckCircle, Clock, Timer, ImageIcon } from 'lucide-react';
import Countdown from '@/components/Countdown';

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

interface SeasonRacesProps {
    races: Race[];
    activeRaceId: string | null;
}

const statusConfig: Record<string, { label: string; icon: typeof CheckCircle; color: string; bg: string }> = {
    finished: { label: 'FINALIZADA', icon: CheckCircle, color: 'text-gray-400', bg: 'bg-gray-500/10 border-gray-500/20' },
    ongoing: { label: 'EN CURSO', icon: Clock, color: 'text-green-400', bg: 'bg-green-500/10 border-green-500/30' },
    upcoming: { label: 'PRÓXIMA', icon: Timer, color: 'text-accent-500', bg: 'bg-accent-500/10 border-accent-500/20' },
};

export default function SeasonRaces({ races, activeRaceId }: SeasonRacesProps) {
    const scrollRef = useRef<HTMLDivElement>(null);
    const activeRef = useRef<HTMLDivElement>(null);

    // Auto-scroll to the active card on mount
    useEffect(() => {
        if (activeRef.current && scrollRef.current) {
            const container = scrollRef.current;
            const card = activeRef.current;
            const scrollLeft = card.offsetLeft - container.clientWidth / 2 + card.clientWidth / 2;
            container.scrollTo({ left: scrollLeft, behavior: 'smooth' });
        }
    }, [activeRaceId]);

    if (races.length === 0) {
        return null;
    }

    return (
        <section className="border-b bg-muted/30 py-8">
            <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <motion.h2
                    className="mb-6 text-lg font-semibold tracking-tight"
                    initial={{ opacity: 0, y: 10 }}
                    whileInView={{ opacity: 1, y: 0 }}
                    viewport={{ once: true }}
                    transition={{ duration: 0.4 }}
                >
                    Temporada {races[0]?.year}
                </motion.h2>
            </div>

            <div
                ref={scrollRef}
                className="flex gap-4 overflow-x-auto scroll-smooth px-4 pb-2 snap-x snap-mandatory sm:px-6 lg:px-[max(1.5rem,calc((100vw-80rem)/2+1.5rem))]"
            >
                {races.map((race, idx) => {
                    const isActive = race.editionId === activeRaceId;
                    const status = statusConfig[race.status] ?? statusConfig.upcoming;
                    const StatusIcon = status.icon;

                    return (
                        <motion.div
                            key={race.editionId}
                            ref={isActive ? activeRef : undefined}
                            className={`relative w-64 shrink-0 snap-start rounded-xl border transition-all ${
                                isActive
                                    ? 'border-accent-500/40 bg-accent-500/5 shadow-lg shadow-accent-500/10'
                                    : 'border-border bg-card hover:bg-muted/50'
                            }`}
                            initial={{ opacity: 0, y: 20 }}
                            whileInView={{ opacity: 1, y: 0 }}
                            viewport={{ once: true }}
                            transition={{ duration: 0.3, delay: idx * 0.05 }}
                        >
                            {/* Cover image or gradient */}
                            <div className="relative h-28 overflow-hidden rounded-t-xl">
                                {race.coverImageUrl ? (
                                    <img
                                        src={race.coverImageUrl}
                                        alt=""
                                        className="h-full w-full object-cover"
                                    />
                                ) : (
                                    <div className="h-full w-full bg-gradient-to-br from-muted to-muted/50" />
                                )}
                                <div className="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent" />

                                {/* Status badge */}
                                <div className={`absolute top-2 right-2 inline-flex items-center gap-1 rounded-full border px-2 py-0.5 text-[10px] font-semibold ${status.bg} ${status.color}`}>
                                    <StatusIcon className="h-3 w-3" />
                                    {status.label}
                                </div>

                                {/* Logo */}
                                <div className="absolute bottom-2 left-2 flex h-10 w-10 items-center justify-center overflow-hidden rounded-full border-2 border-white/60 bg-background">
                                    {race.logoImageUrl ? (
                                        <img src={race.logoImageUrl} alt="" className="h-full w-full object-cover" />
                                    ) : (
                                        <ImageIcon className="h-5 w-5 text-muted-foreground/40" />
                                    )}
                                </div>
                            </div>

                            {/* Content */}
                            <div className="p-3">
                                <p className="font-semibold leading-tight">{race.name}</p>
                                {race.countryName && (
                                    <p className="mt-0.5 text-xs text-muted-foreground">{race.countryName}</p>
                                )}

                                {race.status === 'upcoming' && (
                                    <div className="mt-2 flex items-center gap-1.5 text-xs text-muted-foreground">
                                        <Timer className="h-3 w-3" />
                                        <Countdown targetDate={race.startDate} className="text-accent-500" />
                                    </div>
                                )}

                                {race.status === 'ongoing' && (
                                    <p className="mt-2 text-xs text-green-400 font-medium">En curso</p>
                                )}

                                {race.status === 'finished' && (
                                    <p className="mt-2 text-xs text-muted-foreground">Finalizada</p>
                                )}
                            </div>
                        </motion.div>
                    );
                })}
            </div>
        </section>
    );
}
