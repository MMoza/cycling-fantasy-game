import { useState } from 'react';
import AdminLayout from '@/Layouts/AdminLayout';
import { Head, Link, router } from '@inertiajs/react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { ArrowLeft, Plus, Trash2, Bike } from 'lucide-react';

interface Rider {
    id: string;
    name: string;
    nationality: string | null;
}

interface RosterYear {
    year: number;
    riders: Rider[];
}

interface AllRider {
    id: string;
    name: string;
}

export default function Show({ team, rosters, allRiders }: { team: { id: string; name: string; country: string | null }; rosters: RosterYear[]; allRiders: AllRider[] }) {
    const [selectedYear, setSelectedYear] = useState(new Date().getFullYear().toString());
    const [selectedRider, setSelectedRider] = useState('');

    const currentRoster = rosters.find((r) => r.year.toString() === selectedYear);

    const addRider = () => {
        if (selectedRider && selectedYear) {
            router.post(route('admin.teams.rosters.add', team.id), {
                rider_id: selectedRider,
                year: Number(selectedYear),
            });
        }
    };

    const removeRider = (riderId: string) => {
        router.delete(route('admin.teams.rosters.remove', [team.id, riderId, selectedYear]));
    };

    return (
        <AdminLayout>
            <Head title={team.name} />

            <div className="space-y-6">
                <div className="flex items-center gap-4">
                    <Button variant="ghost" size="icon" asChild>
                        <Link href={route('admin.teams.index')}>
                            <ArrowLeft className="h-4 w-4" />
                        </Link>
                    </Button>
                    <div>
                        <h1 className="text-2xl font-semibold tracking-tight">{team.name}</h1>
                        <p className="text-sm text-muted-foreground">{team.country ?? '—'}</p>
                    </div>
                </div>

                <Card>
                    <CardHeader className="pb-3">
                        <CardTitle>Plantilla por temporada</CardTitle>
                    </CardHeader>
                    <CardContent className="space-y-4">
                        <div className="flex items-end gap-3">
                            <div className="flex-1 space-y-2">
                                <Label htmlFor="year">Temporada</Label>
                                <Input
                                    id="year"
                                    type="number"
                                    min={2020}
                                    max={2100}
                                    value={selectedYear}
                                    onChange={(e) => setSelectedYear(e.target.value)}
                                />
                            </div>
                            <div className="flex-1 space-y-2">
                                <Label htmlFor="rider">Corredor</Label>
                                <Select value={selectedRider} onValueChange={(v) => v && setSelectedRider(v)}>
                                    <SelectTrigger><SelectValue placeholder="Seleccionar..." /></SelectTrigger>
                                    <SelectContent>
                                        {allRiders.map((r) => (
                                            <SelectItem key={r.id} value={r.id}>{r.name}</SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            </div>
                            <Button onClick={addRider} className="shrink-0">
                                <Plus className="mr-1 h-4 w-4" />
                                Añadir
                            </Button>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader className="pb-3">
                        <CardTitle className="flex items-center gap-2">
                            <Bike className="h-4 w-4 text-brand-600" />
                            Plantilla {selectedYear}
                            <Badge variant="secondary">{currentRoster?.riders.length ?? 0}</Badge>
                        </CardTitle>
                    </CardHeader>
                    <CardContent className="p-0">
                        {!currentRoster || currentRoster.riders.length === 0 ? (
                            <div className="flex flex-col items-center justify-center py-8 text-center">
                                <p className="text-sm text-muted-foreground">Sin corredores en esta temporada</p>
                            </div>
                        ) : (
                            <div className="divide-y">
                                {currentRoster.riders.map((rider) => (
                                    <div key={rider.id} className="flex items-center justify-between px-4 py-3">
                                        <div>
                                            <p className="text-sm font-medium">{rider.name}</p>
                                            {rider.nationality && (
                                                <p className="text-xs text-muted-foreground">{rider.nationality}</p>
                                            )}
                                        </div>
                                        <Button variant="ghost" size="sm" onClick={() => removeRider(rider.id)}>
                                            <Trash2 className="h-4 w-4 text-destructive" />
                                        </Button>
                                    </div>
                                ))}
                            </div>
                        )}
                    </CardContent>
                </Card>
            </div>
        </AdminLayout>
    );
}
