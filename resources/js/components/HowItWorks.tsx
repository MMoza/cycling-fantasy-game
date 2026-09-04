import { useRef, useState, useEffect } from 'react';
import { motion, useScroll, useTransform, useInView } from 'framer-motion';
import { ImageIcon } from 'lucide-react';
import PedalesLogo from '@/components/PedalesLogo';

const steps = [
    {
        number: '01',
        title: 'Regístrate',
        description: 'Crea tu cuenta gratis en segundos con Google o email. Sin complicaciones, sin formularios interminables.',
        image: '/images/02-how-it-work/01-register.png',
    },
    {
        number: '02',
        title: 'Únete a la competición',
        description: 'Entra en la liga oficial del Tour, la Vuelta o cualquier clásica del World Tour. Una liga abierta para todos.',
        image: null,
    },
    {
        number: '03',
        title: 'Pronostica',
        description: 'Antes de cada carrera y etapa elige ganador, podio y líder. Tus pronósticos se sellan — nadie los ve hasta el cierre.',
        image: null,
    },
    {
        number: '04',
        title: 'Compite',
        description: 'Sube en la clasificación, desafía a tus amigos y demuestra tu conocimiento del ciclismo profesional.',
        image: null,
    },
];

function IntroPanel() {
    return (
        <div className="flex w-screen flex-shrink-0 items-center justify-center px-6">
            <motion.div
                className="flex flex-col items-center gap-6 text-center"
                initial={{ opacity: 0, x: 80 }}
                whileInView={{ opacity: 1, x: 0 }}
                viewport={{ once: false, amount: 0.4 }}
                transition={{ duration: 0.5, ease: 'easeOut' }}
            >
                <PedalesLogo className="h-56 w-56 sm:h-72 sm:w-72 lg:h-80 lg:w-80" />
                <h2 className="text-4xl font-black uppercase tracking-tight text-foreground sm:text-6xl lg:text-7xl">
                    ¿Cómo funciona Pedales?
                </h2>
            </motion.div>
        </div>
    );
}

function StepPanel({ step, index }: { step: (typeof steps)[number]; index: number }) {
    return (
        <div className="flex w-screen flex-shrink-0 items-center justify-center">
            <div className="mx-auto grid w-full max-w-7xl grid-cols-1 items-center gap-8 px-4 sm:px-6 lg:grid-cols-2 lg:gap-16 lg:px-8">
                {/* Text */}
                <motion.div
                    className="flex flex-col gap-4"
                    initial={{ opacity: 0, x: 80 }}
                    whileInView={{ opacity: 1, x: 0 }}
                    viewport={{ once: false, amount: 0.4 }}
                    transition={{ duration: 0.5, ease: 'easeOut' }}
                >
                    <span className="text-8xl font-black tracking-tighter text-foreground/10 sm:text-9xl">{step.number}</span>
                    <h3 className="text-4xl font-black uppercase tracking-tight text-foreground sm:text-5xl">{step.title}</h3>
                    <p className="max-w-md text-lg text-muted-foreground">{step.description}</p>
                </motion.div>

                {/* Image */}
                <motion.div
                    className="flex h-64 items-center justify-center overflow-hidden rounded-2xl bg-muted/50 lg:h-[28rem]"
                    initial={{ opacity: 0, x: 80 }}
                    whileInView={{ opacity: 1, x: 0 }}
                    viewport={{ once: false, amount: 0.4 }}
                    transition={{ duration: 0.5, delay: 0.12, ease: 'easeOut' }}
                >
                    {step.image ? (
                        <img src={step.image} alt={step.title} className="h-full w-full object-cover" />
                    ) : (
                        <ImageIcon className="h-16 w-16 text-muted-foreground/20" />
                    )}
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

    const totalPanels = steps.length + 1; // intro + 4 steps
    const containerHeight = `${totalPanels * 100}vh`;
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

    const scrollTo = (panelIndex: number) => {
        const container = containerRef.current;
        if (!container) return;
        const totalScrollable = container.scrollHeight - window.innerHeight;
        const target = (panelIndex / (totalPanels - 1)) * totalScrollable;
        window.scrollTo({ top: container.offsetTop + target, behavior: 'smooth' });
    };

    return (
        <div ref={containerRef} className="relative" style={{ height: containerHeight }}>
            {/* Sticky viewport */}
            <div className="sticky top-0 flex h-screen flex-col overflow-hidden">
                {/* Background logo */}
                <div className="pointer-events-none absolute inset-0 flex items-center justify-center">
                    <PedalesLogo className="h-[40rem] w-[40rem] opacity-[0.03]" />
                </div>

                {/* Horizontal panels */}
                <motion.div
                    ref={scrollRef}
                    className="flex h-full"
                    style={{ x }}
                >
                    <IntroPanel />
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
                                onClick={() => scrollTo(index + 1)}
                                className={`relative flex-1 px-4 py-4 text-center text-sm font-medium transition-colors ${
                                    index + 1 === activeIndex
                                        ? 'text-accent-500'
                                        : 'text-muted-foreground hover:text-foreground'
                                }`}
                            >
                                <span className="hidden sm:inline">{step.title}</span>
                                <span className="sm:hidden">{step.number}</span>
                                {index + 1 === activeIndex && (
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
