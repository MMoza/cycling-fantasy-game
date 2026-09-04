import { useState, useEffect } from 'react';
import { motion, MotionValue, useTransform } from 'framer-motion';
import { ChevronRight, ArrowRight } from 'lucide-react';
import { FlagIcon } from '@/components/ui/flag-icon';
import { getCalendarCategories, CalendarCategory, CalendarRace, UciColor } from '@/services/competitionCalendarService';

const UCI_COLORS: Record<UciColor, { bg: string; gradient: string; text: string; solid: string; solidText: string; border: string }> = {
    blue: { bg: 'bg-blue-500/10', gradient: 'from-blue-500/10', text: 'text-blue-500', solid: 'bg-blue-500', solidText: 'text-white', border: 'border-l-blue-500' },
    red: { bg: 'bg-red-500/10', gradient: 'from-red-500/10', text: 'text-red-500', solid: 'bg-red-500', solidText: 'text-white', border: 'border-l-red-500' },
    black: { bg: 'bg-neutral-500/10', gradient: 'from-neutral-500/10', text: 'text-neutral-400', solid: 'bg-neutral-800', solidText: 'text-white', border: 'border-l-neutral-800' },
    yellow: { bg: 'bg-yellow-500/10', gradient: 'from-yellow-500/10', text: 'text-yellow-500', solid: 'bg-yellow-500', solidText: 'text-black', border: 'border-l-yellow-500' },
    green: { bg: 'bg-green-500/10', gradient: 'from-green-500/10', text: 'text-green-500', solid: 'bg-green-500', solidText: 'text-white', border: 'border-l-green-500' },
};

function formatDates(start: string, end: string): string {
    const s = new Date(start + 'T00:00:00');
    const e = new Date(end + 'T00:00:00');
    const months = ['ene', 'feb', 'mar', 'abr', 'may', 'jun', 'jul', 'ago', 'sep', 'oct', 'nov', 'dic'];

    if (start === end) {
        return `${s.getDate()} ${months[s.getMonth()]} ${s.getFullYear()}`;
    }
    if (s.getMonth() === e.getMonth()) {
        return `${s.getDate()}–${e.getDate()} ${months[s.getMonth()]}`;
    }
    return `${s.getDate()} ${months[s.getMonth()]} – ${e.getDate()} ${months[e.getMonth()]}`;
}

