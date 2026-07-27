import { useState, useEffect } from 'react';
import { Card, CardContent } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { UserPlus, Mail, MessageCircle, Copy, Check } from 'lucide-react';
import axios from 'axios';

interface InvitationData {
    code: string;
    accepted_count: number;
    invite_url: string;
}

export function InviteFriend() {
    const [invitation, setInvitation] = useState<InvitationData | null>(null);
    const [dialogOpen, setDialogOpen] = useState(false);
    const [copied, setCopied] = useState(false);

    useEffect(() => {
        fetchInvitation();
    }, []);

    const fetchInvitation = async () => {
        try {
            const response = await axios.get(route('invitation.show'));
            setInvitation(response.data);
        } catch (error) {
            console.error('Error fetching invitation:', error);
        }
    };

    const handleCopy = async () => {
        if (!invitation) return;

        try {
            await navigator.clipboard.writeText(invitation.invite_url);
            setCopied(true);
            setTimeout(() => setCopied(false), 2000);
        } catch (error) {
            console.error('Error copying to clipboard:', error);
        }
    };

    const handleWhatsApp = () => {
        if (!invitation) return;

        const text = `¡Únete a Pedales! Regístrate con mi enlace: ${invitation.invite_url}`;
        const url = `https://wa.me/?text=${encodeURIComponent(text)}`;
        window.open(url, '_blank');
    };

    const handleEmail = () => {
        if (!invitation) return;

        const subject = '¡Únete a Pedales!';
        const body = `¡Hola!\n\nTe invito a unirte a Pedales, la plataforma de predicciones ciclistas.\n\nRegístrate con mi enlace: ${invitation.invite_url}\n\n¡Nos vemos en las carreras!`;
        const url = `mailto:?subject=${encodeURIComponent(subject)}&body=${encodeURIComponent(body)}`;
        window.open(url, '_blank');
    };

    if (!invitation) {
        return null;
    }

    return (
        <>
            <Card className="cursor-pointer border-purple-200/60 bg-gradient-to-br from-purple-50 to-white transition-colors hover:from-purple-100/70 dark:border-purple-800/30 dark:from-purple-950/20 dark:to-transparent dark:hover:from-purple-950/30"
                onClick={() => setDialogOpen(true)}
            >
                <CardContent className="flex items-center gap-3 p-4">
                    <div className="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-purple-100 dark:bg-purple-900/30">
                        <UserPlus className="h-5 w-5 text-purple-600 dark:text-purple-400" />
                    </div>
                    <div className="flex-1">
                        <p className="font-medium">Invita a un amigo</p>
                        <p className="text-sm text-muted-foreground">
                            {invitation.accepted_count} {invitation.accepted_count === 1 ? 'amigo se ha unido' : 'amigos se han unido'}
                        </p>
                    </div>
                    <div className="flex items-center gap-2">
                        <span className="text-2xl font-bold text-purple-600 dark:text-purple-400">
                            {invitation.accepted_count}
                        </span>
                    </div>
                </CardContent>
            </Card>

            <Dialog open={dialogOpen} onOpenChange={setDialogOpen}>
                <DialogContent className="sm:max-w-md">
                    <DialogHeader>
                        <DialogTitle>Invita a tus amigos</DialogTitle>
                    </DialogHeader>
                    <div className="space-y-4">
                        <div className="rounded-lg bg-muted p-4">
                            <p className="text-sm text-muted-foreground mb-2">Tu enlace de invitación:</p>
                            <div className="flex items-center gap-2 min-w-0">
                                <code className="flex-1 text-xs bg-background px-3 py-2 rounded border border-border truncate min-w-0">
                                    {invitation.invite_url}
                                </code>
                                <Button
                                    size="sm"
                                    variant="outline"
                                    onClick={handleCopy}
                                    className="shrink-0"
                                >
                                    {copied ? (
                                        <Check className="h-4 w-4 text-green-600" />
                                    ) : (
                                        <Copy className="h-4 w-4" />
                                    )}
                                </Button>
                            </div>
                        </div>

                        <div className="flex flex-wrap gap-3">
                            <Button
                                variant="outline"
                                onClick={handleWhatsApp}
                                className="gap-2 flex-1 min-w-[140px]"
                            >
                                <MessageCircle className="h-4 w-4" />
                                WhatsApp
                            </Button>
                            <Button
                                variant="outline"
                                onClick={handleEmail}
                                className="gap-2 flex-1 min-w-[140px]"
                            >
                                <Mail className="h-4 w-4" />
                                Email
                            </Button>
                        </div>

                        <div className="text-center">
                            <p className="text-sm text-muted-foreground">
                                <span className="font-semibold text-foreground">{invitation.accepted_count}</span>{' '}
                                {invitation.accepted_count === 1 ? 'persona se ha registrado' : 'personas se han registrado'} con tu enlace
                            </p>
                        </div>
                    </div>
                </DialogContent>
            </Dialog>
        </>
    );
}
