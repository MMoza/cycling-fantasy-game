import { useState, useEffect } from 'react';
import { motion, MotionValue, useTransform, AnimatePresence } from 'framer-motion';
import { Trophy } from 'lucide-react';
import { MOCK_SEASON, SeasonSnapshot } from '@/services/seasonMockData';

const TROPHY_COLORS = ['text-yellow-500', 'text-neutral-400', 'text-amber-700'];

const RANK_BG = [
    'bg-yellow-500/15 text-yellow-600',
    'bg-neutral-400/15 text-neutral-500',
    'bg-amber-700/15 text-amber-700',
    'bg-muted text-muted-foreground',
    'bg-muted text-muted-foreground',
];

function getInitials(name: string): string {
    return name
        .split(/[_\s]/)
        .map((w) => w[0])
        .join('')
        .toUpperCase()
        .slice(0, 2);
}

function Avatar({ name, rank }: { name: string; rank: number }) {
    const bg = rank <= 3 ? RANK_BG[rank - 1] : RANK_BG[3];
    return (
        <div className={`flex h-7 w-7 shrink-0 items-center justify-center rounded-full text-[10px] font-bold ${bg}`}>
            {getInitials(name)}
        </div>
    );
}

function RankBadge({ rank }: { rank: number }) {
    if (rank <= 3) {
        return <Trophy className={`h-3.5 w-3.5 ${TROPHY_COLORS[rank - 1]}`} />;
    }
    return <span className="w-3.5 text-center text-[11px] font-bold text-muted-foreground">{rank}</span>;
}

function LeaderboardRow({ entry, index }: { entry: { rank: number; userName: string; points: number }; index: number }) {
    return (
        <motion.div
            className="flex items-center gap-2 px-3 py-[7px] transition-colors hover:bg-muted/30"
            initial={{ opacity: 0, y: 10 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ delay: index * 0.05, duration: 0.3 }}
        >
            <RankBadge rank={entry.rank} />
            <Avatar name={entry.userName} rank={entry.rank} />
            <span className="flex-1 text-[13px] font-medium text-foreground truncate">{entry.userName}</span>
            <span className="text-[13px] tabular-nums text-muted-foreground">{entry.points}</span>
        </motion.div>
    );
}

interface SeasonCarouselProps {
    staggerProgress: MotionValue<number>;
}

export default function SeasonCarousel({ staggerProgress }: SeasonCarouselProps) {
    const [currentSnapshot, setCurrentSnapshot] = useState(0);
    const total = MOCK_SEASON.length;

    useEffect(() => {
        const unsub = staggerProgress.on('change', (v) => {
            const snapshotIndex = Math.min(Math.floor(v * total), total - 1);
            setCurrentSnapshot(snapshotIndex);
        });
        return unsub;
    }, [staggerProgress, total]);

    const snapshot = MOCK_SEASON[currentSnapshot];

    return (
        <div className="flex h-full w-full flex-col">
            {/* Leaderboard */}
            <div className="flex-1 flex flex-col border border-border/40 bg-background/80 backdrop-blur-sm overflow-hidden">
                {/* Header */}
                <div className="flex items-center gap-3 rounded-t-lg px-4 py-4 bg-accent-500 text-white">
                    <div className="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-white">
                        <span className="text-lg font-black text-accent-500">P</span>
                    </div>
                    <h4 className="text-lg font-black tracking-tight">Clasificación General</h4>
                    <span className="text-sm font-medium text-white/60">2026</span>
                </div>

                {/* Leaderboard rows */}
                <div className="flex-1 divide-y divide-border/15">
                    <AnimatePresence mode="wait">
                        {snapshot.leaderboard.map((entry, i) => (
                            <LeaderboardRow key={`${currentSnapshot}-${entry.userName}`} entry={entry} index={i} />
                        ))}
                    </AnimatePresence>
                </div>
            </div>

            {/* Scroll points */}
            <div className="flex items-center justify-center gap-3 py-4">
                {MOCK_SEASON.map((s, i) => (
                    <motion.div
                        key={s.label}
                        className="flex flex-col items-center gap-1"
                        animate={{
                            scale: i === currentSnapshot ? 1.1 : 0.9,
                            opacity: i === currentSnapshot ? 1 : 0.5,
                        }}
                    >
                        <div
                            className={`h-3 w-3 rounded-full transition-colors ${
                                i === currentSnapshot ? 'bg-accent-500' : 'bg-muted-foreground/30'
                            }`}
                        />
                        <span className="text-[10px] text-muted-foreground whitespace-nowrap">{s.label}</span>
                    </motion.div>
                ))}
            </div>
        </div>
    );
}
