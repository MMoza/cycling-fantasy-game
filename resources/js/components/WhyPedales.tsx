import { useRef, useState, useEffect } from 'react';
import { motion, useScroll, useTransform, useInView, MotionValue } from 'framer-motion';
import PedalesLogo from '@/components/PedalesLogo';
import CompetitionCalendar from '@/components/CompetitionCalendar';
import ClassificationCarousel from '@/components/ClassificationCarousel';
import SpecialtyCarousel from '@/components/SpecialtyCarousel';
import SeasonCarousel from '@/components/SeasonCarousel';
import ComingSoonCards from '@/components/ComingSoonCards';

const features = [
    {
        number: '01',
        title: 'Todo el calendario World Tour',
        description: 'Grandes Vueltas, clásicas, monumentos... Todo el ciclismo profesional en un solo sitio.',
    },
    {
        number: '02',
        title: 'Clasificación por competición',
        description: 'Compite sólo las competiciones que más te gusten, cada una tiene su propie clasificación.',
    },
    {
        number: '03',
        title: 'Clasificación por especialidad',
        description: 'Compite sólo en las especialidades que más te gusten. Cada especialidad tiene su ganador a final de temporada.',
    },
    {
        number: '04',
        title: 'Clasificación de la temporada',
        description: 'Puntos acumulados todo el año. El verdadero campeón de Pedales.',
    },
    {
        number: '05',
        title: 'Próximamente',
        description: 'Pedales sigue creciendo e incorporando novedades.',
        badge: 'Próximamente',
    },
];

// --- Scroll architecture ---
// Visual order (as user scrolls): Intro → F1 → F2 → F3 → F4 → F5
// DOM order (reversed for left→right carousel): [F5, F4, F3, F2, F1, Intro]

const VISUAL_BUDGETS = [100, 500, 300, 300, 300, 100]; // Intro, F1, F2, F3, F4, F5 (vh)
const TOTAL_VH = VISUAL_BUDGETS.reduce((a, b) => a + b, 0);
const TRANSITION_FRAC = 20 / TOTAL_VH;

const X_CENTERS = ['-500%', '-400%', '-300%', '-200%', '-100%', '0%'];

// Scroll ranges per visual panel: [start, end] in scrollYProgress (0-1)
const SCROLL_RANGES: [number, number][] = (() => {
    const ranges: [number, number][] = [];
    let cum = 0;
    for (const b of VISUAL_BUDGETS) {
        const start = cum / TOTAL_VH;
        cum += b;
        ranges.push([start, cum / TOTAL_VH]);
    }
    return ranges;
})();

// Piecewise x: slide in (20vh transition), hold, slide out
function buildXTransform() {
    const input: number[] = [];
    const output: string[] = [];
    for (let i = 0; i < VISUAL_BUDGETS.length; i++) {
        const [start, end] = SCROLL_RANGES[i];
        const xc = X_CENTERS[i];
        if (i === 0) {
            input.push(start);
            output.push(xc);
        }
        const holdEnd = end - TRANSITION_FRAC;
        if (holdEnd > start + 0.001) {
            input.push(holdEnd);
            output.push(xc);
        }
        input.push(end);
        output.push(i < VISUAL_BUDGETS.length - 1 ? X_CENTERS[i + 1] : '0%');
    }
    return { input, output };
}

const xTransform = buildXTransform();

function IntroPanel({ scrollYProgress }: { scrollYProgress: MotionValue<number> }) {
    const [start, end] = SCROLL_RANGES[0];
    const p = useTransform(scrollYProgress, [start, end], [0, 1]);
    const opacity = useTransform(p, [0, 0.5], [0, 1]);
    const y = useTransform(p, [0, 0.5], [40, 0]);

    return (
        <div className="flex w-screen flex-shrink-0 items-center justify-center px-6">
            <motion.div className="flex flex-col items-center gap-6 text-center" style={{ opacity, y }}>
                <PedalesLogo className="h-56 w-56 sm:h-72 sm:w-72 lg:h-80 lg:w-80" />
                <h2 className="text-4xl font-black uppercase tracking-tight text-foreground sm:text-6xl lg:text-7xl">
                    ¿Por qué Pedales?
                </h2>
            </motion.div>
        </div>
    );
}

