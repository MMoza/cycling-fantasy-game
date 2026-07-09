import AppLayout from '@/Layouts/AppLayout';
import { Head } from '@inertiajs/react';
import { FaApple, FaAndroid } from 'react-icons/fa';
import { Trophy, Users, Route, Medal, Eye, Star, Swords, Target, BarChart3, Shield, Heart, Code, Activity, Server, Smartphone, Users2, Goal, Share, Mail } from 'lucide-react';

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
        title: 'Ligas oficiales',
        description: 'Compite en la liga oficial de cada competición o crea la tuya con amigos. Cada liga con su propio sistema de puntuación.',
    },
    {
        icon: Shield,
        title: 'Ligas privadas',
        description: 'Crea tu liga entre amigos con código de invitación. Solo los que tú elijas pueden entrar y competir.',
    },
    {
        icon: Target,
        title: 'Puntuación custom',
        description: 'Elige entre sistemas predefinidos o crea el tuyo propio. Tú decides cuánto vale cada acierto.',
    },
];

const steps = [
    {
        number: '01',
        icon: Trophy,
        title: 'Únete a la liga oficial',
        description: 'Cada competición tiene una liga oficial abierta para todos. Solo tienes que unirte y empezar a competir.',
    },
    {
        number: '02',
        icon: Target,
        title: 'Haz tus pronósticos',
        description: 'Antes de la Grande Vuelta: elige Top 5, maillots y equipos. Antes de cada etapa: ganador, podio y líder. Todo se sella hasta el cierre.',
    },
    {
        number: '03',
        icon: BarChart3,
        title: 'Sube en la clasificación',
        description: 'Cada acierto suma puntos. El líder juega conservador; el perseguidor arriesga. La emoción hasta el último puerto de montaña.',
    },
];

