import { Calendar, BarChart3, Trophy, Users } from 'lucide-react';
import ScrollReveal from '@/components/ScrollReveal';

const features = [
    {
        icon: Calendar,
        title: 'Todo el calendario World Tour',
        description: 'Grandes Vueltas, clásicas, championships. Todo el ciclismo profesional en un solo sitio.',
    },
    {
        icon: BarChart3,
        title: 'Clasificación por competición',
        description: 'Compite en cada carrera por separado. Maillots, etapas y clasificaciones.',
    },
    {
        icon: Trophy,
        title: 'Clasificación de la temporada',
        description: 'Puntos acumulados todo el año. El verdadero campeón de Pedales.',
    },
    {
        icon: Users,
        title: 'Próximamente: Ligas privadas',
        description: 'Crea tu liga con amigos, elige tu sistema de puntuación y compite en tu grupo.',
        badge: 'Próximamente',
    },
];

export default function WhyPedales() {
    return (
        <section className="bg-muted/30 py-20 sm:py-28">
            <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <ScrollReveal>
                    <div className="mx-auto max-w-2xl text-center">
                        <h2 className="text-3xl font-bold tracking-tight text-foreground sm:text-4xl">
                            Por qué Pedales
                        </h2>
                        <p className="mt-4 text-muted-foreground">
                            Un fantasy pensado para verdaderos aficionados al ciclismo
                        </p>
                    </div>
                </ScrollReveal>
                <div className="mt-16 grid gap-8 sm:grid-cols-2 lg:grid-cols-4">
                    {features.map((feature, index) => (
                        <ScrollReveal key={feature.title} delay={index * 0.1}>
                            <div className="relative rounded-lg border bg-background p-6 transition-shadow hover:shadow-md h-full">
                                {feature.badge && (
                                    <span className="absolute right-4 top-4 rounded-full bg-accent-100 px-2 py-0.5 text-xs font-medium text-accent-700 dark:bg-accent-900/20 dark:text-accent-400">
                                        {feature.badge}
                                    </span>
                                )}
                                <div className="flex h-10 w-10 items-center justify-center rounded-lg bg-accent-100 dark:bg-accent-900/20">
                                    <feature.icon className="h-5 w-5 text-accent-500" />
                                </div>
                                <h3 className="mt-4 font-semibold">{feature.title}</h3>
                                <p className="mt-2 text-sm text-muted-foreground">{feature.description}</p>
                            </div>
                        </ScrollReveal>
                    ))}
                </div>
            </div>
        </section>
    );
}
