import { useEffect, useState } from 'react';

function formatDiff(ms: number): string {
    if (ms <= 0) return '00:00:00';
    const totalSeconds = Math.floor(ms / 1000);
    const hours = Math.floor(totalSeconds / 3600);
    const minutes = Math.floor((totalSeconds % 3600) / 60);
    const seconds = totalSeconds % 60;
    return `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
}

export function Countdown({ scheduledStart, onExpired }: { scheduledStart: string; onExpired?: () => void }) {
    const [diff, setDiff] = useState(0);

    useEffect(() => {
        const update = () => {
            const d = new Date(scheduledStart).getTime() - Date.now();
            setDiff(d);
            if (d <= 0) onExpired?.();
        };
        update();
        const id = setInterval(update, 1000);
        return () => clearInterval(id);
    }, [scheduledStart, onExpired]);

    return (
        <span className="font-mono text-xs font-bold tabular-nums tracking-wider">
            {formatDiff(diff)}
        </span>
    );
}
