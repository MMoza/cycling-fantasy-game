import AppLayout from '@/Layouts/AppLayout';
import { Head, Link } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { ArrowLeft, Users, Route, FileText, Trophy, Calendar, ImageIcon, Bike } from 'lucide-react';
import { FlagIcon } from '@/components/ui/flag-icon';

interface CompetitionDetail {
    id: string;
    name: string;
    type: string;
    typeLabel: string;
    countryId: string | null;
    countryName: string | null;
    coverImageUrl: string | null;
    logoImageUrl: string | null;
    editionId: string;
    year: number;
    editionStatus: string;
    editionStartDate: string;
    editionEndDate: string;
    officialLeagueId: string | null;
    officialLeagueName: string | null;
    stagesCount: number;
    teamsCount: number;
    ridersCount: number;
}

const typeIcons: Record<string, string> = {
    gc: '🏔️',
    major: '⭐',
    monument: '🏛️',
    classic: '🚴',
    championship: '🏆',
};

export default function Show({ competition }: { competition: CompetitionDetail }) {
    return (
        <AppLayout>
            <Head title={competition.name} />

            <div className="mx-auto max-w-2xl space-y-6">
                <Button variant="ghost" size="sm" asChild>
                    <Link href={route('competitions.index')}>
                        <ArrowLeft className="mr-1 h-4 w-4" />
                        Competiciones
                    </Link>
                </Button>

                <div className="relative flex h-48 items-end overflow-hidden rounded-xl bg-muted sm:h-56">
                    {competition.coverImageUrl ? (
                        <img src={competition.coverImageUrl} alt="" className="absolute inset-0 h-full w-full object-cover" />
                    ) : (
                        <div className="absolute inset-0 flex items-center justify-center">
                            <ImageIcon className="h-16 w-16 text-muted-foreground/40" />
                        </div>
                    )}
                    <div className="absolute inset-0 bg-gradient-to-t from-black/70 via-black/20 to-transparent" />

                    <div className="relative z-10 flex w-full items-end justify-between p-5">
                        <div className="flex items-center gap-4">
                            <div className="flex h-14 w-14 shrink-0 items-center justify-center overflow-hidden rounded-full border-2 border-white/60 bg-background">
                                {competition.logoImageUrl ? (
                                    <img src={competition.logoImageUrl} alt="" className="h-full w-full object-cover" />
                                ) : (
                                    <span className="text-2xl">{typeIcons[competition.type] ?? '🚴'}</span>
                                )}
                            </div>
                            <div className="text-white">
                                <h1 className="text-xl font-bold leading-tight">{competition.name}</h1>
                                <div className="mt-1 flex items-center gap-2 text-sm text-white/80">
                                    <Badge variant="outline" className="border-white/30 text-white/90">
                                        {competition.typeLabel}
                                    </Badge>
                                    {competition.countryId && (
                                        <FlagIcon code={competition.countryId} className="inline-block h-3 w-4 rounded-sm" />
                                    )}
                                    <span>{competition.countryName}</span>
                                    <span>·</span>
                                    <span>{competition.year}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div className="grid grid-cols-3 gap-3">
                    <Card>
                        <CardContent className="flex flex-col items-center py-4">
                            <Route className="mb-1 h-4 w-4 text-muted-foreground" />
                            <span className="text-lg font-bold">{competition.stagesCount}</span>
                            <span className="text-xs text-muted-foreground">Etapas</span>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardContent className="flex flex-col items-center py-4">
                            <Users className="mb-1 h-4 w-4 text-muted-foreground" />
                            <span className="text-lg font-bold">{competition.teamsCount}</span>
                            <span className="text-xs text-muted-foreground">Equipos</span>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardContent className="flex flex-col items-center py-4">
                            <Bike className="mb-1 h-4 w-4 text-muted-foreground" />
                            <span className="text-lg font-bold">{competition.ridersCount}</span>
                            <span className="text-xs text-muted-foreground">Corredores</span>
                        </CardContent>
                    </Card>
                </div>

                <div className="flex items-center gap-2 text-sm text-muted-foreground">
                    <Calendar className="h-4 w-4" />
                    {competition.editionStartDate} → {competition.editionEndDate}
                </div>

                {competition.officialLeagueId ? (
                    <Button className="w-full" size="lg" asChild>
                        <Link href={route('leagues.show', competition.officialLeagueId)}>
                            <Trophy className="mr-2 h-5 w-5" />
                            {competition.officialLeagueName ?? 'Liga oficial'}
                        </Link>
                    </Button>
                ) : (
                    <Button className="w-full" size="lg" variant="outline" disabled>
                        Sin liga oficial disponible
                    </Button>
                )}

                <div className="space-y-4">
                    <Card className="opacity-50">
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2 text-base">
                                <FileText className="h-4 w-4" />
                                Startlist
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <p className="text-sm text-muted-foreground">Próximamente</p>
                        </CardContent>
                    </Card>

                    <Card className="opacity-50">
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2 text-base">
                                <Route className="h-4 w-4" />
                                Etapas y resultados
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <p className="text-sm text-muted-foreground">Próximamente</p>
                        </CardContent>
                    </Card>

                    <Card className="opacity-50">
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2 text-base">
                                <Trophy className="h-4 w-4" />
                                Clasificación
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <p className="text-sm text-muted-foreground">Próximamente</p>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </AppLayout>
    );
}
