import { useRef, useState, useEffect } from 'react';
import { motion, useScroll, useTransform, useInView } from 'framer-motion';
import { Calendar, BarChart3, Trophy, Users } from 'lucide-react';
import PedalesLogo from '@/components/PedalesLogo';

const features = [
    {
        number: '01',
        icon: Calendar,
        title: 'Todo el calendario World Tour',
        description: 'Grandes Vueltas, clásicas, championships. Todo el ciclismo profesional en un solo sitio.',
    },
    {
        number: '02',
        icon: BarChart3,
        title: 'Clasificación por competición',
        description: 'Compite en cada carrera por separado. Maillots, etapas y clasificaciones.',
    },
    {
        number: '03',
        icon: Trophy,
        title: 'Clasificación de la temporada',
        description: 'Puntos acumulados todo el año. El verdadero campeón de Pedales.',
    },
    {
        number: '04',
        icon: Users,
        title: 'Próximamente: Ligas privadas',
        description: 'Crea tu liga con amigos, elige tu sistema de puntuación y compite con tu grupo.',
        badge: 'Próximamente',
    },
];

function IntroPanel() {
    return (
        <div className="flex w-screen flex-shrink-0 items-center justify-center px-6">
            <div className="flex flex-col items-center gap-6 text-center">
                <PedalesLogo className="h-56 w-56 sm:h-72 sm:w-72 lg:h-80 lg:w-80" />
                <h2 className="text-4xl font-black uppercase tracking-tight text-foreground sm:text-6xl lg:text-7xl">
                    ¿Por qué Pedales?
                </h2>
            </div>
        </div>
    );
}

function FeaturePanel({ feature }: { feature: (typeof features)[number] }) {
    const Icon = feature.icon;

    return (
        <div className="flex w-screen flex-shrink-0 items-center justify-center">
            <div className="mx-auto grid w-full max-w-7xl grid-cols-1 items-center gap-8 px-4 sm:px-6 lg:grid-cols-2 lg:gap-16 lg:px-8">
                <div className="flex flex-col gap-4">
                    <div className="flex items-center gap-4">
                        <span className="text-8xl font-black tracking-tighter text-foreground/10 sm:text-9xl">{feature.number}</span>
                        <div className="flex h-12 w-12 items-center justify-center rounded-xl bg-accent-500/10">
                            <Icon className="h-6 w-6 text-accent-500" />
                        </div>
                    </div>

                    <div className="flex items-center gap-3">
                        <h3 className="text-4xl font-black uppercase tracking-tight text-foreground sm:text-5xl">{feature.title}</h3>
                        {feature.badge && (
                            <span className="shrink-0 rounded-full bg-accent-500/10 px-3 py-1 text-xs font-semibold text-accent-500">
                                {feature.badge}
                            </span>
                        )}
                    </div>

                    <p className="max-w-md text-lg text-muted-foreground">{feature.description}</p>
                </div>

                <div className="flex h-64 items-center justify-center overflow-hidden rounded-2xl bg-muted/50 lg:h-[28rem]">
                    <div className="flex h-full w-full items-center justify-center bg-gradient-to-br from-muted/80 to-muted/40">
                        <Icon className="h-16 w-16 text-muted-foreground/20" />
                    </div>
                </div>
            </div>
        </div>
    );
}

export default function WhyPedales() {
    const containerRef = useRef<HTMLDivElement>(null);
    const isInView = useInView(containerRef, { amount: 0.15 });

    const { scrollYProgress } = useScroll({
        target: containerRef,
        offset: ['start start', 'end end'],
    });

    const totalPanels = features.length + 1;
    const containerHeight = `${totalPanels * 100}vh`;

    // DOM order reversed: [F4, F3, F2, F1, Intro]
    // x: -400% → 0% → panels slide LEFT to RIGHT, showing Intro → F1 → F2 → F3 → F4
    const x = useTransform(scrollYProgress, [0, 1], [`-${(totalPanels - 1) * 100}%`, '0%']);

    const [activeFeature, setActiveFeature] = useState<number | null>(null);

    useEffect(() => {
        const unsubscribe = scrollYProgress.on('change', (v) => {
            const panelIdx = Math.round(v * (totalPanels - 1));
            setActiveFeature(panelIdx === 0 ? null : panelIdx - 1);
        });
        return unsubscribe;
    }, [scrollYProgress, totalPanels]);

    const scrollToFeature = (featureIndex: number) => {
        const container = containerRef.current;
        if (!container) return;
        const totalScrollable = container.scrollHeight - window.innerHeight;
        const target = ((featureIndex + 1) / totalPanels) * totalScrollable;
        window.scrollTo({ top: container.offsetTop + target, behavior: 'smooth' });
    };

    // Reversed DOM so Intro is last (visible at start with negative x)
    const reversedFeatures = [...features].reverse();

    return (
        <div ref={containerRef} className="relative bg-muted/30" style={{ height: containerHeight }}>
            <div className="sticky top-0 flex h-screen flex-col overflow-hidden">
                <div className="pointer-events-none absolute inset-0 flex items-center justify-center">
                    <PedalesLogo className="h-[40rem] w-[40rem] opacity-[0.03]" />
                </div>

                <motion.div className="flex h-full" style={{ x }}>
                    {reversedFeatures.map((feature) => (
                        <FeaturePanel key={feature.number} feature={feature} />
                    ))}
                    <IntroPanel />
                </motion.div>

                <div
                    className={`absolute bottom-0 left-0 right-0 z-10 border-t bg-background/80 backdrop-blur-md transition-transform duration-300 ${
                        isInView ? 'translate-y-0' : 'translate-y-full'
                    }`}
                >
                    <div className="mx-auto flex max-w-7xl">
                        {features.map((feature, index) => {
                            const isActive = activeFeature === index;
                            return (
                                <button
                                    key={feature.number}
                                    onClick={() => scrollToFeature(index)}
                                    className={`relative flex-1 px-4 py-4 text-center text-sm font-semibold uppercase transition-colors ${
                                        isActive
                                            ? 'bg-accent-500 text-white'
                                            : 'text-muted-foreground hover:text-foreground'
                                    }`}
                                >
                                    <span className="hidden sm:inline">{feature.title}</span>
                                    <span className="sm:hidden">{feature.number}</span>
                                </button>
                            );
                        })}
                    </div>
                </div>
            </div>
        </div>
    );
}
