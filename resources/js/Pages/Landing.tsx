import LandingLayout from '@/Layouts/LandingLayout';
import { Head, Link } from '@inertiajs/react';
import { ArrowRight } from 'lucide-react';
import LandingHero from '@/components/LandingHero';
import HowItWorks from '@/components/HowItWorks';
import WhyPedales from '@/components/WhyPedales';
import SeasonLeaderboard from '@/components/SeasonLeaderboard';
import Footer from '@/components/Footer';
import ScrollReveal from '@/components/ScrollReveal';

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

interface LandingProps {
    auth?: {
        user?: {
            id: string;
            name: string;
        } | null;
    } | null;
    seasonRaces: SeasonRace[];
    activeRaceId: string | null;
    currentStage: CurrentStage | null;
}

export default function Landing({ auth, seasonRaces, activeRaceId, currentStage }: LandingProps) {
    return (
        <LandingLayout auth={auth}>
            <Head title="Pedales — Fantasy Cycling">
                <meta name="description" content="Pedales es el fantasy de ciclismo para Grandes Vueltas. Crea tu liga, pronostica el Top 5, maillots, ganadores de etapa y compite con tus amigos en el Tour de Francia, Giro de Italia y La Vuelta." />
                <meta name="keywords" content="fantasy cycling, ciclismo fantasy, tour de francia, giro de italia, la vuelta, porras ciclismo, juego ciclismo, liga ciclismo" />
                <meta name="robots" content="index, follow" />
                <link rel="preload" as="image" href="/logo-pedales.png" />
                <link rel="preload" as="image" href="/portada-landing.avif" />
            </Head>

            {/* Hero with embedded carousel */}
            <LandingHero auth={auth} seasonRaces={seasonRaces} activeRaceId={activeRaceId} currentStage={currentStage} />

            {/* Cómo funciona */}
            <HowItWorks />

            {/* Features */}
            <WhyPedales />

            {/* CTA final */}
            <section className="py-20 sm:py-28">
                <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <ScrollReveal>
                        <div className="relative overflow-hidden rounded-2xl bg-gradient-to-br from-brand-900 via-brand-800 to-brand-950 px-6 py-16 text-center shadow-xl sm:px-16">
                            <div className="absolute inset-0 bg-[radial-gradient(ellipse_at_bottom_left,_var(--tw-gradient-stops))] from-accent-500/10 via-transparent to-transparent" />
                            <div className="relative">
                                <h2 className="text-3xl font-bold tracking-tight text-white sm:text-4xl">
                                    ¿Preparado para darle a los Pedales?
                                </h2>
                                <p className="mx-auto mt-4 max-w-xl text-gray-300">
                                    Demuestra a tu grupeta quién es el más rápido
                                </p>
                                <div className="mt-8 flex items-center justify-center gap-4">
                                    <Link
                                        href={route('register')}
                                        className="inline-flex h-12 items-center justify-center rounded-md bg-accent-500 px-8 py-3 text-base font-semibold text-white shadow-lg shadow-accent-500/25 transition-all hover:bg-accent-600 hover:shadow-xl hover:shadow-accent-500/30"
                                    >
                                        Crear cuenta gratis
                                        <ArrowRight className="ml-2 h-5 w-5" />
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </ScrollReveal>
                </div>
            </section>

            {/* Footer */}
            <Footer />
        </LandingLayout>
    );
}
