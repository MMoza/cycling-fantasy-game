import { useRef, useState, useEffect } from 'react';
import { motion, useScroll, useTransform, useInView, MotionValue } from 'framer-motion';
import { Calendar, BarChart3, Trophy, Users } from 'lucide-react';
import PedalesLogo from '@/components/PedalesLogo';
import CompetitionCalendar from '@/components/CompetitionCalendar';

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

function FeaturePanel({
    feature,
    panelIndex,
    totalPanels,
    scrollYProgress,
}: {
    feature: (typeof features)[number];
    panelIndex: number;
    totalPanels: number;
    scrollYProgress: MotionValue<number>;
}) {
    const Icon = feature.icon;

    const vCenter = (totalPanels - 1 - panelIndex) / (totalPanels - 1);
    const vHalf = 1 / (totalPanels - 1) / 2;

    // Full panel progress: 0 when entering, 1 when centered, continues until next panel
    const vStart = vCenter - vHalf;
    const vEnd = vCenter + vHalf;
    const panelProgress = useTransform(scrollYProgress, [vStart, vEnd], [0, 1]);

    // Phase 1 (0 → 0.35): Number + title appear centered
    const headerOpacity = useTransform(panelProgress, [0, 0.35], [0, 1]);
    const headerY = useTransform(panelProgress, [0, 0.35], [40, 0]);

    // Phase 2 (0.3 → 0.6): Description appears
    const descOpacity = useTransform(panelProgress, [0.3, 0.6], [0, 1]);
    const descY = useTransform(panelProgress, [0.3, 0.6], [20, 0]);

    // Phase 3 (0.55 → 1.0): Text moves up+left, calendar appears
    const textX = useTransform(panelProgress, [0.55, 1], ['0%', '-8%']);
    const textY = useTransform(panelProgress, [0.55, 1], ['0%', '-12%']);
    const textScale = useTransform(panelProgress, [0.55, 1], [1, 0.88]);

    const calendarOpacity = useTransform(panelProgress, [0.55, 0.85], [0, 1]);
    const calendarX = useTransform(panelProgress, [0.55, 0.85], [60, 0]);

    const isStep01 = feature.number === '01';

    return (
        <div className="flex w-screen flex-shrink-0 items-center justify-center">
            <div className="mx-auto grid w-full max-w-7xl grid-cols-1 items-center gap-8 px-4 sm:px-6 lg:grid-cols-2 lg:gap-16 lg:px-8">
                {/* Text side */}
                <motion.div
                    className="flex flex-col gap-4"
                    style={{
                        x: isStep01 ? textX : 0,
                        y: isStep01 ? textY : 0,
                        scale: isStep01 ? textScale : 1,
                    }}
                >
                    <motion.div
                        className="flex items-center gap-4"
                        style={{ opacity: headerOpacity, y: headerY }}
                    >
                        <span className="text-8xl font-black tracking-tighter text-foreground/10 sm:text-9xl">{feature.number}</span>
                        <div className="flex h-12 w-12 items-center justify-center rounded-xl bg-accent-500/10">
                            <Icon className="h-6 w-6 text-accent-500" />
                        </div>
                    </motion.div>

                    <motion.div
                        className="flex items-center gap-3"
                        style={{ opacity: headerOpacity, y: headerY }}
                    >
                        <h3 className="text-4xl font-black uppercase tracking-tight text-foreground sm:text-5xl">{feature.title}</h3>
                        {feature.badge && (
                            <span className="shrink-0 rounded-full bg-accent-500/10 px-3 py-1 text-xs font-semibold text-accent-500">
                                {feature.badge}
                            </span>
                        )}
                    </motion.div>

                    <motion.p
                        className="max-w-md text-lg text-muted-foreground"
                        style={{ opacity: descOpacity, y: descY }}
                    >
                        {feature.description}
                    </motion.p>
                </motion.div>

                {/* Right side */}
                {isStep01 ? (
                    <motion.div
                        className="flex h-64 overflow-hidden rounded-2xl bg-muted/30 lg:h-[28rem]"
                        style={{ opacity: calendarOpacity, x: calendarX }}
                    >
                        <CompetitionCalendar />
                    </motion.div>
                ) : (
                    <div className="flex h-64 items-center justify-center overflow-hidden rounded-2xl bg-muted/50 lg:h-[28rem]">
                        <div className="flex h-full w-full items-center justify-center bg-gradient-to-br from-muted/80 to-muted/40">
                            <Icon className="h-16 w-16 text-muted-foreground/20" />
                        </div>
                    </div>
                )}
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

    const x = useTransform(scrollYProgress, [0, 1], [`-${(totalPanels - 1) * 100}%`, '0%']);
    const bgX = useTransform(scrollYProgress, [0, 1], [`-${(totalPanels - 1) * 100}vw`, '0vw']);

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

    const reversedFeatures = [...features].reverse();

    return (
        <div ref={containerRef} className="relative bg-muted/30" style={{ height: containerHeight }}>
            <div className="sticky top-0 flex h-screen flex-col overflow-hidden">
                <div className="pointer-events-none absolute inset-0 flex items-center justify-center">
                    <PedalesLogo className="h-[40rem] w-[40rem] opacity-[0.03]" />
                </div>

                <motion.div
                    className="pointer-events-none absolute inset-0 h-full"
                    style={{ x: bgX, width: `${totalPanels * 100}vw` }}
                >
                    <img
                        src="/images/03-why-pedales/background.png"
                        alt=""
                        className="h-full w-full object-cover opacity-[0.12]"
                    />
                </motion.div>

                <motion.div className="relative flex h-full" style={{ x }}>
                    {reversedFeatures.map((feature, index) => (
                        <FeaturePanel
                            key={feature.number}
                            feature={feature}
                            panelIndex={index}
                            totalPanels={totalPanels}
                            scrollYProgress={scrollYProgress}
                        />
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
