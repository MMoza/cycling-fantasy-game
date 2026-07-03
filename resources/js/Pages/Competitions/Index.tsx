import AppLayout from '@/Layouts/AppLayout';
import { Head, Link, router } from '@inertiajs/react';
import { Card, CardContent } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Users, ChevronRight, ImageIcon } from 'lucide-react';
import { FlagIcon } from '@/components/ui/flag-icon';

interface CompetitionCard {
    id: string;
    name: string;
    type: string;
    typeLabel: string;
    country_id: string | null;
    country_name: string | null;
    cover_image_url: string | null;
    logo_image_url: string | null;
    official_league_id: string | null;
    official_league_name: string | null;
    edition_id: string;
    year: number;
    edition_status: string;
}

interface CompetitionGroup {
    type: string;
    typeLabel: string;
    competitions: CompetitionCard[];
}

interface YearGroup {
    year: number;
    groups: CompetitionGroup[];
}

interface CompProps {
    yearGroups: YearGroup[];
    years: number[];
    currentYear: number;
}

const typeOrder = ['gc', 'major', 'monument', 'classic', 'championship'];

const typeIcons: Record<string, string> = {
    gc: '🏔️',
    major: '⭐',
    monument: '🏛️',
    classic: '🚴',
    championship: '🏆',
};

export default function Index({ yearGroups, years, currentYear }: CompProps) {
    const yearGroup = yearGroups[0];

    const changeYear = (year: number) => {
        router.get(route('competitions.year', year));
    };

    const sortedGroups = [...(yearGroup?.groups ?? [])].sort(
        (a, b) => typeOrder.indexOf(a.type) - typeOrder.indexOf(b.type)
    );

    return (
        <AppLayout>
            <Head title="Competiciones" />

            <div className="space-y-6">
                <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h1 className="text-2xl font-semibold tracking-tight">Competiciones</h1>
                        <p className="text-sm text-muted-foreground">
                            Explora las competiciones activas y únete a la liga oficial
                        </p>
                    </div>

                    <div className="flex gap-1 overflow-x-auto">
                        {years.map((year) => (
                            <button
                                key={year}
                                type="button"
                                onClick={() => changeYear(year)}
                                className={`rounded-lg px-3 py-1.5 text-sm font-medium transition-colors ${
                                    year === currentYear
                                        ? 'bg-foreground text-background'
                                        : 'bg-muted text-muted-foreground hover:bg-accent hover:text-accent-foreground'
                                }`}
                            >
                                {year}
                            </button>
                        ))}
                    </div>
                </div>

                {sortedGroups.length === 0 ? (
                    <Card>
                        <CardContent className="flex flex-col items-center justify-center px-6 py-16 text-center">
                            <ImageIcon className="h-12 w-12 text-muted-foreground" />
                            <h3 className="mt-4 text-lg font-medium">Sin competiciones</h3>
                            <p className="mt-2 text-sm text-muted-foreground">
                                No hay competiciones activas para {currentYear}
                            </p>
                        </CardContent>
                    </Card>
                ) : (
                    sortedGroups.map((group) => (
                        <section key={group.type}>
                            <div className="mb-3 flex items-center gap-2">
                                <span className="text-lg">{typeIcons[group.type] ?? '🚴'}</span>
                                <h2 className="text-lg font-semibold">{group.typeLabel}</h2>
                                <span className="text-sm text-muted-foreground">
                                    ({group.competitions.length})
                                </span>
                            </div>

                            <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                                {group.competitions.map((comp) => (
                                    <Card key={comp.edition_id} className="overflow-hidden">
                                        <div className="relative flex h-36 items-end bg-muted">
                                            {comp.cover_image_url ? (
                                                <img
                                                    src={comp.cover_image_url}
                                                    alt=""
                                                    className="absolute inset-0 h-full w-full object-cover"
                                                />
                                            ) : (
                                                <div className="absolute inset-0 flex items-center justify-center">
                                                    <ImageIcon className="h-10 w-10 text-muted-foreground/40" />
                                                </div>
                                            )}
                                            <div className="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent" />

                                            <div className="relative z-10 flex w-full items-end justify-between p-4">
                                                <div className="flex items-center gap-3">
                                                    <div className="flex h-10 w-10 shrink-0 items-center justify-center overflow-hidden rounded-full border-2 border-white/60 bg-background">
                                                        {comp.logo_image_url ? (
                                                            <img
                                                                src={comp.logo_image_url}
                                                                alt=""
                                                                className="h-full w-full object-cover"
                                                            />
                                                        ) : (
                                                            <span className="text-lg">{typeIcons[group.type] ?? '🚴'}</span>
                                                        )}
                                                    </div>
                                                    <div className="text-white">
                                                        <p className="font-semibold leading-tight">{comp.name}</p>
                                                        <div className="flex items-center gap-1.5 text-xs text-white/80">
                                                            {comp.country_id && (
                                                                <FlagIcon code={comp.country_id} className="inline-block h-3 w-4 rounded-sm" />
                                                            )}
                                                            {comp.country_name ?? comp.country_id ?? ''}
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <CardContent className="flex items-center justify-between gap-2 p-4">
                                            <div className="flex items-center gap-2">
                                                <Badge variant="secondary" className="text-xs">{comp.typeLabel}</Badge>
                                                <span className="text-xs text-muted-foreground">{comp.year}</span>
                                            </div>

                                            {comp.official_league_id ? (
                                                <Button size="sm" asChild>
                                                    <Link href={route('leagues.show', comp.official_league_id)}>
                                                        <Users className="mr-1 h-3 w-3" />
                                                        {comp.official_league_name ?? 'Liga oficial'}
                                                    </Link>
                                                </Button>
                                            ) : (
                                                <Button size="sm" variant="outline" disabled>
                                                    Sin liga oficial
                                                </Button>
                                            )}
                                        </CardContent>
                                    </Card>
                                ))}
                            </div>
                        </section>
                    ))
                )}
            </div>
        </AppLayout>
    );
}
