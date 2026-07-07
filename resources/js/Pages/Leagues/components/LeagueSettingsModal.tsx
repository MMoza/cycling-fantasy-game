import { useState } from 'react';
import { router } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { X, Save, Copy, Info } from 'lucide-react';
import type { League } from './types';

interface LeagueSettingsModalProps {
    league: League;
    open: boolean;
    onClose: () => void;
    onScoringInfoOpen: () => void;
}

export function LeagueSettingsModal({ league, open, onClose, onScoringInfoOpen }: LeagueSettingsModalProps) {
    const [formName, setFormName] = useState(league.name);
    const [formIsPublic, setFormIsPublic] = useState(league.is_public);
    const [saving, setSaving] = useState(false);
    const [copied, setCopied] = useState(false);

    if (!open) return null;

    const copyInviteCode = () => {
        navigator.clipboard.writeText(league.invite_code);
        setCopied(true);
        setTimeout(() => setCopied(false), 2000);
    };

    const saveSettings = () => {
        setSaving(true);
        router.put(
            route('leagues.update', league.id),
            {
                name: formName,
                is_public: formIsPublic,
            },
            {
                preserveScroll: true,
                onFinish: () => setSaving(false),
                onSuccess: () => onClose(),
            }
        );
    };

    return (
        <div className="fixed inset-0 z-50 flex items-end sm:items-center justify-center">
            <div className="fixed inset-0 bg-black/50" onClick={onClose} />
            <div className="relative z-10 w-full max-w-lg rounded-t-xl bg-popover p-6 shadow-lg sm:rounded-xl animate-in fade-in-0 zoom-in-95 slide-in-from-bottom-8 sm:slide-in-from-bottom-0">
                <div className="mb-6 flex items-center justify-between">
                    <h2 className="text-lg font-semibold">Ajustes de la liga</h2>
                    <button
                        type="button"
                        onClick={onClose}
                        className="rounded-lg p-1.5 text-muted-foreground transition-colors hover:bg-accent hover:text-accent-foreground"
                    >
                        <X className="h-5 w-5" />
                    </button>
                </div>

                <div className="space-y-6">
                    <div className="space-y-3">
                        <div className="flex items-center justify-between">
                            <Label className="text-base font-medium">Sistema de puntuación</Label>
                            <Button
                                type="button"
                                variant="ghost"
                                size="sm"
                                onClick={onScoringInfoOpen}
                                className="gap-1"
                            >
                                <Info className="h-4 w-4" />
                                Detalles
                            </Button>
                        </div>
                        <div className="rounded-lg border bg-muted/50 p-4">
                            <p className="font-medium">{league.scoring_system.name}</p>
                            <p className="mt-1 text-sm text-muted-foreground">{league.scoring_system.description}</p>
                        </div>
                    </div>

                    <div className="space-y-3">
                        <Label className="text-base font-medium">Código de invitación</Label>
                        <div className="flex items-center gap-2">
                            <code className="flex-1 rounded-lg border bg-muted px-3 py-2 text-sm font-mono">
                                {league.invite_code}
                            </code>
                            <Button
                                type="button"
                                variant="outline"
                                size="sm"
                                onClick={copyInviteCode}
                                className="shrink-0"
                            >
                                {copied ? (
                                    <span className="text-xs">Copiado</span>
                                ) : (
                                    <Copy className="h-4 w-4" />
                                )}
                            </Button>
                        </div>
                    </div>

                    {league.is_owner ? (
                        <>
                            <div className="space-y-3">
                                <Label htmlFor="settings-name" className="text-base font-medium">Nombre de la liga</Label>
                                <Input
                                    id="settings-name"
                                    value={formName}
                                    onChange={(e) => setFormName(e.target.value)}
                                />
                            </div>

                            {!league.is_official && (
                                <div className="flex items-center justify-between">
                                    <div>
                                        <Label className="text-base font-medium">Liga pública</Label>
                                        <p className="text-sm text-muted-foreground">Cualquier usuario puede encontrar y unirse</p>
                                    </div>
                                    <Button
                                        type="button"
                                        variant={formIsPublic ? 'default' : 'secondary'}
                                        size="sm"
                                        onClick={() => setFormIsPublic(!formIsPublic)}
                                    >
                                        {formIsPublic ? 'Pública' : 'Privada'}
                                    </Button>
                                </div>
                            )}

                            <div className="flex justify-end gap-3 pt-2">
                                <Button variant="outline" onClick={onClose}>
                                    Cancelar
                                </Button>
                                <Button onClick={saveSettings} disabled={saving}>
                                    <Save className="mr-2 h-4 w-4" />
                                    {saving ? 'Guardando...' : 'Guardar cambios'}
                                </Button>
                            </div>
                        </>
                    ) : (
                        <div className="space-y-4 border-t pt-4">
                            <div>
                                <Label className="text-base font-medium">Nombre</Label>
                                <p className="mt-1 text-sm">{league.name}</p>
                            </div>
                            <div>
                                <Label className="text-base font-medium">Participantes</Label>
                                <p className="mt-1 text-sm">
                                    {league.member_count}
                                    {league.is_official ? '' : ' / 20 máx'}
                                </p>
                            </div>
                            <div>
                                <Label className="text-base font-medium">Tipo</Label>
                                <p className="mt-1 text-sm">{league.is_public ? 'Pública' : 'Privada'}</p>
                            </div>
                        </div>
                    )}
                </div>
            </div>
        </div>
    );
}
