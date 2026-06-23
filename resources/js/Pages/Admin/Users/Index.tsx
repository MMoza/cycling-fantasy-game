import AdminLayout from '@/Layouts/AdminLayout';
import { Head, router } from '@inertiajs/react';
import { Card, CardContent } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Shield, ShieldOff } from 'lucide-react';

interface User {
    id: string;
    name: string;
    email: string;
    is_admin: boolean;
    leagues_count: number;
    created_at: string;
}

export default function Index({ users }: { users: User[] }) {
    const toggleAdmin = (id: string) => {
        router.post(route('admin.users.toggle-admin', id));
    };

    return (
        <AdminLayout>
            <Head title="Usuarios" />

            <div className="space-y-6">
                <div>
                    <h1 className="text-2xl font-semibold tracking-tight">Usuarios</h1>
                    <p className="text-sm text-muted-foreground">Gestiona los usuarios del sistema</p>
                </div>

                <Card>
                    <CardContent className="p-0">
                        <div className="divide-y">
                            {users.map((user) => (
                                <div key={user.id} className="flex items-center justify-between p-4">
                                    <div className="flex items-center gap-3">
                                        <div>
                                            <p className="font-medium">
                                                {user.name}
                                                {user.is_admin && (
                                                    <Badge variant="default" className="ml-2 bg-brand-600 text-xs">
                                                        Admin
                                                    </Badge>
                                                )}
                                            </p>
                                            <p className="text-sm text-muted-foreground">
                                                {user.email} · {user.leagues_count} ligas · Desde {user.created_at}
                                            </p>
                                        </div>
                                    </div>
                                    <Button
                                        variant={user.is_admin ? 'destructive' : 'outline'}
                                        size="sm"
                                        onClick={() => toggleAdmin(user.id)}
                                    >
                                        {user.is_admin ? (
                                            <><ShieldOff className="mr-1 h-3 w-3" /> Quitar admin</>
                                        ) : (
                                            <><Shield className="mr-1 h-3 w-3" /> Hacer admin</>
                                        )}
                                    </Button>
                                </div>
                            ))}
                        </div>
                    </CardContent>
                </Card>
            </div>
        </AdminLayout>
    );
}