function FeaturePanel({
    feature,
    visualIndex,
    scrollYProgress,
}: {
    feature: (typeof features)[number];
    visualIndex: number;
    scrollYProgress: MotionValue<number>;
}) {
    const [start, end] = SCROLL_RANGES[visualIndex];
    const pp = useTransform(scrollYProgress, [start, end], [0, 1]);

    const headerOpacity = useTransform(pp, [0, 0.08], [0, 1]);
    const descOpacity = useTransform(pp, [0.05, 0.2], [0, 1]);
    const textScale = useTransform(pp, [0, 0.5], [1, 0.6]);
    const textX = useTransform(pp, [0, 0.5], ['0%', '-3%']);
    const textY = useTransform(pp, [0, 0.5], ['35vh', '5rem']);

    // Phase 4 (0.45→0.85): content appears
    const contentOpacity = useTransform(pp, [0.45, 0.6], [0, 1]);
    const contentY = useTransform(pp, [0.45, 0.6], [40, 0]);
    const calendarStagger = useTransform(pp, [0.6, 0.9], [0, 1]);

    return (
        <div className="flex w-screen flex-shrink-0 overflow-hidden">
            <div className="mx-auto flex h-full w-full max-w-7xl flex-col px-4 sm:px-6 lg:px-8">
                <motion.div
                    className="flex flex-shrink-0 flex-col gap-4 origin-top-left"
                    style={{ scale: textScale, x: textX, y: textY }}
                >
                    <motion.div style={{ opacity: headerOpacity }}>
                        <span className="text-8xl font-black tracking-tighter text-foreground/10 sm:text-9xl">{feature.number}</span>
                    </motion.div>

                    <motion.div className="flex items-center gap-3" style={{ opacity: headerOpacity }}>
                        <h3 className="text-4xl font-black uppercase tracking-tight text-foreground sm:text-5xl">{feature.title}</h3>
                        {feature.badge && (
                            <span className="shrink-0 rounded-full bg-accent-500/10 px-3 py-1 text-xs font-semibold text-accent-500">
                                {feature.badge}
                            </span>
                        )}
                    </motion.div>

                    <motion.p className="max-w-md text-lg text-muted-foreground" style={{ opacity: descOpacity }}>
                        {feature.description}
                    </motion.p>
                </motion.div>

                {feature.number === '01' ? (
                    <motion.div
                        className="flex min-h-0 w-full flex-1 overflow-hidden bg-muted/30 mb-4"
                        style={{ opacity: contentOpacity, y: contentY }}
                    >
                        <CompetitionCalendar staggerProgress={calendarStagger} />
                    </motion.div>
                ) : feature.number === '02' ? (
                    <motion.div
                        className="flex min-h-0 w-full flex-1 overflow-hidden bg-muted/30 mb-4"
                        style={{ opacity: contentOpacity, y: contentY }}
                    >
                        <ClassificationCarousel staggerProgress={calendarStagger} />
                    </motion.div>
                ) : feature.number === '03' ? (
                    <motion.div
                        className="flex min-h-0 w-full flex-1 overflow-hidden bg-muted/30 mb-4"
                        style={{ opacity: contentOpacity, y: contentY }}
                    >
                        <SpecialtyCarousel staggerProgress={calendarStagger} />
                    </motion.div>
                ) : feature.number === '04' ? (
                    <motion.div
                        className="flex min-h-0 w-full flex-1 overflow-hidden bg-muted/30 mb-4"
                        style={{ opacity: contentOpacity, y: contentY }}
                    >
                        <SeasonCarousel staggerProgress={calendarStagger} />
                    </motion.div>
                ) : feature.number === '05' ? (
                    <motion.div
                        className="flex min-h-0 w-full flex-1 overflow-hidden bg-muted/30 mb-4"
                        style={{ opacity: contentOpacity, y: contentY }}
                    >
                        <ComingSoonCards />
                    </motion.div>
                ) : (
                    <motion.div
                        className="flex min-h-0 flex-1 items-center justify-center overflow-hidden rounded-2xl bg-muted/50 mb-2"
                        style={{ opacity: contentOpacity, y: contentY }}
                    >
                        <div className="flex h-full w-full items-center justify-center bg-gradient-to-br from-muted/80 to-muted/40">
                            <span className="text-6xl font-black text-muted-foreground/10">{feature.number}</span>
                        </div>
                    </motion.div>
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
    const x = useTransform(scrollYProgress, xTransform.input, xTransform.output);
    const bgX = useTransform(scrollYProgress, [0, 1], ['-500vw', '0vw']);

    const [activeFeature, setActiveFeature] = useState<number | null>(null);

    useEffect(() => {
        const unsub = scrollYProgress.on('change', (v) => {
            for (let i = VISUAL_BUDGETS.length - 1; i >= 1; i--) {
                if (v >= SCROLL_RANGES[i][0] - 0.001) {
                    setActiveFeature(i - 1);
                    return;
                }
            }
            setActiveFeature(null);
        });
        return unsub;
    }, [scrollYProgress]);

    const scrollToFeature = (featureIndex: number) => {
        const container = containerRef.current;
        if (!container) return;
        const vi = featureIndex + 1;
        const [s, e] = SCROLL_RANGES[vi];
        const totalScrollable = container.scrollHeight - window.innerHeight;
        window.scrollTo({ top: container.offsetTop + ((s + e) / 2) * totalScrollable, behavior: 'smooth' });
    };

    const reversed = [...features].reverse();

    return (
        <div ref={containerRef} className="relative bg-muted/30" style={{ height: `${TOTAL_VH}vh` }}>
            <div className="sticky top-0 flex h-screen flex-col overflow-hidden">
                <div className="pointer-events-none absolute inset-0 flex items-center justify-center">
                    <PedalesLogo className="h-[40rem] w-[40rem] opacity-[0.03]" />
                </div>

                <motion.div
                    className="pointer-events-none absolute inset-0 h-full"
                    style={{ x: bgX, width: `${totalPanels * 100}vw` }}
                >
                    <img src="/images/03-why-pedales/background.png" alt="" className="h-full w-full object-cover opacity-[0.12]" />
                </motion.div>

                <motion.div className="relative flex h-full" style={{ x }}>
                    {reversed.map((feature, domIdx) => (
                        <FeaturePanel
                            key={feature.number}
                            feature={feature}
                            visualIndex={totalPanels - 1 - domIdx}
                            scrollYProgress={scrollYProgress}
                        />
                    ))}
                    <IntroPanel scrollYProgress={scrollYProgress} />
                </motion.div>

                <div
                    className={`absolute bottom-0 left-0 right-0 z-10 border-t bg-background/80 backdrop-blur-md transition-transform duration-300 ${
                        isInView ? 'translate-y-0' : 'translate-y-full'
                    }`}
                >
                    <div className="mx-auto flex max-w-7xl">
                        {features.map((feature, index) => (
                            <button
                                key={feature.number}
                                onClick={() => scrollToFeature(index)}
                                className={`relative flex-1 px-4 py-4 text-center text-sm font-semibold uppercase transition-colors ${
                                    activeFeature === index
                                        ? 'bg-accent-500 text-white'
                                        : 'text-muted-foreground hover:text-foreground'
                                }`}
                            >
                                <span className="hidden sm:inline">{feature.title}</span>
                                <span className="sm:hidden">{feature.number}</span>
                            </button>
                        ))}
                    </div>
                </div>
            </div>
        </div>
    );
}
