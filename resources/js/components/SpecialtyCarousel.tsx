import { MotionValue, useTransform, motion } from 'framer-motion';
import ClassificationCard from './ClassificationCard';
import { MOCK_SPECIALTIES, SpecialtyCategory } from '@/services/specialtyMockData';

function AnimatedCard({
    category,
    index,
    total,
    staggerProgress,
}: {
    category: SpecialtyCategory;
    index: number;
    total: number;
    staggerProgress: MotionValue<number>;
}) {
    const segmentSize = 1 / total;
    const segmentStart = index * segmentSize;
    const arrive = segmentStart + segmentSize * 0.25;
    const holdEnd = segmentStart + segmentSize * 0.65;
    const segmentEnd = segmentStart + segmentSize;

    const x = useTransform(staggerProgress, [segmentStart, arrive, holdEnd, segmentEnd], ['55%', '0%', '0%', '-55%']);
    const scale = useTransform(staggerProgress, [segmentStart, arrive, holdEnd, segmentEnd], [0.88, 1, 1, 0.88]);
    const opacity = useTransform(staggerProgress, [segmentStart, arrive, holdEnd, segmentEnd], [0.3, 1, 1, 0.3]);
    const blur = useTransform(staggerProgress, [segmentStart, arrive, holdEnd, segmentEnd], [2, 0, 0, 2]);
    const zIndex = useTransform(staggerProgress, [segmentStart, arrive, holdEnd, segmentEnd], [0, 10, 10, 0]);
    const taglineOpacity = useTransform(staggerProgress, [arrive, arrive + 0.02, holdEnd - 0.02, holdEnd], [0, 1, 1, 0]);

    const competitionData = {
        competition: category.name,
        year: 2026,
        countryId: 'UCI',
        brandColor: category.uciColor,
        tagline: category.tagline,
        leaderboard: category.leaderboard,
    };

    return (
        <motion.div
            className="absolute inset-0 flex items-center justify-center"
            style={{ x, scale, opacity, zIndex }}
        >
            <motion.div
                className="h-full w-full max-w-md"
                style={{
                    filter: useTransform(blur, (v) => `blur(${v}px)`),
                }}
            >
                <ClassificationCard competition={competitionData} taglineOpacity={taglineOpacity} />
            </motion.div>
        </motion.div>
    );
}

interface SpecialtyCarouselProps {
    staggerProgress: MotionValue<number>;
}

export default function SpecialtyCarousel({ staggerProgress }: SpecialtyCarouselProps) {
    const total = MOCK_SPECIALTIES.length;

    return (
        <div className="relative h-full w-full overflow-hidden">
            {MOCK_SPECIALTIES.map((category, index) => (
                <AnimatedCard
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
