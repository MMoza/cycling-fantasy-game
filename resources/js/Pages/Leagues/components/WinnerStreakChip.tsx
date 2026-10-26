/**
 * Chip de racha de victorias consecutivas. Solo se muestra con 2 o más.
 */
export function WinnerStreakChip({ streak }: { streak?: number }) {
    if ((streak ?? 0) < 2) {
        return null;
    }

    return (
        <span
            className="inline-flex shrink-0 items-center gap-0.5 rounded-full bg-orange-50 px-1.5 py-0.5 text-[11px] font-bold text-orange-700 ring-1 ring-orange-200/70 dark:bg-orange-950/30 dark:text-orange-300 dark:ring-orange-900/50"
            title={`Racha de ${streak} victorias consecutivas`}
        >
            <svg className="h-3 w-3 animate-pulse text-orange-600 dark:text-orange-400" viewBox="0 0 24 24" fill="currentColor">
                <path d="M12 23c-3.6 0-8-2.4-8-7.7C4 10 12 1 12 1s8 9 8 14.3c0 5.3-4.4 7.7-8 7.7zm0-18.5C9.5 7.5 6 12.6 6 15.3 6 19 8.7 21 12 21s6-2 6-5.7c0-2.7-3.5-7.8-6-10.5z" />
                <path d="M12 21c-1.7 0-3-1-3-2.7 0-1.4 1.5-3.3 3-5.2 1.5 1.9 3 3.8 3 5.2 0 1.7-1.3 2.7-3 2.7z" />
            </svg>
            {streak}
        </span>
    );
}
