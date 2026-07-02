import { useRef, useState } from 'react';
import { router, usePage } from '@inertiajs/react';
import Avatar from '@/components/Avatar';
import { Button } from '@/components/ui/button';
import { Trash2, Upload } from 'lucide-react';

export default function UpdateAvatarForm({ className = '' }: { className?: string }) {
    const user = usePage().props.auth.user;
    const fileInputRef = useRef<HTMLInputElement>(null);
    const [preview, setPreview] = useState<string | null>(null);
    const [processing, setProcessing] = useState(false);

    const handleFileChange = (e: React.ChangeEvent<HTMLInputElement>) => {
        const file = e.target.files?.[0];
        if (!file) return;

        // Show instant preview
        const objectUrl = URL.createObjectURL(file);
        setPreview(objectUrl);
    };

    const handleUpload = () => {
        const file = fileInputRef.current?.files?.[0];
        if (!file) return;

        setProcessing(true);

        router.post(
            route('profile.avatar'),
            { avatar: file },
            {
                forceFormData: true,
                preserveScroll: true,
                onFinish: () => {
                    setProcessing(false);
                    setPreview(null);
                    if (fileInputRef.current) {
                        fileInputRef.current.value = '';
                    }
                },
            },
        );
    };

    const handleDelete = () => {
        setProcessing(true);

        router.delete(route('profile.avatar.delete'), {
            preserveScroll: true,
            onFinish: () => {
                setProcessing(false);
                setPreview(null);
            },
        });
    };

    const displayUser = preview
        ? { name: user.name, avatar: preview }
        : user;

    const hasAvatar = !!(user.avatar || preview);

    return (
        <section className={className}>
            <header>
                <h2 className="text-lg font-medium text-gray-900">
                    Foto de perfil
                </h2>
                <p className="mt-1 text-sm text-gray-600">
                    Sube una imagen para personalizar tu perfil.
                </p>
            </header>

            <div className="mt-6 flex items-center gap-6">
                <Avatar user={displayUser} size="lg" />

                <div className="flex flex-col gap-3">
                    <div className="flex gap-3">
                        <input
                            ref={fileInputRef}
                            type="file"
                            accept="image/jpeg,image/png,image/webp"
                            className="hidden"
                            onChange={handleFileChange}
                        />

                        {preview ? (
                            <>
                                <Button
                                    type="button"
                                    onClick={handleUpload}
                                    disabled={processing}
                                    size="sm"
                                >
                                    <Upload className="mr-2 h-4 w-4" />
                                    {processing ? 'Subiendo...' : 'Guardar imagen'}
                                </Button>
                                <Button
                                    type="button"
                                    variant="outline"
                                    size="sm"
                                    onClick={() => {
                                        setPreview(null);
                                        if (fileInputRef.current) {
                                            fileInputRef.current.value = '';
                                        }
                                    }}
                                    disabled={processing}
                                >
                                    Cancelar
                                </Button>
                            </>
                        ) : (
                            <>
                                <Button
                                    type="button"
                                    variant="outline"
                                    size="sm"
                                    onClick={() => fileInputRef.current?.click()}
                                    disabled={processing}
                                >
                                    <Upload className="mr-2 h-4 w-4" />
                                    Cambiar imagen
                                </Button>
                                {hasAvatar && (
                                    <Button
                                        type="button"
                                        variant="ghost"
                                        size="sm"
                                        onClick={handleDelete}
                                        disabled={processing}
                                        className="text-destructive hover:bg-destructive/10"
                                    >
                                        <Trash2 className="mr-2 h-4 w-4" />
                                        Eliminar
                                    </Button>
                                )}
                            </>
                        )}
                    </div>

                    <p className="text-xs text-muted-foreground">
                        JPEG, PNG o WebP. Máximo 2 MB.
                    </p>
                </div>
            </div>
        </section>
    );
}
