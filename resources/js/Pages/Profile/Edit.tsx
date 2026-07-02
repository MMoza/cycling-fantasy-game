import AppLayout from '@/Layouts/AppLayout';
import { PageProps } from '@/types';
import { Head } from '@inertiajs/react';
import { Card, CardContent } from '@/components/ui/card';
import DeleteUserForm from './Partials/DeleteUserForm';
import UpdatePasswordForm from './Partials/UpdatePasswordForm';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm';
import UpdateAvatarForm from './Partials/UpdateAvatarForm';

export default function Edit({
    mustVerifyEmail,
    status,
}: PageProps<{ mustVerifyEmail: boolean; status?: string }>) {
    return (
        <AppLayout>
            <Head title="Profile" />

            <div className="space-y-6">
                <div>
                    <h1 className="text-2xl font-semibold tracking-tight">Perfil</h1>
                    <p className="text-sm text-muted-foreground">
                        Gestiona tu información personal y seguridad
                    </p>
                </div>

                <Card>
                    <div className="h-1 rounded-t-xl bg-brand-600" />
                    <CardContent className="p-6">
                        <UpdateAvatarForm className="max-w-xl" />
                    </CardContent>
                </Card>

                <Card>
                    <div className="h-1 rounded-t-xl bg-brand-600" />
                    <CardContent className="p-6">
                        <UpdateProfileInformationForm
                            mustVerifyEmail={mustVerifyEmail}
                            status={status}
                            className="max-w-xl"
                        />
                    </CardContent>
                </Card>

                <Card>
                    <div className="h-1 rounded-t-xl bg-accent-500" />
                    <CardContent className="p-6">
                        <UpdatePasswordForm className="max-w-xl" />
                    </CardContent>
                </Card>

                <Card>
                    <div className="h-1 rounded-t-xl bg-destructive" />
                    <CardContent className="p-6">
                        <DeleteUserForm className="max-w-xl" />
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}
