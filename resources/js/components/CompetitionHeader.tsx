import { ShieldCheck, Trophy, Settings } from 'lucide-react';
import { Badge } from '@/components/ui/badge';

interface CompetitionHeaderProps {
    competitionName: string;
    year: number;
    leagueName: string;
    logoImageUrl?: string | null;
    isOfficial?: boolean;
    onSettingsClick?: () => void;
}

export function CompetitionHeader({ competitionName, year, leagueName, logoImageUrl, isOfficial, onSettingsClick }: CompetitionHeaderProps) {
    return (
        <div className="sticky top-[88px] z-40 bg-background/85 backdrop-blur md:hidden">
            <div className="flex items-center gap-2 px-4 py-2">
                <div className="flex h-8 w-8 shrink-0 items-center justify-center overflow-hidden rounded-full border border-border bg-muted">
                    {logoImageUrl ? (
                        <img src={logoImageUrl} alt="" className="h-full w-full object-cover" />
                    ) : (
                        <Trophy className="h-4 w-4 text-muted-foreground" />
                    )}
                </div>
                <div className="min-w-0 flex-1">
                    <div className="flex items-center gap-1.5">
                        <span className="truncate text-sm font-semibold">
                            {competitionName} {year}
                        </span>
                        {isOfficial && (
                            <Badge variant="default" className="gap-0.5 rounded-full bg-brand-600 text-white text-[9px] h-4 px-1.5 border-0">
                                <ShieldCheck className="h-2 w-2" />
                                Oficial
                            </Badge>
                        )}
                    </div>
                    <span className="block truncate text-xs text-muted-foreground">{leagueName}</span>
                </div>
                {onSettingsClick && (
                    <button
                        type="button"
                        onClick={onSettingsClick}
                        className="shrink-0 rounded-md p-1.5 text-muted-foreground transition-colors hover:bg-muted hover:text-foreground"
                    >
                        <Settings className="h-4 w-4" />
                    </button>
                )}
            </div>
            <div className="uci-rainbow-stripe" />
        </div>
    );
}
