import type { MouseEvent } from 'react';
import { Menu, MenuButton, MenuItem, MenuItems } from '@headlessui/react';
import { Check, ChevronDown, ChevronLeft, ChevronRight, Settings, ShieldCheck, Trophy } from 'lucide-react';
import { Badge } from '@/components/ui/badge';
import type { League, SeasonLeague } from './types';

interface LeagueSwitcherProps {
    league: League;
    seasonLeagues: SeasonLeague[];
    activeLeagueId: string;
    onSwitch: (leagueId: string) => void;
    onSettingsOpen: () => void;
}

/**
 * Mobile sticky header with a dropdown listing every league of the season
 * the user belongs to. Clicking the competition opens the dropdown anchored
 * right below the header.
 */
export function LeagueSwitcher({ league, seasonLeagues, activeLeagueId, onSwitch, onSettingsOpen }: LeagueSwitcherProps) {
    const hasOptions = seasonLeagues.length > 1;

    const handleMenuClick = (event: MouseEvent<HTMLDivElement>) => {
        const trigger = (event.target as HTMLElement).closest('[data-league-id]');
        const leagueId = trigger?.getAttribute('data-league-id');

        if (leagueId) {
            onSwitch(leagueId);
        }
    };

    return (
        <div className="sticky top-[88px] z-40 bg-background/95 backdrop-blur md:hidden">
            <div className="flex items-center gap-2 px-4 py-2">
                <Menu as="div" className="relative min-w-0 flex-1">
                    <MenuButton className="flex w-full items-center gap-2 rounded-md text-left transition-colors hover:bg-muted/60 focus:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-1 focus-visible:ring-offset-background">
                        <div className="flex h-8 w-8 shrink-0 items-center justify-center overflow-hidden rounded-full border border-border bg-muted">
                            {league.competition.logoImageUrl ? (
                                <img src={league.competition.logoImageUrl} alt="" className="h-full w-full object-cover bg-black" />
                            ) : (
                                <Trophy className="h-4 w-4 text-muted-foreground" />
                            )}
                        </div>
                        <div className="min-w-0 flex-1">
                            <div className="flex items-center gap-1.5">
                                <span className="truncate text-sm font-semibold">
                                    {league.competition.name} {league.competition.year}
                                </span>
                                {league.is_official && (
                                    <Badge variant="default" className="gap-0.5 rounded-full bg-brand-600 text-white text-[9px] h-4 px-1.5 border-0">
                                        <ShieldCheck className="h-2 w-2" />
                                        Oficial
                                    </Badge>
                                )}
                            </div>
                            <span className="block truncate text-xs text-muted-foreground">{league.name}</span>
                        </div>
                        {hasOptions && <ChevronDown className="h-4 w-4 shrink-0 text-muted-foreground" />}
                    </MenuButton>

                    <MenuItems
                        transition
                        onClick={handleMenuClick}
                        className="absolute left-0 right-0 top-full z-50 mt-[15px] origin-top rounded-xl border bg-popover p-1.5 shadow-lg outline-none transition duration-100 ease-out data-[closed]:scale-95 data-[closed]:opacity-0"
                    >
                        <div className="px-3 py-2">
                            <p className="text-xs font-medium text-muted-foreground">Tus ligas de la temporada</p>
                        </div>

                        <div className="max-h-[60vh] space-y-0.5 overflow-y-auto">
                            {seasonLeagues.map((item) => (
                                <MenuItem key={item.id}>
                                    <button
                                        type="button"
                                        data-league-id={item.id}
                                        className="flex w-full items-center gap-3 rounded-lg px-3 py-2 text-left text-sm transition-colors hover:bg-accent hover:text-accent-foreground data-[focus]:bg-accent data-[focus]:text-accent-foreground"
                                    >
                                        <span className="flex h-8 w-8 shrink-0 items-center justify-center overflow-hidden rounded-full border border-border bg-muted">
                                            {item.competition.logoImageUrl ? (
                                                <img src={item.competition.logoImageUrl} alt="" className="h-full w-full object-cover bg-black" />
                                            ) : (
                                                <Trophy className="h-4 w-4 text-muted-foreground" />
                                            )}
                                        </span>
                                        <span className="min-w-0 flex-1">
                                            <span className="block truncate text-sm font-medium">
                                                {item.competition.name} {item.year}
                                            </span>
                                            <span className="block truncate text-xs text-muted-foreground">{item.name}</span>
                                        </span>
                                        {item.is_official && (
                                            <Badge variant="default" className="shrink-0 rounded-full bg-brand-600 text-white text-[9px] h-4 px-1.5 border-0">
                                                Oficial
                                            </Badge>
                                        )}
                                        {item.id === activeLeagueId && (
                                            <Check className="h-4 w-4 shrink-0 text-brand-600" aria-hidden="true" />
                                        )}
                                    </button>
                                </MenuItem>
                            ))}
                        </div>
                    </MenuItems>
                </Menu>

                <button
                    type="button"
                    onClick={onSettingsOpen}
                    className="shrink-0 rounded-md p-1.5 text-muted-foreground transition-colors hover:bg-muted hover:text-foreground"
                >
                    <Settings className="h-4 w-4" />
                </button>
            </div>
            <div className="uci-rainbow-stripe" />
        </div>
    );
}

interface LeagueNavigationArrowsProps {
    seasonLeagues: SeasonLeague[];
    activeLeagueId: string;
    onSwitch: (leagueId: string) => void;
    disabled?: boolean;
}

/**
 * Desktop navigation: chevrons on both sides of the league hero card plus a
 * "current / total" counter. Wraps around the season league list.
 */
export function LeagueNavigationArrows({ seasonLeagues, activeLeagueId, onSwitch, disabled = false }: LeagueNavigationArrowsProps) {
    const index = seasonLeagues.findIndex((item) => item.id === activeLeagueId);

    if (seasonLeagues.length < 2 || index === -1) {
        return null;
    }

    const goTo = (delta: number) => {
        const next = (index + delta + seasonLeagues.length) % seasonLeagues.length;
        onSwitch(seasonLeagues[next].id);
    };

    const arrowClass =
        'absolute top-1/2 z-20 flex h-10 w-10 -translate-y-1/2 items-center justify-center rounded-full bg-background/90 text-muted-foreground shadow-sm ring-1 ring-foreground/10 backdrop-blur transition-colors hover:bg-background hover:text-foreground focus:outline-none focus-visible:ring-2 focus-visible:ring-ring disabled:cursor-wait disabled:opacity-50';

    return (
        <>
            <button
                type="button"
                aria-label="Liga anterior"
                onClick={() => goTo(-1)}
                disabled={disabled}
                className={`${arrowClass} -left-4 hidden md:flex`}
            >
                <ChevronLeft className="h-5 w-5" />
            </button>

            <button
                type="button"
                aria-label="Liga siguiente"
                onClick={() => goTo(1)}
                disabled={disabled}
                className={`${arrowClass} -right-4 hidden md:flex`}
            >
                <ChevronRight className="h-5 w-5" />
            </button>

            <div className="pointer-events-none absolute inset-x-0 top-full mt-1.5 hidden justify-center md:flex">
                <span className="rounded-full bg-muted px-2.5 py-0.5 text-[11px] font-medium tabular-nums text-muted-foreground">
                    {index + 1} / {seasonLeagues.length}
                </span>
            </div>
        </>
    );
}
