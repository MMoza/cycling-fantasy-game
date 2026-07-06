import AdminLayout from '@/Layouts/AdminLayout';
import { Head, Link } from '@inertiajs/react';
import { Card, CardContent } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Plus, Edit, Bike } from 'lucide-react';
import { FlagIcon } from '@/components/ui/flag-icon';

interface Rider {
    id: string;
    first_name: string;
    last_name: string;
    full_name: string;
    country_id: string | null;
    profile_image: string | null;
    age: number | null;
    team_name: string | null;
}

interface CountryOption {
    value: string;
    label: string;
}

export default function Index({ riders, countries }: { riders: Rider[]; countries: CountryOption[] }) {
    const countryLabel = (id: string | null) => countries.find((c) => c.value === id)?.label ?? id ?? '—';

    return (
        <AdminLayout>
            <Head title="Corredores" />

            <div className="space-y-6">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl font-semibold tracking-tight">Corredores</h1>
                        <p className="text-sm text-muted-foreground">Gestiona los corredores del pelotón</p>
                    </div>
                    <Button asChild>
                        <Link href={route('admin.riders.create')}>
                            <Plus className="mr-2 h-4 w-4" />
                            Nuevo corredor
                        </Link>
                    </Button>
                </div>

                <Card>
                    <CardContent className="p-0">
                        {riders.length === 0 ? (
                            <div className="flex flex-col items-center justify-center px-4 py-12 text-center">
                                <Bike className="h-12 w-12 text-muted-foreground" />
                                <p className="mt-4 text-sm text-muted-foreground">No hay corredores</p>
                            </div>
                        ) : (
                            <div className="divide-y">
                                {riders.map((rider) => (
                                    <div key={rider.id} className="flex items-center justify-between p-4">
                                        <div className="flex items-center gap-3">
                                            {rider.profile_image ? (
                                                <img src={rider.profile_image} alt="" className="h-9 w-9 rounded-full object-cover object-top" />
                                            ) : (
                                                <div className="flex h-9 w-9 items-center justify-center rounded-full bg-muted">
                                                    <Bike className="h-4 w-4 text-muted-foreground" />
                                                </div>
                                            )}
                                            <div>
                                                <p className="font-medium">
                                                    <span className="uppercase">{rider.last_name}</span> {rider.first_name}
                                                </p>
                                                <p className="text-sm text-muted-foreground">
                                                    {rider.country_id && <FlagIcon code={rider.country_id} className="mr-1 inline-block h-3 w-4 align-middle rounded-sm" />}
                                                    {countryLabel(rider.country_id)}
                                                    {rider.team_name ? ` · ${rider.team_name}` : ''}
                                                    {rider.age !== null ? ` · ${rider.age} años` : ''}
                                                </p>
                                            </div>
                                        </div>
                                        <Button variant="ghost" size="sm" asChild>
                                            <Link href={route('admin.riders.edit', rider.id)}>
                                                <Edit className="h-4 w-4" />
                                            </Link>
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
