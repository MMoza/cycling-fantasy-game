import { useState } from 'react';
import AppLayout from '@/Layouts/AppLayout';
import { Head, Link, router } from '@inertiajs/react';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Users, Trophy, Copy, Search, Plus, Eye, Globe, Sparkles, ShieldCheck } from 'lucide-react';

interface League {
    id: string;
    name: string;
    edition: {
        name: string;
        year: number;
    };
    scoring_system?: {
        name: string;
    };
    member_count: number;
    owner_id?: string;
    invite_code?: string;
    owner_name?: string;
    is_joined?: boolean;
    is_official?: boolean;
}

interface PaginatedData {
    data: League[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
}

interface IndexProps {
    my_leagues: League[];
    public_leagues: PaginatedData | Record<string, never>;
    search_query: string;
}

function LeagueCard({ league, showJoin }: { league: League; showJoin?: boolean }) {
    const [copied, setCopied] = useState(false);

    const copyInviteCode = (code: string) => {
        navigator.clipboard.writeText(code);
        setCopied(true);
        setTimeout(() => setCopied(false), 2000);
    };

    return (
        <Card>
            <CardHeader>
                <div className="flex items-center justify-between">
                    <div className="flex items-center gap-2 min-w-0">
                        <CardTitle className="text-lg truncate">{league.name}</CardTitle>
                        {league.is_official && (
                            <Badge variant="default" className="shrink-0 gap-1 rounded-full bg-brand-600 text-white hover:bg-brand-600 text-[10px] px-1.5 py-0">
                                <ShieldCheck className="h-2.5 w-2.5" />
                                Oficial
                            </Badge>
                        )}
                    </div>
                    <Badge variant="secondary">{league.edition.year}</Badge>
                </div>
                <CardDescription>
                    {league.edition.name}{league.scoring_system ? ` · ${league.scoring_system.name}` : ''}
                </CardDescription>
            </CardHeader>
            <CardContent>
                <div className="flex flex-wrap gap-4 text-sm text-muted-foreground">
                    <div className="flex items-center gap-1">
                        <Users className="h-4 w-4" />
                        {league.member_count} miembros
                    </div>
                    {league.owner_name && (
                        <span className="text-xs">Creada por {league.owner_name}</span>
                    )}
                </div>
            </CardContent>
            <CardFooter className="flex justify-between">
                {league.invite_code ? (
                    <Button
                        variant="ghost"
                        size="sm"
                        onClick={() => copyInviteCode(league.invite_code!)}
                    >
                        <Copy className="mr-1 h-3 w-3" />
                        {copied ? '¡Copiado!' : league.invite_code}
                    </Button>
                ) : (
                    <div />
                )}
                {showJoin && !league.is_joined ? (
                    <Button size="sm" onClick={() => router.post(route('leagues.join'), { invite_code: league.invite_code })}>
                        <Users className="mr-1 h-3 w-3" />
                        Unirse
                    </Button>
                ) : (
                    <Button size="sm" asChild>
                        <Link href={route('leagues.show', league.id)}>
                            <Eye className="mr-1 h-3 w-3" />
                            {league.is_joined ? 'Ver liga' : 'Ver'}
                        </Link>
                    </Button>
                )}
            </CardFooter>
        </Card>
    );
}

export default function Index({ my_leagues, public_leagues, search_query }: IndexProps) {
    const [joinCode, setJoinCode] = useState('');
    const [joinDialogOpen, setJoinDialogOpen] = useState(false);
    const [search, setSearch] = useState(search_query);

    const publicData = 'data' in public_leagues ? (public_leagues as PaginatedData) : null;

    const handleSearch = (e: React.FormEvent) => {
        e.preventDefault();
        router.get(route('leagues.index'), { q: search || undefined });
    };

    const handleClearSearch = () => {
        setSearch('');
        router.get(route('leagues.index'));
    };

    const handleJoin = () => {
        if (joinCode.trim()) {
            router.post(route('leagues.join'), { invite_code: joinCode });
            setJoinDialogOpen(false);
        }
    };

    const goToPage = (page: number) => {
        router.get(route('leagues.index'), { q: search || undefined, page });
    };

    return (
        <AppLayout>
            <Head title="Ligas" />

            <div className="space-y-8">
                <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h1 className="text-2xl font-semibold tracking-tight">Ligas</h1>
                        <p className="text-sm text-muted-foreground">
                            Gestiona tus ligas o encuentra ligas públicas
                        </p>
                    </div>
                    <div className="flex gap-2">
                        <Button variant="outline" onClick={() => setJoinDialogOpen(true)}>
                            <Search className="mr-2 h-4 w-4" />
                            Unirse con código
                        </Button>
                        <Button asChild>
                            <Link href={route('leagues.create')}>
                                <Plus className="mr-2 h-4 w-4" />
                                Crear liga
                            </Link>
                        </Button>
                    </div>
                </div>

                <Dialog open={joinDialogOpen} onOpenChange={setJoinDialogOpen}>
                    <DialogContent>
                        <DialogHeader>
                            <DialogTitle>Unirse a una liga</DialogTitle>
                            <DialogDescription>
                                Introduce el código de invitación de la liga
                            </DialogDescription>
                        </DialogHeader>
                        <div className="space-y-4 py-4">
                            <div className="space-y-2">
                                <Label htmlFor="code">Código de invitación</Label>
                                <Input
                                    id="code"
                                    placeholder="ABC12345"
                                    value={joinCode}
                                    onChange={(e: React.ChangeEvent<HTMLInputElement>) => setJoinCode(e.target.value)}
                                />
                            </div>
                        </div>
                        <DialogFooter>
                            <Button onClick={handleJoin} disabled={!joinCode.trim()}>
                                Unirse
                            </Button>
                        </DialogFooter>
                    </DialogContent>
                </Dialog>

                {/* My leagues */}
                <div>
                    <div className="mb-4 flex items-center gap-2">
                        <Trophy className="h-5 w-5 text-brand-600" />
                        <h2 className="text-lg font-semibold">Mis Ligas</h2>
                    </div>

                    {my_leagues.length === 0 ? (
                        <Card>
                            <CardContent className="flex flex-col items-center justify-center px-6 py-12 text-center">
                                <Users className="h-12 w-12 text-muted-foreground" />
                                <h3 className="mt-4 text-lg font-medium">No estás en ninguna liga</h3>
                                <p className="mt-2 text-sm text-muted-foreground">
                                    Crea una liga o busca una pública para empezar
                                </p>
                            </CardContent>
                        </Card>
                    ) : (
                        <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                            {my_leagues.map((league) => (
                                <LeagueCard key={league.id} league={league} />
                            ))}
                        </div>
                    )}
                </div>

                {/* Public leagues search */}
                <div>
                    <div className="mb-4 flex items-center gap-2">
                        <Globe className="h-5 w-5 text-muted-foreground" />
                        <h2 className="text-lg font-semibold">Ligas Públicas</h2>
                    </div>

                    <form onSubmit={handleSearch} className="mb-4 flex gap-2">
                        <div className="relative flex-1">
                            <Search className="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                            <Input
                                placeholder="Buscar ligas públicas..."
                                value={search}
                                onChange={(e: React.ChangeEvent<HTMLInputElement>) => setSearch(e.target.value)}
                                className="pl-9"
                            />
                        </div>
                        <Button type="submit">Buscar</Button>
                        {search_query && (
                            <Button type="button" variant="outline" onClick={handleClearSearch}>
                                Limpiar
                            </Button>
                        )}
                    </form>

                    {publicData && publicData.data.length > 0 ? (
                        <>
                            <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                                {publicData.data.map((league) => (
                                    <LeagueCard key={league.id} league={league} showJoin />
                                ))}
                            </div>

                            {publicData.last_page > 1 && (
                                <div className="mt-6 flex items-center justify-center gap-2">
                                    <Button
                                        variant="outline"
                                        size="sm"
                                        disabled={publicData.current_page <= 1}
                                        onClick={() => goToPage(publicData.current_page - 1)}
                                    >
                                        Anterior
                                    </Button>
                                    <span className="text-sm text-muted-foreground">
                                        Página {publicData.current_page} de {publicData.last_page}
                                        {' · '}
                                        {publicData.total} ligas
                                    </span>
                                    <Button
                                        variant="outline"
                                        size="sm"
                                        disabled={publicData.current_page >= publicData.last_page}
                                        onClick={() => goToPage(publicData.current_page + 1)}
                                    >
                                        Siguiente
                                    </Button>
                                </div>
                            )}
                        </>
                    ) : search_query ? (
                        <Card>
                            <CardContent className="flex flex-col items-center justify-center px-6 py-12 text-center">
                                <Search className="h-12 w-12 text-muted-foreground" />
                                <h3 className="mt-4 text-lg font-medium">Sin resultados</h3>
                                <p className="mt-2 text-sm text-muted-foreground">
                                    No se encontraron ligas públicas con ese nombre
                                </p>
                            </CardContent>
                        </Card>
                    ) : (
                        <Card>
                            <CardContent className="flex flex-col items-center justify-center px-6 py-12 text-center">
                                <Globe className="h-12 w-12 text-muted-foreground" />
                                <h3 className="mt-4 text-lg font-medium">Busca ligas públicas</h3>
                                <p className="mt-2 text-sm text-muted-foreground">
                                    Usa el buscador para encontrar ligas públicas y unirte a ellas
                                </p>
                            </CardContent>
                        </Card>
                    )}
                </div>
            </div>
        </AppLayout>
    );
}
