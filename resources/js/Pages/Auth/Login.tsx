import Checkbox from '@/breeze/Checkbox';
import InputError from '@/breeze/InputError';
import InputLabel from '@/breeze/InputLabel';
import TextInput from '@/breeze/TextInput';
import GuestLayout from '@/Layouts/GuestLayout';
import { Head, Link, useForm } from '@inertiajs/react';
import { FormEventHandler } from 'react';

export default function Login({
    status,
    canResetPassword,
}: {
    status?: string;
    canResetPassword: boolean;
}) {
    const { data, setData, post, processing, errors, reset } = useForm({
        email: '',
        password: '',
        remember: false as boolean,
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();

        post(route('login'), {
            onFinish: () => reset('password'),
        });
    };

    return (
        <GuestLayout>
            <Head title="Iniciar sesión" />

            {status && (
                <div className="mb-4 text-sm font-medium text-green-600">
                    {status}
                </div>
            )}

            <form onSubmit={submit} className="space-y-5">
                <div>
                    <InputLabel htmlFor="email" value="Correo electrónico" />

                    <TextInput
                        id="email"
                        type="email"
                        name="email"
                        value={data.email}
                        className="mt-1 block w-full"
                        autoComplete="username"
                        isFocused={true}
                        onChange={(e) => setData('email', e.target.value)}
                    />

                    <InputError message={errors.email} className="mt-2" />
                </div>

                <div>
                    <InputLabel htmlFor="password" value="Contraseña" />

                    <TextInput
                        id="password"
                        type="password"
                        name="password"
                        value={data.password}
                        className="mt-1 block w-full"
                        autoComplete="current-password"
                        onChange={(e) => setData('password', e.target.value)}
                    />

                    <InputError message={errors.password} className="mt-2" />
                </div>

                <label className="flex items-center gap-2">
                    <Checkbox
                        name="remember"
                        checked={data.remember}
                        onChange={(e) =>
                            setData(
                                'remember',
                                (e.target.checked || false) as false,
                            )
                        }
                    />
                    <span className="text-sm text-muted-foreground">
                        Recordarme
                    </span>
                </label>

                <div className="space-y-3">
                    <button
                        type="submit"
                        disabled={processing}
                        className="inline-flex w-full items-center justify-center rounded-md bg-accent-500 px-6 py-2.5 text-sm font-semibold text-white shadow-lg shadow-accent-500/25 transition-all hover:bg-accent-600 hover:shadow-xl hover:shadow-accent-500/30 disabled:opacity-50 disabled:shadow-none"
                    >
                        {processing ? 'Entrando...' : 'Entrar'}
                    </button>

                    {canResetPassword && (
                        <Link
                            href={route('password.request')}
                            className="block text-center text-sm text-muted-foreground underline hover:text-foreground transition-colors"
                        >
                            ¿Olvidaste tu contraseña?
                        </Link>
                    )}
                </div>
            </form>
        </GuestLayout>
    );
}
