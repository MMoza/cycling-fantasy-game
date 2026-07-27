import { useState } from 'react';
import { router } from '@inertiajs/react';
import AdminLayout from '@/Layouts/AdminLayout';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { RichTextEditor } from '@/components/RichTextEditor';
import { ArrowLeft, Save, Send } from 'lucide-react';

interface User {
    id: string;
    name: string;
    email: string;
}

interface EmailData {
    id: string;
    subject: string;
    body_html: string;
    recipients: string;
    recipient_ids: string[] | null;
    scheduled_at: string | null;
    status: string;
}

interface FormProps {
    email: EmailData | null;
    users: User[];
}

export default function Form({ email, users }: FormProps) {
    const isEditing = email !== null;

    const [subject, setSubject] = useState(email?.subject ?? '');
    const [bodyHtml, setBodyHtml] = useState(email?.body_html ?? '');
    const [recipients, setRecipients] = useState(email?.recipients ?? 'all_users');
    const [recipientIds, setRecipientIds] = useState<string[]>(email?.recipient_ids ?? []);
    const [scheduledAt, setScheduledAt] = useState(
        email?.scheduled_at ? new Date(email.scheduled_at).toISOString().slice(0, 16) : ''
    );

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();

        const data = {
            subject,
            body_html: bodyHtml,
            recipients,
            recipient_ids: recipients === 'custom' ? recipientIds : null,
            scheduled_at: scheduledAt || null,
        };

        if (isEditing) {
            router.patch(route('admin.emails.update', email.id), data);
        } else {
            router.post(route('admin.emails.store'), data);
        }
    };

    const handleSendNow = () => {
        if (!isEditing) return;

        const data = {
            subject,
            body_html: bodyHtml,
            recipients,
            recipient_ids: recipients === 'custom' ? recipientIds : null,
            scheduled_at: null,
        };

        router.patch(route('admin.emails.update', email.id), data, {
            onSuccess: () => {
                router.post(route('admin.emails.send-now', email.id));
            },
        });
    };

    const toggleRecipient = (userId: string) => {
        setRecipientIds((prev) =>
            prev.includes(userId) ? prev.filter((id) => id !== userId) : [...prev, userId]
        );
    };

    return (
        <AdminLayout>
            <div className="space-y-6">
                <div className="flex items-center gap-4">
                    <Button
                        variant="ghost"
                        size="sm"
                        onClick={() => router.visit(route('admin.emails.index'))}
                    >
                        <ArrowLeft className="h-4 w-4 mr-2" />
                        Volver
                    </Button>
                    <div>
                        <h1 className="text-2xl font-bold">
                            {isEditing ? 'Editar email' : 'Nuevo email'}
                        </h1>
                        <p className="text-sm text-muted-foreground mt-1">
                            {isEditing ? 'Modifica el contenido del email' : 'Crea un nuevo email para enviar'}
                        </p>
                    </div>
                </div>

                <form onSubmit={handleSubmit} className="space-y-6">
                    {/* Subject */}
                    <div className="space-y-2">
                        <Label htmlFor="subject">Asunto</Label>
                        <Input
                            id="subject"
                            value={subject}
                            onChange={(e) => setSubject(e.target.value)}
                            placeholder="Asunto del email"
                            required
                        />
                    </div>

                    {/* Body */}
                    <div className="space-y-2">
                        <Label>Contenido</Label>
                        <RichTextEditor content={bodyHtml} onChange={setBodyHtml} />
                    </div>

                    {/* Recipients */}
                    <div className="space-y-2">
                        <Label>Destinatarios</Label>
                        <div className="space-y-2">
                            <label className="flex items-center gap-2">
                                <input
                                    type="radio"
                                    name="recipients"
                                    value="all_users"
                                    checked={recipients === 'all_users'}
                                    onChange={(e) => setRecipients(e.target.value)}
                                />
                                <span>Todos los usuarios</span>
                            </label>
                            <label className="flex items-center gap-2">
                                <input
                                    type="radio"
                                    name="recipients"
                                    value="custom"
                                    checked={recipients === 'custom'}
                                    onChange={(e) => setRecipients(e.target.value)}
                                />
                                <span>Destinatarios personalizados</span>
                            </label>
                        </div>

                        {recipients === 'custom' && (
                            <div className="mt-4 border border-border rounded-lg p-4 max-h-64 overflow-y-auto">
                                <div className="space-y-2">
                                    {users.map((user) => (
                                        <label key={user.id} className="flex items-center gap-2">
                                            <input
                                                type="checkbox"
                                                checked={recipientIds.includes(user.id)}
                                                onChange={() => toggleRecipient(user.id)}
                                            />
                                            <span className="text-sm">
                                                {user.name} ({user.email})
                                            </span>
                                        </label>
                                    ))}
                                </div>
                            </div>
                        )}
                    </div>

                    {/* Schedule */}
                    <div className="space-y-2">
                        <Label htmlFor="scheduled_at">Programar envío (opcional)</Label>
                        <Input
                            id="scheduled_at"
                            type="datetime-local"
                            value={scheduledAt}
                            onChange={(e) => setScheduledAt(e.target.value)}
                        />
                        <p className="text-xs text-muted-foreground">
                            Déjalo vacío para guardar como borrador
                        </p>
                    </div>

                    {/* Actions */}
                    <div className="flex gap-2">
                        <Button type="submit">
                            <Save className="h-4 w-4 mr-2" />
                            {isEditing ? 'Actualizar' : 'Guardar'}
                        </Button>
                        {isEditing && email.status !== 'sent' && (
                            <Button type="button" variant="outline" onClick={handleSendNow}>
                                <Send className="h-4 w-4 mr-2" />
                                Enviar ahora
                            </Button>
                        )}
                    </div>
                </form>
            </div>
        </AdminLayout>
    );
}
