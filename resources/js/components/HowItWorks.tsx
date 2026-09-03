import { Trophy, Target, TrendingUp, UserPlus } from 'lucide-react';
import ScrollReveal from '@/components/ScrollReveal';

const steps = [
    {
        number: '01',
        icon: UserPlus,
        title: 'Regístrate',
        description: 'Crea tu cuenta en segundos con Google o email.',
    },
    {
        number: '02',
        icon: Trophy,
        title: 'Únete a cualquier competición UCI World Tour',
        description: 'Desde el Tour de Francia hasta las clásicas monumento.',
    },
    {
        number: '03',
        icon: Target,
        title: 'Haz tus pronósticos',
        description: 'Antes de cada carrera y etapa, elige tus favoritos.',
    },
    {
        number: '04',
        icon: TrendingUp,
        title: 'Compite contra la comunidad',
        description: 'Sube en la clasificación y demuestra tu conocimiento del ciclismo.',
    },
];

export default function HowItWorks() {
    return (
        <section className="py-20 sm:py-28">
            <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <ScrollReveal>
                    <div className="mx-auto max-w-2xl text-center">
                        <h2 className="text-3xl font-bold tracking-tight text-foreground sm:text-4xl">
                            Cómo funciona
                        </h2>
                        <p className="mt-4 text-muted-foreground">
                            Cuatro pasos para empezar a competir en las Grandes Vueltas
                        </p>
                    </div>
                </ScrollReveal>
                <div className="mt-16 grid gap-8 md:grid-cols-4">
                    {steps.map((step, index) => (
                        <ScrollReveal key={step.title} delay={index * 0.1}>
                            <div className="relative">
                                {index < steps.length - 1 && (
                                    <div className="absolute left-8 top-16 hidden h-px w-[calc(100%-4rem)] bg-border md:block" />
                                )}
                                <div className="relative flex flex-col items-center text-center">
                                    <div className="relative">
                                        <span className="absolute -left-2 -top-2 text-5xl font-bold text-muted/40">
                                            {step.number}
                                        </span>
                                        <div className="relative flex h-16 w-16 items-center justify-center rounded-full bg-brand-100 dark:bg-brand-900/30">
                                            <step.icon className="h-7 w-7 text-brand-600 dark:text-brand-400" />
                                        </div>
                                    </div>
                                    <h3 className="mt-6 text-lg font-semibold">{step.title}</h3>
                                    <p className="mt-2 text-sm text-muted-foreground max-w-xs">{step.description}</p>
                                </div>
                            </div>
                        </ScrollReveal>
                    ))}
                </div>
            </div>
        </section>
    );
}
