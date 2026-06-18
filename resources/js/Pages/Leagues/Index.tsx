import AppLayout from '@/Layouts/AppLayout';
import { Head, Link, router } from '@inertiajs/react';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Users, Trophy, Copy, Search, Plus } from 'lucide-react';
import { useState } from 'react';

interface League {
    id: string;
    name: string;
    edition: {
        name: string;
        year: number;
    };
    scoring_system: {
        name: string;
    };
    member_count: number;
    owner_id: string;
    invite_code: string;
}

interface DashboardProps {
    leagues: League[];
    auth: { user: { id: string; name: string } };
}

export default function Index({ leagues }: DashboardProps) {
    const [joinCode, setJoinCode] = useState('');
    const [joinDialogOpen, setJoinDialogOpen] = useState(false);

    const handleJoin = () => {
        if (joinCode.trim()) {
            router.post(route('leagues.join'), { invite_code: joinCode });
            setJoinDialogOpen(false);
        }
    };

    const copyInviteCode = (code: string) => {
        navigator.clipboard.writeText(code);
    };

    return (
        <AppLayout>
            <Head title="Mis Ligas" />

            <div className="space-y-6">
                <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h1 className="text-2xl font-semibold tracking-tight">Mis Ligas</h1>
                        <p className="text-sm text-muted-foreground">
                            Gestiona tus ligas y compite con amigos
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

                {leagues.length === 0 ? (
                    <Card>
                        <CardContent className="flex flex-col items-center justify-center py-12 text-center">
                            <Users className="h-12 w-12 text-muted-foreground" />
                            <h3 className="mt-4 font-medium">No hay ligas aún</h3>
                            <p className="mt-1 text-sm text-muted-foreground">
                                Crea una liga o únete a una existente para empezar a competir
                            </p>
                        </CardContent>
                    </Card>
                ) : (
                    <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                        {leagues.map((league) => (
                            <Card key={league.id}>
                                <CardHeader>
                                    <div className="flex items-center justify-between">
                                        <CardTitle className="text-lg">{league.name}</CardTitle>
                                        <Badge variant="secondary">{league.edition.year}</Badge>
                                    </div>
                                    <CardDescription>
                                        {league.edition.name} · {league.scoring_system.name}
                                    </CardDescription>
                                </CardHeader>
                                <CardContent>
                                    <div className="flex items-center gap-4 text-sm text-muted-foreground">
                                        <div className="flex items-center gap-1">
                                            <Users className="h-4 w-4" />
                                            {league.member_count} miembros
                                        </div>
                                        <div className="flex items-center gap-1">
                                            <Trophy className="h-4 w-4" />
                                            Tour 2026
                                        </div>
                                    </div>
                                </CardContent>
                                <CardFooter className="flex justify-between">
                                    <Button
                                        variant="ghost"
                                        size="sm"
                                        onClick={() => copyInviteCode(league.invite_code)}
                                    >
                                        <Copy className="mr-1 h-3 w-3" />
                                        {league.invite_code}
                                    </Button>
                                    <Button size="sm" asChild>
                                        <Link href={route('leagues.show', league.id)}>
                                            Ver liga
                                        </Link>
                                    </Button>
                                </CardFooter>
                            </Card>
                        ))}
                    </div>
                )}
            </div>
        </AppLayout>
    );
}
