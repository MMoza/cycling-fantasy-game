import { Link, router } from '@inertiajs/react';
import AdminLayout from '@/Layouts/AdminLayout';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Plus, Edit, Trash2, Send, Mail } from 'lucide-react';

interface Email {
    id: string;
    subject: string;
    recipients: string;
    scheduled_at: string | null;
    status: string;
    status_label: string;
    sent_at: string | null;
    sent_count: number;
    error_message: string | null;
    created_by: string;
    created_at: string;
}

interface IndexProps {
    emails: Email[];
    filters: {
        status: string | null;
    };
}

const statusColors: Record<string, string> = {
    draft: 'bg-gray-100 text-gray-800',
    scheduled: 'bg-blue-100 text-blue-800',
    sent: 'bg-green-100 text-green-800',
    failed: 'bg-red-100 text-red-800',
};

export default function Index({ emails, filters }: IndexProps) {
    const handleDelete = (id: string) => {
        if (confirm('¿Estás seguro de que quieres eliminar este email?')) {
            router.delete(route('admin.emails.destroy', id));
        }
    };

    const handleSendNow = (id: string) => {
        if (confirm('¿Enviar este email ahora?')) {
            router.post(route('admin.emails.send-now', id));
        }
    };

    return (
        <AdminLayout>
            <div className="space-y-6">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl font-bold">Mailing</h1>
                        <p className="text-sm text-muted-foreground mt-1">
                            Gestiona y programa correos electrónicos
                        </p>
                    </div>
                    <Link href={route('admin.emails.create')}>
                        <Button>
                            <Plus className="h-4 w-4 mr-2" />
                            Nuevo email
                        </Button>
                    </Link>
                </div>

                {/* Filters */}
                <div className="flex gap-2">
                    <Link
                        href={route('admin.emails.index')}
                        className={filters.status === null ? 'font-semibold' : ''}
                    >
                        Todos
                    </Link>
                    <Link
                        href={route('admin.emails.index', { status: 'draft' })}
                        className={filters.status === 'draft' ? 'font-semibold' : ''}
                    >
                        Borradores
                    </Link>
                    <Link
                        href={route('admin.emails.index', { status: 'scheduled' })}
                        className={filters.status === 'scheduled' ? 'font-semibold' : ''}
                    >
                        Programados
                    </Link>
                    <Link
                        href={route('admin.emails.index', { status: 'sent' })}
                        className={filters.status === 'sent' ? 'font-semibold' : ''}
                    >
                        Enviados
                    </Link>
                </div>

                {/* Table */}
                <div className="border border-border rounded-lg overflow-hidden">
                    <table className="w-full">
                        <thead className="bg-muted/50">
                            <tr>
                                <th className="text-left px-4 py-3 text-sm font-medium">Asunto</th>
                                <th className="text-left px-4 py-3 text-sm font-medium">Destinatarios</th>
                                <th className="text-left px-4 py-3 text-sm font-medium">Programado</th>
                                <th className="text-left px-4 py-3 text-sm font-medium">Estado</th>
                                <th className="text-left px-4 py-3 text-sm font-medium">Enviado</th>
                                <th className="text-right px-4 py-3 text-sm font-medium">Acciones</th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-border">
                            {emails.length === 0 ? (
                                <tr>
                                    <td colSpan={6} className="px-4 py-8 text-center text-muted-foreground">
                                        <Mail className="h-12 w-12 mx-auto mb-2 opacity-50" />
                                        <p>No hay emails</p>
                                    </td>
                                </tr>
                            ) : (
                                emails.map((email) => (
                                    <tr key={email.id} className="hover:bg-muted/30">
                                        <td className="px-4 py-3">
                                            <div className="font-medium">{email.subject}</div>
                                            <div className="text-xs text-muted-foreground">
                                                Creado por {email.created_by} el {email.created_at}
                                            </div>
                                        </td>
                                        <td className="px-4 py-3 text-sm">{email.recipients}</td>
                                        <td className="px-4 py-3 text-sm">
                                            {email.scheduled_at ?? '-'}
                                        </td>
                                        <td className="px-4 py-3">
                                            <Badge className={statusColors[email.status]}>
                                                {email.status_label}
                                            </Badge>
                                            {email.error_message && (
                                                <div className="text-xs text-red-600 mt-1">
                                                    {email.error_message}
                                                </div>
                                            )}
                                        </td>
                                        <td className="px-4 py-3 text-sm">
                                            {email.sent_at ? (
                                                <div>
                                                    <div>{email.sent_at}</div>
                                                    <div className="text-xs text-muted-foreground">
                                                        {email.sent_count} envíos
                                                    </div>
                                                </div>
                                            ) : (
                                                '-'
                                            )}
                                        </td>
                                        <td className="px-4 py-3">
                                            <div className="flex items-center justify-end gap-2">
                                                {email.status !== 'sent' && (
                                                    <>
                                                        <Button
                                                            variant="ghost"
                                                            size="sm"
                                                            onClick={() => handleSendNow(email.id)}
                                                        >
                                                            <Send className="h-4 w-4" />
                                                        </Button>
                                                        <Link href={route('admin.emails.edit', email.id)}>
                                                            <Button variant="ghost" size="sm">
                                                                <Edit className="h-4 w-4" />
                                                            </Button>
                                                        </Link>
                                                        <Button
                                                            variant="ghost"
                                                            size="sm"
                                                            onClick={() => handleDelete(email.id)}
                                                        >
                                                            <Trash2 className="h-4 w-4 text-red-600" />
                                                        </Button>
                                                    </>
                                                )}
                                            </div>
                                        </td>
                                    </tr>
                                ))
                            )}
                        </tbody>
                    </table>
                </div>
            </div>
        </AdminLayout>
    );
}