export default function Pedales() {
    return (
        <AppLayout>
            <Head title="Pedales — El juego" />

            <div className="space-y-16">
                {/* Hero */}
                <section className="relative overflow-hidden rounded-2xl bg-white shadow-xl">
                    <img
                        src="/promo-pedales.png"
                        alt="Pedales - Predict. Compete. Live cycling."
                        className="w-full"
                    />
                </section>

                {/* Instalar la app */}
                <section className="rounded-xl border bg-muted/30 p-6 sm:p-10">
                    <div className="mx-auto max-w-2xl text-center">
                        <h2 className="text-2xl font-bold tracking-tight text-foreground sm:text-3xl">
                            Instalar la app
                        </h2>
                        <p className="mt-3 text-muted-foreground">
                            Pedales es una PWA. Instálala en tu móvil para tenerla siempre a mano.
                        </p>
                    </div>

                    <div className="mt-10 grid gap-8 md:grid-cols-2">
                        {/* iPhone */}
                        <div className="rounded-lg border bg-background p-6">
                        <div className="flex items-center gap-3 mb-4">
                            <FaApple className="h-6 w-6 text-foreground" />
                            <h3 className="font-semibold">iPhone / iPad</h3>
                        </div>
                            <ol className="space-y-3 text-sm text-muted-foreground">
                                <li className="flex items-start gap-3">
                                    <span className="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-accent-500 text-xs font-bold text-white">1</span>
                                    <span>Abre Pedales en <strong className="text-foreground">Safari</strong></span>
                                </li>
                                <li className="flex items-start gap-3">
                                    <span className="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-accent-500 text-xs font-bold text-white">2</span>
                                    <span className="flex items-center gap-1.5">
                                        Pulsa el botón <Share className="h-4 w-4" /> <strong className="text-foreground">Compartir</strong>
                                    </span>
                                </li>
                                <li className="flex items-start gap-3">
                                    <span className="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-accent-500 text-xs font-bold text-white">3</span>
                                    <span className="flex items-center gap-1.5">
                                        Busca y pulsa <strong className="text-foreground">Añadir a pantalla de inicio</strong>
                                    </span>
                                </li>
                                <li className="flex items-start gap-3">
                                    <span className="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-accent-500 text-xs font-bold text-white">4</span>
                                    <span className="flex items-center gap-1.5">
                                        Pulsa <strong className="text-foreground">Añadir</strong>
                                    </span>
                                </li>
                            </ol>
                        </div>

                        {/* Android */}
                        <div className="rounded-lg border bg-background p-6">
                        <div className="flex items-center gap-3 mb-4">
                            <FaAndroid className="h-6 w-6 text-green-600 dark:text-green-400" />
                            <h3 className="font-semibold">Android</h3>
                        </div>
                            <ol className="space-y-3 text-sm text-muted-foreground">
                                <li className="flex items-start gap-3">
                                    <span className="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-accent-500 text-xs font-bold text-white">1</span>
                                    <span>Abre Pedales en <strong className="text-foreground">Chrome</strong></span>
                                </li>
                                <li className="flex items-start gap-3">
                                    <span className="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-accent-500 text-xs font-bold text-white">2</span>
                                    <span className="flex items-center gap-1.5">
                                        Pulsa el menú <span className="text-lg leading-none">⋮</span> <strong className="text-foreground">Más opciones</strong>
                                    </span>
                                </li>
                                <li className="flex items-start gap-3">
                                    <span className="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-accent-500 text-xs font-bold text-white">3</span>
                                    <span className="flex items-center gap-1.5">
                                        Pulsa <strong className="text-foreground">Instalar app</strong> o <strong className="text-foreground">Añadir a pantalla de inicio</strong>
                                    </span>
                                </li>
                                <li className="flex items-start gap-3">
                                    <span className="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-accent-500 text-xs font-bold text-white">4</span>
                                    <span className="flex items-center gap-1.5">
                                        Confirma con <strong className="text-foreground">Instalar</strong>
                                    </span>
                                </li>
                            </ol>
                        </div>
                    </div>
                </section>

                {/* Cómo funciona */}
                <section>
                    <div className="mx-auto max-w-2xl text-center">
                        <h2 className="text-2xl font-bold tracking-tight text-foreground sm:text-3xl">
                            Cómo funciona
                        </h2>
                        <p className="mt-3 text-muted-foreground">
                            Tres pasos para empezar a competir
                        </p>
                    </div>
                    <div className="mt-12 grid gap-8 md:grid-cols-3">
                        {steps.map((step, index) => (
                            <div key={step.title} className="relative">
                                {index < steps.length - 1 && (
                                    <div className="absolute left-8 top-12 hidden h-px w-[calc(100%-4rem)] bg-border md:block" />
                                )}
                                <div className="relative flex flex-col items-center text-center">
                                    <div className="flex h-14 w-14 items-center justify-center rounded-full bg-brand-100 dark:bg-brand-900/30">
                                        <step.icon className="h-6 w-6 text-brand-600 dark:text-brand-400" />
                                    </div>
                                    <span className="mt-3 text-sm font-bold text-accent-500">{step.number}</span>
                                    <h3 className="mt-2 text-base font-semibold">{step.title}</h3>
                                    <p className="mt-2 text-sm text-muted-foreground max-w-xs">{step.description}</p>
                                </div>
                            </div>
                        ))}
                    </div>
                </section>

                {/* Por qué Pedales */}
                <section className="rounded-xl border bg-muted/30 p-6 sm:p-10">
                    <div className="mx-auto max-w-2xl text-center">
                        <h2 className="text-2xl font-bold tracking-tight text-foreground sm:text-3xl">
                            Por qué Pedales
                        </h2>
                        <p className="mt-3 text-muted-foreground">
                            Un fantasy pensado para verdaderos aficionados al ciclismo
                        </p>
                    </div>
                    <div className="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                        {features.map((feature) => (
                            <div
                                key={feature.title}
                                className="rounded-lg border bg-background p-5 transition-shadow hover:shadow-md"
                            >
                                <div className="flex h-10 w-10 items-center justify-center rounded-lg bg-accent-100 dark:bg-accent-900/20">
                                    <feature.icon className="h-5 w-5 text-accent-500" />
                                </div>
                                <h3 className="mt-3 font-semibold text-sm">{feature.title}</h3>
                                <p className="mt-2 text-sm text-muted-foreground">{feature.description}</p>
                            </div>
                        ))}
                    </div>
                </section>

                {/* Reglas del juego */}
                <section className="rounded-xl border bg-background p-6 sm:p-10">
                    <h2 className="text-xl font-bold tracking-tight text-foreground sm:text-2xl">
                        Reglas del juego
                    </h2>
                    <div className="mt-6 space-y-4 text-sm text-muted-foreground">
                        <div className="flex gap-3">
                            <Medal className="h-5 w-5 shrink-0 text-accent-500" />
                            <p>
                                <span className="font-medium text-foreground">Pronósticos sellados:</span> Nadie ve las apuestas de otros hasta que cierra la etapa o la competición.
                            </p>
                        </div>
                        <div className="flex gap-3">
                            <Route className="h-5 w-5 shrink-0 text-accent-500" />
                            <p>
                                <span className="font-medium text-foreground">Antes de cada etapa:</span> Elige ganador, 2º, 3º, líder general y combativo. Se bloquea al inicio.
                            </p>
                        </div>
                        <div className="flex gap-3">
                            <Trophy className="h-5 w-5 shrink-0 text-accent-500" />
                            <p>
                                <span className="font-medium text-foreground">Antes del inicio de la competición:</span> Pronostica Top 5 de la general, maillots (verde, blanco, montaña), equipos y supercombativo.
                            </p>
                        </div>
                    </div>
                </section>

                {/* Sobre el proyecto */}
                <section className="rounded-xl border bg-muted/50 p-6 sm:p-10">
                    <div className="mx-auto max-w-3xl">
                        <div className="flex items-center gap-3 mb-6">
                            <div className="flex h-12 w-12 items-center justify-center rounded-full bg-accent-100 dark:bg-accent-900/30">
                                <Heart className="h-6 w-6 text-accent-500" />
                            </div>
                            <h2 className="text-2xl font-bold tracking-tight text-foreground sm:text-3xl">
                                Sobre el proyecto
                            </h2>
                        </div>

                        <div className="space-y-8 text-muted-foreground">
                            <p className="text-base leading-relaxed">
                                Pedales nace de una necesidad real: <span className="font-medium text-foreground">no existía una aplicación de predicción ciclista que combinara emoción, estrategia y comunidad</span>. Las porras de siempre se hacen en grupos de WhatsApp, sin sistema de puntos, sin historial y sin la emoción de ver cómo subes o bajas en la clasificación en tiempo real.
                            </p>

                            <p className="text-base leading-relaxed">
                                Como desarrollador, quería un proyecto que me obligara a <span className="font-medium text-foreground">seguir aprendiendo y explorando nuevas tecnologías</span>. Pedales es mi campo de pruebas: arquitectura DDD, Laravel 13, React con Inertia, testing con Pest, CI/CD, PWA... Cada decisión técnica tiene un propósito.
                            </p>

                            <p className="text-base leading-relaxed">
                                Pero sobre todo, es un producto que quiero que sea <span className="font-medium text-foreground">100% funcional y utilizable</span>. No un prototipo, no un portfolio. Una app real que puedas usar cada temporada con tus amigos.
                            </p>

                            {/* Estado del proyecto */}
                            <div className="rounded-lg border bg-background p-4">
                                <div className="flex items-center gap-2 mb-3">
                                    <Activity className="h-4 w-4 text-accent-500" />
                                    <h3 className="text-sm font-semibold text-foreground">Estado del proyecto</h3>
                                </div>
                                <p className="flex items-center gap-2 text-sm flex-nowrap">
                                    <span className="inline-flex items-center rounded-full bg-green-600 px-2.5 py-0.5 text-xs font-medium text-white shrink-0">
                                        En producción
                                    </span>
                                    <span>PWA y Web pública con primera competición oficial en funcionamiento.</span>
                                </p>
                            </div>

                            {/* En desarrollo */}
                            <div>
                                <h3 className="text-sm font-semibold text-foreground mb-3 flex items-center gap-2">
                                    <Code className="h-4 w-4 text-accent-500" />
                                    Trabajando en
                                </h3>
                                <ul className="space-y-2 text-sm">
                                    <li className="flex items-start gap-2">
                                        <span className="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full bg-accent-500" />
                                        Mejorar sistemas de puntuación
                                    </li>
                                    <li className="flex items-start gap-2">
                                        <span className="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full bg-accent-500" />
                                        Integración de API de PCS para automatizar resultados
                                    </li>
                                    <li className="flex items-start gap-2">
                                        <span className="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full bg-accent-500" />
                                        Pulir UI para mejor experiencia de usuario
                                    </li>
                                    <li className="flex items-start gap-2">
                                        <span className="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full bg-accent-500" />
                                        Notificaciones push y mailing
                                    </li>
                                    <li className="flex items-start gap-2">
                                        <span className="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full bg-accent-500" />
                                        Integración de SSO de Google
                                    </li>
                                </ul>
                            </div>

                            {/* TODO: Descomentar cuando la app esté más desarrollada
                            <div>
                                <h3 className="text-sm font-semibold text-foreground mb-3 flex items-center gap-2">
                                    <Heart className="h-4 w-4 text-accent-500" />
                                    Tu ayuda permite
                                </h3>
                                <ul className="space-y-2 text-sm">
                                    <li className="flex items-start gap-2">
                                        <Server className="h-4 w-4 shrink-0 text-muted-foreground mt-0.5" />
                                        Escalar servidores y mantener el producto accesible a coste 0€ para el usuario
                                    </li>
                                    <li className="flex items-start gap-2">
                                        <Code className="h-4 w-4 shrink-0 text-muted-foreground mt-0.5" />
                                        Costear la API de PCS
                                    </li>
                                    <li className="flex items-start gap-2">
                                        <Smartphone className="h-4 w-4 shrink-0 text-muted-foreground mt-0.5" />
                                        Desarrollar y publicar apps nativas para Android e iOS
                                    </li>
                                    <li className="flex items-start gap-2">
                                        <Goal className="h-4 w-4 shrink-0 text-muted-foreground mt-0.5" />
                                        Costear un dominio propio
                                    </li>
                                </ul>
                                <p className="mt-4 text-sm font-medium text-foreground">
                                    Objetivo: <span className="text-accent-500">100€/mes</span> para mantener el producto en producción a coste 0€ para todos los usuarios.
                                </p>
                            </div>

                            <div className="rounded-lg border bg-background p-4">
                                <h3 className="text-sm font-semibold text-foreground mb-4 flex items-center gap-2">
                                    <BarChart3 className="h-4 w-4 text-accent-500" />
                                    Métricas
                                </h3>
                                <div className="grid grid-cols-3 gap-4 text-center">
                                    <div>
                                        <div className="flex items-center justify-center gap-1 text-lg font-bold text-foreground">
                                            <Users2 className="h-4 w-4 text-muted-foreground" />
                                            --
                                        </div>
                                        <p className="text-xs text-muted-foreground">Usuarios activos</p>
                                    </div>
                                    <div>
                                        <div className="flex items-center justify-center gap-1 text-lg font-bold text-foreground">
                                            <Heart className="h-4 w-4 text-muted-foreground" />
                                            --
                                        </div>
                                        <p className="text-xs text-muted-foreground">Supporters</p>
                                    </div>
                                    <div>
                                        <div className="flex items-center justify-center gap-1 text-lg font-bold text-foreground">
                                            <Goal className="h-4 w-4 text-muted-foreground" />
                                            -- / 100€
                                        </div>
                                        <p className="text-xs text-muted-foreground">Recaudado este mes</p>
                                    </div>
                                </div>
                            </div>
                            */}
                        </div>

                        {/* TODO: Descomentar botón Ko-fi cuando la app esté más desarrollada
                        <div className="mt-10 flex justify-center pt-8 border-t">
                            <a
                                href="https://ko-fi.com/K0T322T7O6"
                                target="_blank"
                                rel="noopener noreferrer"
                                className="inline-flex items-center gap-2 rounded-md bg-[#72a4f2] px-4 py-2 text-sm font-bold text-white shadow transition-opacity hover:opacity-90"
                                style={{
                                    fontFamily: "'Quicksand', Helvetica, Century Gothic, sans-serif",
                                    lineHeight: '36px',
                                    textDecoration: 'none',
                                }}
                            >
                                <img
                                    src="https://storage.ko-fi.com/cdn/cup-border.png"
                                    alt="Ko-fi"
                                    className="h-4 w-5"
                                    style={{ margin: 0, marginRight: 6 }}
                                />
                                Support me on Ko-fi
                            </a>
                        </div>
                        */}
                    </div>
                </section>

                {/* Contacto */}
                <section className="rounded-xl border bg-muted/50 p-6 sm:p-10">
                    <div className="mx-auto max-w-2xl text-center">
                        <div className="flex items-center justify-center gap-3 mb-4">
                            <div className="flex h-12 w-12 items-center justify-center rounded-full bg-accent-100 dark:bg-accent-900/30">
                                <Mail className="h-6 w-6 text-accent-500" />
                            </div>
                            <h2 className="text-2xl font-bold tracking-tight text-foreground sm:text-3xl">
                                Contacto
                            </h2>
                        </div>
                        <p className="text-muted-foreground">
                            ¿Has encontrado un error? ¿Tienes una sugerencia para mejorar Pedales? Escríbeme y te responderé lo antes posible.
                        </p>
                        <a
                            href="mailto:miguelangelmozabarquilla@gmail.com?subject=Sugerencia%20o%20error%20en%20Pedales"
                            className="mt-6 inline-flex items-center gap-2 rounded-lg bg-accent-500 px-6 py-3 text-sm font-semibold text-white shadow transition-all hover:bg-accent-600"
                        >
                            <Mail className="h-4 w-4" />
                            Enviar mensaje
                        </a>
                    </div>
                </section>
            </div>
        </AppLayout>
    );
}
