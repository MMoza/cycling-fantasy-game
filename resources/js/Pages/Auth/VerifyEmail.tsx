import GuestLayout from '@/Layouts/GuestLayout';
import { Head, Link, useForm } from '@inertiajs/react';
import { FormEventHandler } from 'react';

export default function VerifyEmail({ status }: { status?: string }) {
    const { post, processing } = useForm({});

    const submit: FormEventHandler = (e) => {
        e.preventDefault();

        post(route('verification.send'));
    };

    return (
        <GuestLayout>
            <Head title="Verificar correo" />

            <div className="text-sm text-muted-foreground space-y-3">
                <p>
                    Gracias por registrarte. Verifica tu correo electrónico
                    usando el enlace que te hemos enviado.
                </p>
                <p>
                    Si no recibiste el correo, solicita uno nuevo más abajo.
                </p>
            </div>

            {status === 'verification-link-sent' && (
                <div className="text-sm font-medium text-green-600">
                    Se ha enviado un nuevo enlace de verificación a tu correo.
                </div>
            )}

            <form onSubmit={submit} className="space-y-4">
                <button
                    type="submit"
                    disabled={processing}
                    className="inline-flex w-full items-center justify-center rounded-md bg-accent-500 px-6 py-2.5 text-sm font-semibold text-white shadow-lg shadow-accent-500/25 transition-all hover:bg-accent-600 hover:shadow-xl hover:shadow-accent-500/30 disabled:opacity-50 disabled:shadow-none"
                >
                    {processing ? 'Enviando...' : 'Reenviar verificación'}
                </button>

                <Link
                    href={route('logout')}
                    method="post"
                    as="button"
                    className="block w-full text-center text-sm text-muted-foreground underline hover:text-foreground transition-colors"
                >
                    Cerrar sesión
                </Link>
            </form>
        </GuestLayout>
    );
}
