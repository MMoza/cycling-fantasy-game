import { cn } from '@/lib/utils';

interface StageTypeIconProps {
    type: string;
    className?: string;
}

export function StageTypeIcon({ type, className }: StageTypeIconProps) {
    return (
        <span className={cn('inline-flex items-center justify-center', className)}>
            {type === 'time_trial' && <TimeTrialIcon />}
            {type === 'team_time_trial' && <TeamTimeTrialIcon />}
            {type === 'flat' && <FlatIcon />}
            {type === 'mountain' && <MountainIcon />}
            {type === 'high_mountain' && <HighMountainIcon />}
            {type === 'hill' && <HillIcon />}
            {type === 'rest' && <RestIcon />}
        </span>
    );
}

function TimeTrialIcon() {
    return (
        <svg viewBox="0 0 22 22" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" className="h-5 w-5">
            <circle cx="11" cy="11" r="9" />
            <polyline points="11 6 11 11 14 14" />
            <line x1="11" y1="1" x2="11" y2="3" />
        </svg>
    );
}

function TeamTimeTrialIcon() {
    return (
        <svg viewBox="0 0 22 22" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" className="h-5 w-5">
            <circle cx="11" cy="12" r="9" />
            <polyline points="11 7 11 12 14 15" />
            <line x1="11" y1="2" x2="11" y2="4" />
            <text x="11" y="5" textAnchor="middle" fontSize="6" fill="currentColor" stroke="none" fontWeight="700">E</text>
        </svg>
    );
}

function FlatIcon() {
    return (
        <svg viewBox="0 0 24 16" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" className="h-5 w-5">
            <line x1="2" y1="12" x2="22" y2="12" />
            <line x1="6" y1="9" x2="18" y2="9" strokeWidth="1.5" strokeDasharray="1 3" />
        </svg>
    );
}

function MountainIcon() {
    return (
        <svg viewBox="0 0 24 18" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" className="h-5 w-5">
            <polyline points="2 16 12 2 22 16" />
        </svg>
    );
}

function HighMountainIcon() {
    return (
        <svg viewBox="0 0 24 18" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" className="h-5 w-5">
            <polyline points="2 16 7 5 12 14 17 2 22 16" />
        </svg>
    );
}

function HillIcon() {
    return (
        <svg viewBox="0 0 24 16" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" className="h-5 w-5">
            <polyline points="2 12 8 7 14 10 22 4" />
        </svg>
    );
}

function RestIcon() {
    return (
        <svg viewBox="0 0 24 16" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" className="h-5 w-5">
            <line x1="4" y1="4" x2="20" y2="4" />
            <line x1="4" y1="8" x2="20" y2="8" />
            <line x1="4" y1="12" x2="14" y2="12" />
            <circle cx="19" cy="12" r="2" />
            <path d="M19 10.5V12l1 0.8" />
        </svg>
    );
}
