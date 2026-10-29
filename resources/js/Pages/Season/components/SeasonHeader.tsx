import { Link } from '@inertiajs/react';
import { BarChart3, Calendar, ChevronLeft, ChevronRight, Trophy } from 'lucide-react';
import { Button } from '@/components/ui/button';

interface SeasonHeaderProps {
    year: number;
    years: number[];
    onYearChange: (year: number) => void;
}

/**
 * Cabecera de la temporada: título centrado navegable con flechas, pills de
 * años y control segmentado (Clasificación / Estadísticas).
 */
export default function SeasonHeader({ year, years, onYearChange }: SeasonHeaderProps) {
    const sortedYears = [...years].sort((a, b) => a - b);
    const prevYear = sortedYears.filter((seasonYear) => seasonYear < year).pop();
    const nextYear = sortedYears.find((seasonYear) => seasonYear > year);

    return (
        <div className="flex flex-col items-center gap-4">
            <div className="flex w-full items-center justify-center gap-1">
                <Button
                    type="button"
                    variant="ghost"
                    size="icon"
                    aria-label="Temporada anterior"
                    disabled={prevYear === undefined}
                    onClick={() => prevYear !== undefined && onYearChange(prevYear)}
                    className="h-9 w-9 shrink-0"
                >
                    <ChevronLeft className="h-5 w-5" />
                </Button>

                <h1 className="flex items-center gap-2 text-2xl font-semibold tracking-tight">
                    <Calendar className="h-6 w-6 text-accent-500" aria-hidden="true" />
                    Temporada {year}
                </h1>

                <Button
                    type="button"
                    variant="ghost"
                    size="icon"
                    aria-label="Temporada siguiente"
                    disabled={nextYear === undefined}
                    onClick={() => nextYear !== undefined && onYearChange(nextYear)}
                    className="h-9 w-9 shrink-0"
                >
                    <ChevronRight className="h-5 w-5" />
                </Button>
            </div>

            {years.length > 1 && (
                <div className="flex max-w-full flex-wrap items-center justify-center gap-1">
                    {years.map((seasonYear) => (
                        <button
                            key={seasonYear}
                            type="button"
                            onClick={() => onYearChange(seasonYear)}
                            aria-current={seasonYear === year ? 'true' : undefined}
                            className={`rounded-full px-3 py-1.5 text-sm font-medium transition-colors ${
                                seasonYear === year
                                    ? 'bg-foreground text-background'
                                    : 'bg-muted text-muted-foreground hover:bg-accent hover:text-accent-foreground'
                            }`}
                        >
                            {seasonYear}
                        </button>
                    ))}
                </div>
            )}

            <div className="grid w-full max-w-md grid-cols-2 gap-1 rounded-lg bg-muted p-1" role="group" aria-label="Secciones de temporada">
                <Link
                    href={route('season.classification')}
                    className="flex items-center justify-center gap-1.5 rounded-md bg-background px-2 py-2 text-xs font-medium text-foreground shadow-sm sm:text-sm"
                >
                    <Trophy className="h-4 w-4 shrink-0" aria-hidden="true" />
                    Clasificación
                </Link>
                <button
                    type="button"
                    disabled
                    title="Próximamente"
                    className="flex cursor-not-allowed items-center justify-center gap-1.5 rounded-md px-2 py-2 text-xs font-medium text-muted-foreground/60 sm:text-sm"
                >
                    <BarChart3 className="h-4 w-4 shrink-0" aria-hidden="true" />
                    Estadísticas
                </button>
            </div>
        </div>
    );
}
