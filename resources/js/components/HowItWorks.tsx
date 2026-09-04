import { useRef, useState, useEffect } from 'react';
import { motion, useScroll, useTransform, useInView } from 'framer-motion';
import { UserPlus, Trophy, Target, TrendingUp, ImageIcon } from 'lucide-react';

const steps = [
    {
        number: '01',
        icon: UserPlus,
        title: 'Regístrate',
        description: 'Crea tu cuenta gratis en segundos con Google o email. Sin complicaciones, sin formularios interminables.',
    },
    {
        number: '02',
        icon: Trophy,
        title: 'Únete a la competición',
        description: 'Entra en la liga oficial del Tour, la Vuelta o cualquier clásica del World Tour. Una liga abierta para todos.',
    },
    {
        number: '03',
        icon: Target,
        title: 'Pronostica',
        description: 'Antes de cada carrera y etapa elige ganador, podio y líder. Tus pronósticos se sellan — nadie los ve hasta el cierre.',
    },
    {
        number: '04',
        icon: TrendingUp,
        title: 'Compite',
        description: 'Sube en la clasificación, desafía a tus amigos y demuestra tu conocimiento del ciclismo profesional.',
    },
];

function StepPanel({ step, index }: { step: (typeof steps)[number]; index: number }) {
    return (
        <div className="flex w-screen flex-shrink-0 items-center justify-center">
            <div className="mx-auto grid w-full max-w-7xl grid-cols-1 items-center gap-8 px-4 sm:px-6 lg:grid-cols-2 lg:gap-16 lg:px-8">
                {/* Text */}
                <motion.div
                    className="flex flex-col gap-6"
                    initial={{ opacity: 0, x: 80 }}
                    whileInView={{ opacity: 1, x: 0 }}
                    viewport={{ once: false, amount: 0.4 }}
                    transition={{ duration: 0.5, ease: 'easeOut' }}
                >
                    <div className="flex items-center gap-4">
                        <span className="text-6xl font-bold text-muted/40 sm:text-7xl">{step.number}</span>
                        <div className="flex h-12 w-12 items-center justify-center rounded-xl bg-accent-500/10">
                            <step.icon className="h-6 w-6 text-accent-500" />
                        </div>
                    </div>
                    <h3 className="text-3xl font-bold tracking-tight text-foreground sm:text-4xl">{step.title}</h3>
                    <p className="max-w-md text-lg text-muted-foreground">{step.description}</p>
                </motion.div>

                {/* Image placeholder */}
                <motion.div
                    className="flex h-64 items-center justify-center rounded-2xl bg-muted/50 lg:h-[28rem]"
                    initial={{ opacity: 0, x: 80 }}
                    whileInView={{ opacity: 1, x: 0 }}
                    viewport={{ once: false, amount: 0.4 }}
                    transition={{ duration: 0.5, delay: 0.12, ease: 'easeOut' }}
                >
                    <ImageIcon className="h-16 w-16 text-muted-foreground/20" />
                </motion.div>
            </div>
        </div>
    );
}

export default function HowItWorks() {
    const containerRef = useRef<HTMLDivElement>(null);
    const scrollRef = useRef<HTMLDivElement>(null);
    const isInView = useInView(containerRef, { amount: 0.15 });

    const { scrollYProgress } = useScroll({
        target: containerRef,
        offset: ['start start', 'end end'],
    });

    const totalPanels = steps.length;
    const x = useTransform(scrollYProgress, [0, 1], ['0%', `-${(totalPanels - 1) * 100}%`]);

    // Track active panel from scroll progress
    const [activeIndex, setActiveIndex] = useState(0);

    useEffect(() => {
        const unsubscribe = scrollYProgress.on('change', (v) => {
            const idx = Math.round(v * (totalPanels - 1));
            setActiveIndex(Math.min(idx, totalPanels - 1));
        });
        return unsubscribe;
    }, [scrollYProgress, totalPanels]);

    const scrollTo = (index: number) => {
        const container = containerRef.current;
        if (!container) return;
        const panelWidth = window.innerWidth;
        const totalScrollable = panelWidth * (totalPanels - 1);
        const target = (index / (totalPanels - 1)) * totalScrollable;
        const containerTop = container.offsetTop;
        window.scrollTo({ top: containerTop + target, behavior: 'smooth' });
    };

    return (
        <div ref={containerRef} className="relative h-[400vh]">
            {/* Sticky viewport */}
            <div className="sticky top-0 flex h-screen flex-col overflow-hidden">
                {/* Section title — desktop only, top-left */}
                <div className="absolute top-0 left-0 z-10 px-4 pt-20 sm:px-6 lg:px-8">
                    <h2 className="text-3xl font-bold tracking-tight text-foreground sm:text-4xl">
                        Cómo funciona
                    </h2>
                </div>

                {/* Horizontal panels */}
                <motion.div
                    ref={scrollRef}
                    className="flex h-full"
                    style={{ x }}
                >
                    {steps.map((step, index) => (
                        <StepPanel key={step.number} step={step} index={index} />
                    ))}
                </motion.div>

                {/* Tab bar */}
                <div className="absolute bottom-0 left-0 right-0 z-10 border-t bg-background/80 backdrop-blur-md">
                    <div className="mx-auto flex max-w-7xl">
                        {steps.map((step, index) => (
                            <button
                                key={step.number}
                                onClick={() => scrollTo(index)}
                                className={`relative flex-1 px-4 py-4 text-center text-sm font-medium transition-colors ${
                                    index === activeIndex
                                        ? 'text-accent-500'
                                        : 'text-muted-foreground hover:text-foreground'
                                }`}
                            >
                                <span className="hidden sm:inline">{step.title}</span>
                                <span className="sm:hidden">{step.number}</span>
                                {index === activeIndex && (
                                    <motion.div
                                        layoutId="activeTab"
                                        className="absolute bottom-0 left-0 right-0 h-0.5 bg-accent-500"
                                        transition={{ type: 'spring', stiffness: 400, damping: 30 }}
                                    />
                                )}
                            </button>
                        ))}
                    </div>
                </div>
            </div>
        </div>
    );
}
