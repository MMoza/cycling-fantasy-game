import { MotionValue, useTransform, motion } from 'framer-motion';
import ClassificationCard from './ClassificationCard';
import { MOCK_CLASSIFICATIONS, ClassificationCompetition } from '@/services/classificationMockData';

function AnimatedCard({
    competition,
    index,
    total,
    staggerProgress,
}: {
    competition: ClassificationCompetition;
    index: number;
    total: number;
    staggerProgress: MotionValue<number>;
}) {
    const segmentSize = 1 / total;
    const segmentStart = index * segmentSize;
    const segmentMid = segmentStart + segmentSize / 2;
    const segmentEnd = segmentStart + segmentSize;

    const x = useTransform(staggerProgress, [segmentStart, segmentMid, segmentEnd], ['55%', '0%', '-55%']);
    const scale = useTransform(staggerProgress, [segmentStart, segmentMid, segmentEnd], [0.88, 1, 0.88]);
    const opacity = useTransform(staggerProgress, [segmentStart, segmentMid, segmentEnd], [0.4, 1, 0.4]);
    const blur = useTransform(staggerProgress, [segmentStart, segmentMid, segmentEnd], [2, 0, 2]);
    const zIndex = useTransform(staggerProgress, [segmentStart, segmentMid, segmentEnd], [0, 10, 0]);

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
                <ClassificationCard competition={competition} />
            </motion.div>
        </motion.div>
    );
}

interface ClassificationCarouselProps {
    staggerProgress: MotionValue<number>;
}

export default function ClassificationCarousel({ staggerProgress }: ClassificationCarouselProps) {
    const total = MOCK_CLASSIFICATIONS.length;

    return (
        <div className="relative h-full w-full overflow-hidden">
            {MOCK_CLASSIFICATIONS.map((competition, index) => (
                <AnimatedCard
                    key={competition.competition}
                    competition={competition}
                    index={index}
                    total={total}
                    staggerProgress={staggerProgress}
                />
            ))}
        </div>
    );
}
