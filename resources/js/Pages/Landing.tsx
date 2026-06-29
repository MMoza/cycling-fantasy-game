import LandingLayout from '@/Layouts/LandingLayout';
import { Head, Link } from '@inertiajs/react';
import { Bike, Trophy, Users, Route, Medal, ArrowRight, Star, Eye, Swords } from 'lucide-react';

const features = [
    {
        icon: Swords,
        title: 'Modo Híbrido',
        description: 'Antes del Tour: elige Top 5 General, Maillot Verde, Blanco, Montaña y equipos. En cada etapa: acierta ganador, podio y líder. Estrategia global y microdecisiones.',
    },
    {
        icon: Star,
        title: '6 Clasificaciones',
        description: 'Puntúas por la General, la Montaña, el Sprint, la Juventud, el Supercombativo y los Equipos. Cada etapa reparte puntos en múltiples frentes.',
    },
    {
        icon: Eye,
        title: 'Pronósticos sellados',
        description: 'Nadie ve tus apuestas hasta que cierra la etapa. Sin copiar a los favoritos, sin dejarse llevar. Solo tú y tu criterio.',
    },
    {
        icon: Users,
        title: 'Ligas privadas',
        description: 'Monta tu liga entre amigos o únete a una ya creada. Cada uno con su código. Compites en tu propio grupo, pero contra todo el pelotón.',
    },
];

const steps = [
    {
        number: '01',
        icon: Trophy,
        title: 'Monta tu liga',
        description: 'Crea una liga para el Tour, el Giro o la Vuelta. Invita a tus amigos con un código y elegid juntos el sistema de puntuación.',
    },
    {
        number: '02',
        icon: Medal,
        title: 'Haz tus pronósticos',
        description: 'Antes de la Grandísima Vuelta: elige Top 5, maillots y equipos. Antes de cada etapa: ganador, podio y líder. Todo se sella hasta el cierre.',
    },
    {
        number: '03',
        icon: Route,
        title: 'Súe en la clasificación',
        description: 'Cada acierto suma puntos. El líder juega conservador; el perseguidor arriesga. La emoción hasta el último puerto de montaña.',
    },
];

const grandTours = [
    { name: 'Tour de Francia', color: 'bg-yellow-400', textColor: 'text-yellow-600' },
    { name: 'Giro de Italia', color: 'bg-rose-500', textColor: 'text-rose-600' },
    { name: 'La Vuelta', color: 'bg-red-500', textColor: 'text-red-600' },
];

