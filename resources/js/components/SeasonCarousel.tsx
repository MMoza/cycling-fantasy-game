import { useState, useEffect } from 'react';
import { motion, MotionValue, AnimatePresence } from 'framer-motion';
import { Trophy, ChevronUp, ChevronDown, Minus, Crown } from 'lucide-react';
import PedalesLogo from '@/components/PedalesLogo';
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

function RankChange({ change }: { change: number | null }) {
    if (change === null || change === 0) {
        return <Minus className="h-3 w-3 text-muted-foreground/40" />;
    }
    if (change > 0) {
        return (
            <span className="flex items-center text-[10px] font-bold text-green-600">
                <ChevronUp className="h-3 w-3" />
                {change}
            </span>
        );
    }
    return (
        <span className="flex items-center text-[10px] font-bold text-red-500">
            <ChevronDown className="h-3 w-3" />
            {Math.abs(change)}
        </span>
    );
}

function LeaderboardRow({
    entry,
    index,
    isChampion,
}: {
    entry: { rank: number; userName: string; points: number; rankChange: number | null };
    index: number;
    isChampion: boolean;
}) {
    return (
        <motion.div
            className={`flex items-center gap-2 px-3 py-[7px] transition-colors ${
                isChampion ? 'bg-yellow-500/10' : 'hover:bg-muted/30'
            }`}
            initial={{ opacity: 0, y: 10 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ delay: index * 0.05, duration: 0.3 }}
        >
            <RankBadge rank={entry.rank} />
            <Avatar name={entry.userName} rank={entry.rank} />
            <span className={`flex-1 text-[13px] font-medium truncate ${isChampion ? 'text-yellow-600 font-bold' : 'text-foreground'}`}>
                {entry.userName}
                {isChampion && <Crown className="inline h-3 w-3 ml-1 text-yellow-500" />}
            </span>
            <RankChange change={entry.rankChange} />
            <span className={`text-[13px] tabular-nums ${isChampion ? 'text-yellow-600 font-bold' : 'text-muted-foreground'}`}>
                {entry.points}
            </span>
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
    const isFinal = currentSnapshot === total - 1;
    const champion = snapshot.leaderboard[0];

    return (
        <div className="flex h-full w-full flex-col items-center">
            {/* Leaderboard */}
            <div className="w-full max-w-md flex flex-col border border-border/40 bg-background/80 backdrop-blur-sm overflow-hidden">
                {/* Header */}
                <div className="flex items-center gap-3 rounded-t-lg px-4 py-4 bg-accent-500 text-white">
                    <div className="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-white">
                        <PedalesLogo className="h-8 w-8" />
                    </div>
                    <h4 className="text-lg font-black tracking-tight">Clasificación General</h4>
                    <span className="text-sm font-medium text-white/60">2026</span>
                </div>

                {/* Leaderboard rows */}
                <div className="flex-1 divide-y divide-border/15">
                    <AnimatePresence mode="wait">
                        {snapshot.leaderboard.map((entry, i) => (
                            <LeaderboardRow
                                key={`${currentSnapshot}-${entry.userName}`}
                                entry={entry}
                                index={i}
                                isChampion={isFinal && i === 0}
                            />
                        ))}
                    </AnimatePresence>
                </div>

                {/* Champion banner */}
                {isFinal && (
                    <motion.div
                        initial={{ opacity: 0, height: 0 }}
                        animate={{ opacity: 1, height: 'auto' }}
                        className="bg-yellow-500/10 border-t border-yellow-500/30 px-4 py-3 text-center"
                    >
                        <p className="text-sm font-bold text-yellow-600">
                            <Crown className="inline h-4 w-4 mr-1" />
                            ¡{champion.userName} es el campeón de la temporada!
                        </p>
                    </motion.div>
                )}
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
