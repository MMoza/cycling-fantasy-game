import { motion, MotionValue, useTransform } from 'framer-motion';

const categories = [
    {
        name: 'Grand Tours',
        races: ['Tour de France', "Giro d'Italia", 'Vuelta a España'],
    },
    {
        name: 'Major Tours',
        races: [
            'Paris-Nice',
            'Tirreno-Adriatico',
            'Volta a Catalunya',
            'Itzulia Basque Country',
            'Tour de Romandie',
            'Tour de Suisse',
            'Tour Auvergne-Rhône-Alpes',
        ],
    },
    {
        name: 'Monuments',
        races: ['Milano-SanRemo', 'Ronde van Vlaanderen', 'Paris-Roubaix', 'Liège-Bastogne-Liège', 'Il Lombardia'],
    },
    {
        name: 'Championships',
        races: ['World Championships', 'European Championships'],
    },
    {
        name: 'Top Classics',
        races: [
            'Omloop Het Nieuwsblad',
            'Strade Bianche',
            'E3 Classic',
            'Gent-Wevelgem',
            'Dwars door Vlaanderen',
            'Eschborn-Frankfurt',
            'Amstel Gold Race',
            'La Flèche Wallonne',
            'San Sebastian',
            'Bretagne Classic',
            'GP Québec',
            'GP Montréal',
        ],
    },
];

function AnimatedCategoryCard({ category, progress }: { category: (typeof categories)[number]; progress: MotionValue<number> }) {
    const opacity = useTransform(progress, [0, 1], [0, 1]);
    const y = useTransform(progress, [0, 1], [20, 0]);

    return (
        <motion.div
            className="rounded-xl border border-border/50 bg-background/60 p-4 backdrop-blur-sm"
            style={{ opacity, y }}
        >
            <h4 className="mb-3 text-xs font-bold uppercase tracking-wider text-accent-500">{category.name}</h4>
            <ul className="space-y-1.5">
                {category.races.map((race) => (
                    <li key={race} className="text-sm text-foreground/80">
                        {race}
                    </li>
                ))}
            </ul>
        </motion.div>
    );
}

function AnimatedCategoryCarousel({ category, progress }: { category: (typeof categories)[number]; progress: MotionValue<number> }) {
    const opacity = useTransform(progress, [0, 1], [0, 1]);
    const y = useTransform(progress, [0, 1], [16, 0]);

    return (
        <motion.div className="flex flex-col gap-2" style={{ opacity, y }}>
            <h4 className="text-xs font-bold uppercase tracking-wider text-accent-500">{category.name}</h4>
            <div className="flex gap-2 overflow-x-auto scrollbar-hide">
                {category.races.map((race) => (
                    <span
                        key={race}
                        className="shrink-0 rounded-lg border border-border/50 bg-background/60 px-3 py-2 text-xs font-medium text-foreground/80 backdrop-blur-sm"
                    >
                        {race}
                    </span>
                ))}
            </div>
        </motion.div>
    );
}

interface CompetitionCalendarProps {
    staggerProgress: MotionValue<number>;
}

export default function CompetitionCalendar({ staggerProgress }: CompetitionCalendarProps) {
    const total = categories.length;

    return (
        <>
            {/* Desktop: grid */}
            <div className="hidden h-full grid-cols-5 gap-3 overflow-hidden p-4 lg:grid">
                {categories.map((category, index) => {
                    const start = index / total;
                    const end = (index + 1) / total;
                    const catProgress = useTransform(staggerProgress, [start, end], [0, 1]);
                    return <AnimatedCategoryCard key={category.name} category={category} progress={catProgress} />;
                })}
            </div>

            {/* Mobile: carousels */}
            <div className="flex h-full flex-col justify-center gap-4 overflow-hidden px-4 py-6 lg:hidden">
                {categories.map((category, index) => {
                    const start = index / total;
                    const end = (index + 1) / total;
                    const catProgress = useTransform(staggerProgress, [start, end], [0, 1]);
                    return <AnimatedCategoryCarousel key={category.name} category={category} progress={catProgress} />;
                })}
            </div>
        </>
    );
}
