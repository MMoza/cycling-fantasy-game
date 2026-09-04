import { Trophy, ChevronUp, ChevronDown, Minus } from 'lucide-react';
import { MotionValue, motion } from 'framer-motion';
import { FlagIcon } from '@/components/ui/flag-icon';
import PedalesLogo from '@/components/PedalesLogo';
import { ClassificationCompetition, ClassificationEntry } from '@/services/classificationMockData';

function isLightColor(hex: string): boolean {
    const c = hex.replace('#', '');
    const r = parseInt(c.substring(0, 2), 16);
    const g = parseInt(c.substring(2, 4), 16);
    const b = parseInt(c.substring(4, 6), 16);
    return (r * 299 + g * 587 + b * 114) / 1000 > 150;
}

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
    if (change === null) {
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

function LeaderboardRow({ entry }: { entry: ClassificationEntry }) {
    return (
        <div className="flex items-center gap-2 px-3 py-[7px] transition-colors hover:bg-muted/30">
            <RankBadge rank={entry.rank} />
            <Avatar name={entry.userName} rank={entry.rank} />
            <span className="flex-1 text-[13px] font-medium text-foreground truncate">{entry.userName}</span>
            <RankChange change={entry.rankChange} />
            <span className="text-[13px] tabular-nums text-muted-foreground">{entry.points}</span>
        </div>
    );
}

export default function ClassificationCard({
    competition,
    taglineOpacity,
}: {
    competition: ClassificationCompetition;
    taglineOpacity: MotionValue<number>;
}) {
    const light = isLightColor(competition.brandColor);
    const headerText = light ? 'text-foreground' : 'text-white';
    const headerSubtext = light ? 'text-foreground/60' : 'text-white/60';

    return (
        <div className="flex h-full w-full flex-col border border-border/40 bg-background/80 backdrop-blur-sm overflow-hidden">
            {/* Header */}
            <div
                className={`flex items-center gap-3 rounded-t-lg px-4 py-4 ${headerText}`}
                style={{ backgroundColor: competition.brandColor }}
            >
                <div className="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-white">
                    <PedalesLogo className="h-8 w-8" />
                </div>
                <FlagIcon code={competition.countryId} className="h-6 w-8" />
                <h4 className="text-lg font-black tracking-tight">{competition.competition}</h4>
                <span className={`text-sm font-medium ${headerSubtext}`}>{competition.year}</span>
            </div>

            {/* Leaderboard */}
            <div className="divide-y divide-border/15">
                {competition.leaderboard.map((entry) => (
                    <LeaderboardRow key={entry.rank} entry={entry} />
                ))}
            </div>

            {/* Tagline */}
            <motion.div
                className="flex-1 border-t border-border/20 px-4 py-3 flex items-center justify-center"
                style={{ opacity: taglineOpacity }}
            >
                <p className="text-xl font-bold italic text-center" style={{ color: competition.brandColor }}>
                    {competition.tagline}
                </p>
            </motion.div>
        </div>
    );
}
