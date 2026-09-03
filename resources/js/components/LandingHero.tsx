import { useState } from 'react';
import { Link } from '@inertiajs/react';
import { motion } from 'framer-motion';
import { ArrowRight, Zap } from 'lucide-react';
import Countdown from '@/components/Countdown';
import PedalesLogo from '@/components/PedalesLogo';

interface LandingHeroProps {
    activeEdition?: {
        name: string;
        status: string;
    } | null;
    nextEdition?: {
        name: string;
        startDate: string;
    } | null;
    auth?: {
        user?: {
            id: string;
            name: string;
        } | null;
    } | null;
}

export default function LandingHero({ activeEdition, nextEdition, auth }: LandingHeroProps) {
    const user = auth?.user;
    const [imageLoaded, setImageLoaded] = useState(false);

    return (
        <section className="relative flex min-h-[calc(100vh-3.5rem)] flex-col overflow-hidden">
            <div
                className="absolute inset-0 bg-cover bg-center"
                style={{ backgroundImage: 'url(/portada-landing.avif)' }}
            />
            <div className="absolute inset-0 bg-gradient-to-t from-black/80 via-black/40 to-black/20" />

            {/* Centered logo + CTA */}
            <div className="relative z-10 flex flex-1 flex-col items-center justify-center px-4 pt-14 sm:px-6 lg:px-8">
                <motion.div
                    className="relative h-36 w-36 sm:h-44 sm:w-44"
                    initial={{ opacity: 0, scale: 0.8, rotate: -180 }}
                    animate={{ opacity: 1, scale: 1, rotate: 0 }}
                    transition={{ duration: 0.8, ease: 'easeOut' }}
                >
                    <div className="absolute inset-0 rounded-full ring-4 ring-white/20 shadow-2xl" />
                    <div className={`absolute inset-0 flex items-center justify-center rounded-full bg-black/30 transition-opacity duration-500 ${imageLoaded ? 'opacity-0' : 'opacity-100'}`}>
                        <PedalesLogo className="h-24 w-24 sm:h-32 sm:w-32" />
                    </div>
                    <img
                        src="/logo-pedales.png"
                        alt="Pedales"
                        className={`absolute inset-0 h-full w-full rounded-full object-cover transition-opacity duration-500 ${imageLoaded ? 'opacity-100' : 'opacity-0'}`}
                        onLoad={() => setImageLoaded(true)}
                    />
                </motion.div>

                <motion.div
                    className="mt-10 flex items-center justify-center gap-4"
                    initial={{ opacity: 0, y: 20 }}
                    animate={{ opacity: 1, y: 0 }}
                    transition={{ duration: 0.6, delay: 0.4, ease: 'easeOut' }}
                >
                    {user ? (
                        <Link
                            href={route('dashboard')}
                            className="inline-flex h-12 items-center justify-center rounded-md bg-accent-500 px-8 py-3 text-base font-semibold text-white shadow-lg shadow-accent-500/25 transition-all hover:bg-accent-600 hover:shadow-xl hover:shadow-accent-500/30"
                        >
                            Jugar
                            <ArrowRight className="ml-2 h-5 w-5" />
                        </Link>
                    ) : (
                        <>
                            <Link
                                href={route('register')}
                                className="inline-flex h-12 items-center justify-center rounded-md bg-accent-500 px-8 py-3 text-base font-semibold text-white shadow-lg shadow-accent-500/25 transition-all hover:bg-accent-600 hover:shadow-xl hover:shadow-accent-500/30"
                            >
                                Registrarse
                                <ArrowRight className="ml-2 h-5 w-5" />
                            </Link>
                            <Link
                                href={route('login')}
                                className="inline-flex h-12 items-center justify-center rounded-md border border-white/20 px-8 py-3 text-base font-semibold text-white/90 transition-all hover:border-white/40 hover:text-white"
                            >
                                Iniciar sesión
                            </Link>
                        </>
                    )}
                </motion.div>
            </div>

            {/* Bottom — badge + tagline (darker gradient zone) */}
            <div className="relative z-10 px-4 pb-8 sm:px-6 lg:px-8">
                <div className="mx-auto max-w-3xl text-center">
                    {(activeEdition || nextEdition) && (
                        <motion.div
                            className="inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/5 px-4 py-2 backdrop-blur-sm"
                            initial={{ opacity: 0, y: 20 }}
                            animate={{ opacity: 1, y: 0 }}
                            transition={{ duration: 0.6, delay: 0.2, ease: 'easeOut' }}
                        >
                            <Zap className="h-4 w-4 text-accent-500" />
                            {activeEdition ? (
                                <span className="text-sm text-white/90">
                                    <span className="text-green-400">En curso</span> — {activeEdition.name}
                                </span>
                            ) : nextEdition ? (
                                <span className="text-sm text-white/90">
                                    Próximo: {nextEdition.name} —{' '}
                                    <Countdown targetDate={nextEdition.startDate} className="text-accent-500" />
                                </span>
                            ) : null}
                        </motion.div>
                    )}

                    <motion.p
                        className="mt-4 text-sm text-gray-300 sm:text-base"
                        initial={{ opacity: 0, y: 20 }}
                        animate={{ opacity: 1, y: 0 }}
                        transition={{ duration: 0.6, delay: 0.4, ease: 'easeOut' }}
                    >
                        El fantasy de ciclismo para Grandes Vueltas. Pronósticos sellados, ligas privadas y emoción hasta el último metro.
                    </motion.p>
                </div>
            </div>
        </section>
    );
}
