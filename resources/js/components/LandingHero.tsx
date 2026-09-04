import { useState } from 'react';
import { Link } from '@inertiajs/react';
import { motion } from 'framer-motion';
import { ArrowRight } from 'lucide-react';
import PedalesLogo from '@/components/PedalesLogo';
import RaceCarousel from '@/components/RaceCarousel';

interface SeasonRace {
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

interface LandingHeroProps {
    seasonRaces: SeasonRace[];
    activeRaceId: string | null;
    currentStage: CurrentStage | null;
    auth?: {
        user?: {
            id: string;
            name: string;
        } | null;
    } | null;
}

export default function LandingHero({ seasonRaces, activeRaceId, currentStage, auth }: LandingHeroProps) {
    const user = auth?.user;
    const [imageLoaded, setImageLoaded] = useState(false);

    return (
        <section className="relative flex min-h-[calc(100vh-3.5rem)] flex-col overflow-hidden">
            <div
                className="absolute inset-0 bg-cover bg-center"
                style={{ backgroundImage: 'url(/portada-landing.avif)' }}
            />
            <div className="absolute inset-0 bg-gradient-to-t from-black/90 via-black/50 to-black/30" />

            {/* Logo — centered */}
            <div className="relative z-10 flex flex-1 flex-col items-center justify-center px-4 pt-14 sm:px-6 lg:px-8">
                <motion.div
                    className="relative h-32 w-32 sm:h-40 sm:w-40"
                    initial={{ opacity: 0, scale: 0.8, rotate: -180 }}
                    animate={{ opacity: 1, scale: 1, rotate: 0 }}
                    transition={{ duration: 0.8, ease: 'easeOut' }}
                >
                    <div className="absolute inset-0 rounded-full ring-4 ring-white/20 shadow-2xl" />
                    <div className={`absolute inset-0 flex items-center justify-center rounded-full bg-black/30 transition-opacity duration-500 ${imageLoaded ? 'opacity-0' : 'opacity-100'}`}>
                        <PedalesLogo className="h-20 w-20 sm:h-28 sm:w-28" />
                    </div>
                    <img
                        src="/logo-pedales.png"
                        alt="Pedales"
                        className={`absolute inset-0 h-full w-full rounded-full object-cover transition-opacity duration-500 ${imageLoaded ? 'opacity-100' : 'opacity-0'}`}
                        onLoad={() => setImageLoaded(true)}
                    />
                </motion.div>
            </div>

            {/* Carousel + tagline — bottom of hero */}
            <div className="relative z-10 pb-8">
                <RaceCarousel
                    races={seasonRaces}
                    activeRaceId={activeRaceId}
                    currentStage={currentStage}
                />

                {/* Tagline */}
                <div className="px-4 text-center sm:px-6">
                    <motion.p
                        className="text-base font-semibold text-white sm:text-lg"
                        initial={{ opacity: 0, y: 20 }}
                        animate={{ opacity: 1, y: 0 }}
                        transition={{ duration: 0.6, delay: 0.3, ease: 'easeOut' }}
                    >
                        El fantasy de ciclismo para{' '}
                        <span className="text-accent-500">Grandes Vueltas.</span>
                    </motion.p>
                    <motion.p
                        className="mt-2 text-sm text-gray-300 sm:text-base"
                        initial={{ opacity: 0, y: 20 }}
                        animate={{ opacity: 1, y: 0 }}
                        transition={{ duration: 0.6, delay: 0.5, ease: 'easeOut' }}
                    >
                        Pronósticos sellados · Ligas privadas · Emoción hasta el último metro.
                    </motion.p>
                </div>
            </div>
        </section>
    );
}
