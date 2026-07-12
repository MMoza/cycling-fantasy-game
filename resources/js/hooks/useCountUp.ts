import { useEffect, useRef, useState } from 'react';

export function useCountUp(end: number, duration: number = 2000): number {
    const [value, setValue] = useState(0);
    const startTime = useRef<number | null>(null);
    const rafId = useRef<number>(0);

    useEffect(() => {
        if (end === 0) {
            setValue(0);
            return;
        }

        startTime.current = null;

        const easeOut = (t: number): number => 1 - Math.pow(1 - t, 3);

        const animate = (timestamp: number) => {
            if (startTime.current === null) {
                startTime.current = timestamp;
            }

            const elapsed = timestamp - startTime.current;
            const progress = Math.min(elapsed / duration, 1);
            const easedProgress = easeOut(progress);

            setValue(Math.round(easedProgress * end));

            if (progress < 1) {
                rafId.current = requestAnimationFrame(animate);
            }
        };

        rafId.current = requestAnimationFrame(animate);

        return () => cancelAnimationFrame(rafId.current);
    }, [end, duration]);

    return value;
}