export default function Landing() {
    return (
        <LandingLayout>
            <Head title="Pedales — Fantasy Cycling" />

            {/* Hero */}
            <section className="relative overflow-hidden">
                <div className="absolute inset-0 bg-gradient-to-br from-brand-900 via-brand-800 to-brand-950" />
                <div className="absolute inset-0 bg-[radial-gradient(ellipse_at_top_right,_var(--tw-gradient-stops))] from-accent-500/10 via-transparent to-transparent" />
                <div className="relative mx-auto max-w-7xl px-4 py-24 sm:px-6 sm:py-32 lg:px-8">
                    <div className="mx-auto max-w-3xl text-center">
                        
                        <h1 className="text-4xl font-bold tracking-tight text-white sm:text-6xl">
                            PEDALES
                            <span className="block text-accent-400">Fantasy Cycling</span>
                        </h1>
                        <p className="mt-6 text-lg leading-8 text-gray-300">
                            Elige tu top 5 para los Campos Elíseos, el maillot verde y la montaña.
                            Después, acierta el ganador de cada etapa, el podio y quién viste el amarillo.
                            Gana puntos, supera a tus amigos y demuestra quién manda en el pelotón.
                        </p>
                        <div className="mt-10 flex items-center justify-center gap-4">
                            <Link
                                href={route('register')}
                                className="inline-flex h-12 items-center justify-center rounded-md bg-accent-500 px-8 py-3 text-base font-semibold text-white shadow-lg shadow-accent-500/25 transition-all hover:bg-accent-600 hover:shadow-xl hover:shadow-accent-500/30"
                            >
                                Crear cuenta gratis
                                <ArrowRight className="ml-2 h-5 w-5" />
                            </Link>
                            <Link
                                href={route('login')}
                                className="inline-flex h-12 items-center justify-center rounded-md border border-white/20 px-8 py-3 text-base font-semibold text-white/90 transition-all hover:border-white/40 hover:text-white"
                            >
                                Iniciar sesión
                            </Link>
                        </div>
                    </div>
                </div>
            </section>

            {/* Grandes Vueltas */}
            <section className="border-b bg-muted/30">
                <div className="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
                    <div className="flex flex-col items-center gap-6 sm:flex-row sm:justify-center sm:gap-12">
                        {grandTours.map((tour) => (
                            <div key={tour.name} className="flex items-center gap-3">
                                <div className={`h-4 w-4 rounded-full ${tour.color}`} />
                                <span className={`text-lg font-semibold ${tour.textColor}`}>
                                    {tour.name}
                                </span>
                            </div>
                        ))}
                    </div>
                </div>
            </section>

            {/* Cómo funciona */}
            <section className="py-20 sm:py-28">
                <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div className="mx-auto max-w-2xl text-center">
                        <h2 className="text-3xl font-bold tracking-tight text-foreground sm:text-4xl">
                            Cómo funciona
                        </h2>
                        <p className="mt-4 text-muted-foreground">
                            Tres pasos para empezar a competir en las Grandes Vueltas
                        </p>
                    </div>
                    <div className="mt-16 grid gap-8 md:grid-cols-3">
                        {steps.map((step, index) => (
                            <div key={step.title} className="relative">
                                {index < steps.length - 1 && (
                                    <div className="absolute left-8 top-16 hidden h-px w-[calc(100%-4rem)] bg-border md:block" />
                                )}
                                <div className="relative flex flex-col items-center text-center">
                                    <div className="flex h-16 w-16 items-center justify-center rounded-full bg-brand-100 dark:bg-brand-900/30">
                                        <step.icon className="h-7 w-7 text-brand-600 dark:text-brand-400" />
                                    </div>
                                    <span className="mt-4 text-sm font-bold text-accent-500">{step.number}</span>
                                    <h3 className="mt-2 text-lg font-semibold">{step.title}</h3>
                                    <p className="mt-2 text-sm text-muted-foreground max-w-xs">{step.description}</p>
                                </div>
                            </div>
                        ))}
                    </div>
                </div>
            </section>

            {/* Features */}
            <section className="bg-muted/30 py-20 sm:py-28">
                <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div className="mx-auto max-w-2xl text-center">
                        <h2 className="text-3xl font-bold tracking-tight text-foreground sm:text-4xl">
                            Por qué Pedales
                        </h2>
                        <p className="mt-4 text-muted-foreground">
                            Un fantasy pensado para verdaderos aficionados al ciclismo
                        </p>
                    </div>
                    <div className="mt-16 grid gap-8 sm:grid-cols-2 lg:grid-cols-4">
                        {features.map((feature) => (
                            <div
                                key={feature.title}
                                className="rounded-lg border bg-background p-6 transition-shadow hover:shadow-md"
                            >
                                <div className="flex h-10 w-10 items-center justify-center rounded-lg bg-accent-100 dark:bg-accent-900/20">
                                    <feature.icon className="h-5 w-5 text-accent-500" />
                                </div>
                                <h3 className="mt-4 font-semibold">{feature.title}</h3>
                                <p className="mt-2 text-sm text-muted-foreground">{feature.description}</p>
                            </div>
                        ))}
                    </div>
                </div>
            </section>

            {/* CTA final */}
            <section className="py-20 sm:py-28">
                <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
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
                </div>
            </section>
        </LandingLayout>
    );
}
