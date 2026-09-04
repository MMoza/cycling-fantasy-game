import { Users, Sliders, Zap } from 'lucide-react';
import { motion } from 'framer-motion';

const FEATURES = [
    {
        icon: Users,
        title: 'Competiciones privadas',
        description: 'Crea tu liga con amigos y compite en grupo.',
    },
    {
        icon: Sliders,
        title: 'Sistema de puntuación',
        description: 'Personaliza las reglas y puntos a tu gusto.',
    },
    {
        icon: Zap,
        title: 'Bonus por rachas',
        description: 'Gana puntos extra por aciertos consecutivos.',
    },
];

export default function ComingSoonCards() {
    return (
        <div className="flex h-full w-full items-center justify-center">
            <div className="grid w-full max-w-3xl grid-cols-1 gap-4 sm:grid-cols-3">
                {FEATURES.map((feature, index) => (
                    <motion.div
                        key={feature.title}
                        initial={{ opacity: 0, y: 20 }}
                        animate={{ opacity: 1, y: 0 }}
                        transition={{ delay: index * 0.15, duration: 0.4 }}
                        className="relative flex flex-col items-center rounded-xl border border-dashed border-border/50 bg-background/40 p-6 text-center backdrop-blur-sm"
                    >
                        <div className="mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-accent-500/10">
                            <feature.icon className="h-6 w-6 text-accent-500" />
                        </div>
                        <h4 className="mb-1 text-sm font-bold text-foreground">{feature.title}</h4>
                        <p className="text-xs text-muted-foreground">{feature.description}</p>
                        <span className="absolute right-3 top-3 rounded-full bg-accent-500/10 px-2 py-0.5 text-[10px] font-semibold text-accent-500">
                            Próximamente
                        </span>
                    </motion.div>
                ))}
            </div>
        </div>
    );
}
