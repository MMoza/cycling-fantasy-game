import { useEffect, useState } from 'react';

interface CountdownProps {
    targetDate: string;
    className?: string;
}

interface TimeLeft {
    days: number;
    hours: number;
    minutes: number;
    seconds: number;
}

function calculateTimeLeft(targetDate: string): TimeLeft {
    const diff = new Date(targetDate).getTime() - Date.now();

    if (diff <= 0) {
        return { days: 0, hours: 0, minutes: 0, seconds: 0 };
    }

    const totalSeconds = Math.floor(diff / 1000);

    return {
        days: Math.floor(totalSeconds / 86400),
        hours: Math.floor((totalSeconds % 86400) / 3600),
        minutes: Math.floor((totalSeconds % 3600) / 60),
        seconds: totalSeconds % 60,
    };
}

export default function Countdown({ targetDate, className = '' }: CountdownProps) {
    const [timeLeft, setTimeLeft] = useState<TimeLeft>(() => calculateTimeLeft(targetDate));

    useEffect(() => {
        const id = setInterval(() => {
            setTimeLeft(calculateTimeLeft(targetDate));
        }, 1000);

        return () => clearInterval(id);
    }, [targetDate]);

    const parts = [
        { value: timeLeft.days, label: 'd' },
        { value: timeLeft.hours, label: 'h' },
        { value: timeLeft.minutes, label: 'm' },
        { value: timeLeft.seconds, label: 's' },
    ];

    return (
        <span className={`font-mono text-sm font-bold tabular-nums tracking-wider ${className}`}>
            {parts
                .filter((p) => p.value > 0 || p.label === 'h' || p.label === 'm')
                .map((p) => `${p.value}${p.label}`)
                .join(' ')}
        </span>
    );
}
