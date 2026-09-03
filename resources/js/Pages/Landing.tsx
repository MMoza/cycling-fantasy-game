import LandingLayout from '@/Layouts/LandingLayout';
import { Head, Link } from '@inertiajs/react';
import { ArrowRight } from 'lucide-react';
import LandingHero from '@/components/LandingHero';
import HowItWorks from '@/components/HowItWorks';
import WhyPedales from '@/components/WhyPedales';
import SeasonLeaderboard from '@/components/SeasonLeaderboard';
import Footer from '@/components/Footer';
import ScrollReveal from '@/components/ScrollReveal';

function MountainIcon({ fill }: { fill: string }) {
    return (
        <svg viewBox="0 0 24 16" className="h-6 w-9" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path
                d="M0 16L8 2L12 8L16 0L24 16H0Z"
                fill={fill}
                className="drop-shadow-sm"
            />
        </svg>
    );
}

const grandTours = [
    { name: 'Tour de Francia', textColor: 'text-yellow-600', fill: '#FACC15' },
    { name: 'Giro de Italia', textColor: 'text-rose-600', fill: '#F43F5E' },
    { name: 'La Vuelta', textColor: 'text-red-600', fill: '#EF4444' },
];

interface LandingProps {
    auth?: {
        user?: {
            id: string;
            name: string;
        } | null;
    } | null;
    activeEdition?: {
        name: string;
        status: string;
    } | null;
    nextEdition?: {
        name: string;
        startDate: string;
    } | null;
}

export default function Landing({ auth, activeEdition, nextEdition }: LandingProps) {
    return (
        <LandingLayout auth={auth}>
            <Head title="Pedales — Fantasy Cycling">
                <meta name="description" content="Pedales es el fantasy de ciclismo para Grandes Vueltas. Crea tu liga, pronostica el Top 5, maillots, ganadores de etapa y compite con tus amigos en el Tour de Francia, Giro de Italia y La Vuelta." />
                <meta name="keywords" content="fantasy cycling, ciclismo fantasy, tour de francia, giro de italia, la vuelta, porras ciclismo, juego ciclismo, liga ciclismo" />
                <meta name="robots" content="index, follow" />
                <link rel="preload" as="image" href="/logo-pedales.png" />
                <link rel="preload" as="image" href="/portada-landing.avif" />
            </Head>

            {/* Hero */}
            <LandingHero activeEdition={activeEdition} nextEdition={nextEdition} auth={auth} />

            {/* Grandes Vueltas — Infinite Carousel */}
            <section className="overflow-hidden border-b bg-muted/30 py-8">
                <div className="marquee-track flex w-max items-center gap-16">
                    {[...grandTours, ...grandTours].map((tour, i) => (
                        <div key={`${tour.name}-${i}`} className="flex items-center gap-3 px-8">
                            <MountainIcon fill={tour.fill} />
                            <span className={`text-xl font-bold tracking-wide ${tour.textColor}`}>
                                {tour.name}
                            </span>
                        </div>
                    ))}
                </div>
            </section>

            {/* Cómo funciona */}
            <HowItWorks />

            {/* Features */}
            <WhyPedales />

            {/* Clasificación pública */}
            <SeasonLeaderboard />

            {/* CTA final */}
            <section className="py-20 sm:py-28">
                <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <ScrollReveal>
                        <div className="relative overflow-hidden rounded-2xl bg-gradient-to-br from-brand-900 via-brand-800 to-brand-950 px-6 py-16 text-center shadow-xl sm:px-16">
                            <div className="absolute inset-0 bg-[radial-gradient(ellipse_at_bottom_left,_var(--tw-gradient-stops))] from-accent-500/10 via-transparent to-transparent" />
                            <div className="relative">
                                <h2 className="text-3xl font-bold tracking-tight text-white sm:text-4xl">
                                    ¿Quién será el primero en tu grupo?
                                </h2>
                                <p className="mx-auto mt-4 max-w-xl text-gray-300">
                                    El Tour 2026 se acerca. Forma tu liga, estudia el recorrido y haz tus apuestas. En los Campos Elíseos solo uno gana el maillot amarillo de tu grupo.
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