function RaceCard({ race, color }: { race: CalendarRace; color: UciColor }) {
    const [hovered, setHovered] = useState(false);
    const colors = UCI_COLORS[color];

    const frontBg = hovered ? colors.solid : 'bg-background/80';
    const frontText = hovered ? colors.solidText : 'text-foreground';
    const frontBorder = hovered ? 'border-transparent' : 'border-border/40';
    const rotate = hovered ? 'rotateY(180deg)' : 'rotateY(0deg)';

    return (
        <div
            className="shrink-0 [perspective:600px]"
            onMouseEnter={() => setHovered(true)}
            onMouseLeave={() => setHovered(false)}
            onClick={() => setHovered((prev) => !prev)}
        >
            {/* Mobile card */}
            <div
                className="relative h-10 w-[140px] lg:hidden [transform-style:preserve-3d] transition-transform duration-500"
                style={{ transform: rotate }}
            >
                <div className={`absolute inset-0 flex items-center gap-1.5 border px-2.5 text-[11px] font-medium [backface-visibility:hidden] transition-colors duration-300 ${frontBg} ${frontText} ${frontBorder}`}>
                    <FlagIcon code={race.countryId} className="h-3 w-4 shrink-0" />
                    <span className="truncate flex-1">{race.name}</span>
                    <ChevronRight className="h-3 w-3 shrink-0 opacity-50" />
                </div>
                <div className={`absolute inset-0 flex items-center justify-center border border-transparent [backface-visibility:hidden] [transform:rotateY(180deg)] ${colors.solid} ${colors.solidText}`}>
                    <span className="text-xs font-bold tracking-wide">VER MÁS</span>
                    <ArrowRight className={`ml-1 h-3 w-3 transition-transform duration-500 ${hovered ? 'translate-x-1' : ''}`} />
                </div>
            </div>

            {/* Desktop card */}
            <div
                className="hidden h-[80px] w-[200px] lg:block [transform-style:preserve-3d] transition-transform duration-500"
                style={{ transform: rotate }}
            >
                <div className={`absolute inset-0 flex flex-col justify-end border p-3 backdrop-blur-sm [backface-visibility:hidden] transition-colors duration-300 ${frontBg} ${frontText} ${frontBorder}`}>
                    <h5 className="text-sm font-semibold leading-tight">{race.name}</h5>
                    <div className="mt-1.5 flex items-center gap-2 text-[10px] opacity-60">
                        <FlagIcon code={race.countryId} className="h-3 w-4" />
                        <span>{formatDates(race.startDate, race.endDate)}</span>
                    </div>
                </div>
                <div className={`absolute inset-0 flex flex-col items-center justify-center border border-transparent [backface-visibility:hidden] [transform:rotateY(180deg)] ${colors.solid} ${colors.solidText}`}>
                    <div className="mb-1.5 flex h-8 w-8 items-center justify-center rounded-full border-2 border-white/30 bg-white/10">
                        <span className="text-[8px] font-bold opacity-70">Logo</span>
                    </div>
                    <div className="flex items-center">
                        <span className="text-[11px] font-bold tracking-wide">VER MÁS</span>
                        <ArrowRight className={`ml-1 h-3 w-3 transition-transform duration-500 ${hovered ? 'translate-x-1' : ''}`} />
                    </div>
                </div>
            </div>
        </div>
    );
}

function CategoryRow({
    category,
    index,
    total,
    staggerProgress,
}: {
    category: CalendarCategory;
    index: number;
    total: number;
    staggerProgress: MotionValue<number>;
}) {
    const start = index / total;
    const end = (index + 1) / total;
    const progress = useTransform(staggerProgress, [start, end], [0, 1]);
    const opacity = useTransform(progress, [0, 1], [0, 1]);
    const y = useTransform(progress, [0, 1], [20, 0]);

    const colors = UCI_COLORS[category.color];

    return (
        <motion.div style={{ opacity, y }} className="w-full min-w-0">
            <h4 className={`mb-1 px-4 text-xs font-bold uppercase tracking-wider ${colors.text}`}>{category.name}</h4>
            <div className="relative w-full min-w-0">
                <div className={`pointer-events-none absolute left-0 top-0 z-10 h-full w-12 bg-gradient-to-r ${colors.gradient} to-transparent`} />
                <div className={`flex w-full min-w-0 gap-2 overflow-x-auto scroll-smooth border-l-4 py-2 ${colors.bg} ${colors.border} scrollbar-hide lg:gap-3 lg:py-3`} style={{ scrollbarWidth: 'none' }}>
                    {category.races.map((race) => (
                        <RaceCard key={race.name} race={race} color={category.color} />
                    ))}
                </div>
                <div className={`pointer-events-none absolute right-0 top-0 z-10 h-full w-12 bg-gradient-to-l ${colors.gradient} to-transparent`} />
            </div>
        </motion.div>
    );
}

interface CompetitionCalendarProps {
    staggerProgress: MotionValue<number>;
}

export default function CompetitionCalendar({ staggerProgress }: CompetitionCalendarProps) {
    const [categories, setCategories] = useState<CalendarCategory[]>([]);

    useEffect(() => {
        getCalendarCategories().then(setCategories);
    }, []);

    const total = categories.length;

    if (total === 0) return null;

    return (
        <div className="flex h-full w-full min-w-0 flex-col justify-center gap-3 py-4 lg:gap-4">
            {categories.map((category, index) => (
                <CategoryRow
                    key={category.name}
                    category={category}
                    index={index}
                    total={total}
                    staggerProgress={staggerProgress}
                />
            ))}
        </div>
    );
}
