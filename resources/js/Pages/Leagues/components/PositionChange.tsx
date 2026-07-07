import { ChevronUp, ChevronDown, Minus } from 'lucide-react';

export function PositionChange({ change }: { change: number | null }) {
    if (change === null || change === 0) {
        return <Minus className="h-3 w-3 text-muted-foreground/40" />;
    }
    if (change > 0) {
        return (
            <span className="flex items-center gap-0.5 text-xs font-medium text-green-600">
                <ChevronUp className="h-3 w-3" />
                {change}
            </span>
        );
    }
    return (
        <span className="flex items-center gap-0.5 text-xs font-medium text-red-500">
            <ChevronDown className="h-3 w-3" />
            {Math.abs(change)}
        </span>
    );
}
