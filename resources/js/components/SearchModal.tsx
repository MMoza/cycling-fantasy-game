import { useState, useRef, useEffect } from 'react';
import { Link, router } from '@inertiajs/react';
import { Dialog, DialogBackdrop, DialogPanel, DialogTitle } from '@headlessui/react';
import { Search, User, Bike, Users, Trophy, X } from 'lucide-react';

interface SearchResult {
    id: string;
    name: string;
    type: 'user' | 'rider' | 'team' | 'competition';
}

interface SearchResponse {
    users?: SearchResult[];
    riders?: SearchResult[];
    teams?: SearchResult[];
    competitions?: SearchResult[];
}

const tabs = [
    { key: 'all', label: 'Todo' },
    { key: 'users', label: 'Jugadores', icon: User },
    { key: 'riders', label: 'Corredores', icon: Bike },
    { key: 'teams', label: 'Equipos', icon: Users },
    { key: 'competitions', label: 'Competiciones', icon: Trophy },
] as const;

const typeLinks: Record<string, (id: string) => string> = {
    user: () => route('profile.edit'),
    rider: (id: string) => route('admin.riders.edit', id),
    team: (id: string) => route('admin.teams.show', id),
    competition: (id: string) => route('admin.competitions.edit', id),
};

export default function SearchModal({ open, onClose }: { open: boolean; onClose: () => void }) {
    const [q, setQ] = useState('');
    const [activeTab, setActiveTab] = useState<string>('all');
    const [results, setResults] = useState<SearchResponse>({});
    const [loading, setLoading] = useState(false);
    const inputRef = useRef<HTMLInputElement>(null);
    const timerRef = useRef<ReturnType<typeof setTimeout>>();

    useEffect(() => {
        if (open) {
            setTimeout(() => inputRef.current?.focus(), 100);
        } else {
            setQ('');
            setResults({});
            setActiveTab('all');
        }
    }, [open]);

    useEffect(() => {
        if (q.length < 2) {
            setResults({});
            return;
        }

        if (timerRef.current) clearTimeout(timerRef.current);

        timerRef.current = setTimeout(async () => {
            setLoading(true);
            try {
                const res = await fetch(`/search?q=${encodeURIComponent(q)}&type=${activeTab}`);
                const data = await res.json();
                setResults(data);
            } catch {
                setResults({});
            } finally {
                setLoading(false);
            }
        }, 200);
    }, [q, activeTab]);

    const flatResults = (): SearchResult[] => {
        if (activeTab !== 'all') {
            return results[activeTab as keyof SearchResponse] ?? [];
        }
        const all: SearchResult[] = [];
        for (const key of ['users', 'riders', 'teams', 'competitions'] as const) {
            all.push(...(results[key] ?? []));
        }
        return all;
    };

    const items = flatResults();

    return (
        <Dialog open={open} onClose={onClose} className="relative z-[200]">
            <DialogBackdrop className="fixed inset-0 bg-black/50" />
            <div className="fixed inset-0 flex items-start justify-center pt-[15vh]">
                <DialogPanel className="w-full max-w-lg rounded-xl border bg-popover shadow-2xl outline-none">
                    <div className="flex items-center gap-3 border-b px-4 py-3">
                        <Search className="h-5 w-5 shrink-0 text-muted-foreground" />
                        <input
                            ref={inputRef}
                            type="text"
                            value={q}
                            onChange={(e) => setQ(e.target.value)}
                            placeholder="Buscar jugadores, corredores, equipos..."
                            className="flex-1 bg-transparent text-sm outline-none placeholder:text-muted-foreground"
                        />
                        {q && (
                            <button type="button" onClick={() => setQ('')} className="text-muted-foreground hover:text-foreground">
                                <X className="h-4 w-4" />
                            </button>
                        )}
                    </div>

                    {/* Tabs */}
                    <div className="flex gap-1 border-b px-3 py-2">
                        {tabs.map((tab) => {
                            const TabIcon = 'icon' in tab ? tab.icon : undefined;
                            return (
                                <button
                                    key={tab.key}
                                    type="button"
                                    onClick={() => setActiveTab(tab.key)}
                                    className={`flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-medium transition-colors ${
                                        activeTab === tab.key
                                            ? 'bg-accent text-accent-foreground'
                                            : 'text-muted-foreground hover:bg-muted hover:text-foreground'
                                    }`}
                                >
                                    {TabIcon && <TabIcon className="h-3.5 w-3.5" />}
                                    {tab.label}
                                </button>
                            );
                        })}
                    </div>

                    {/* Results */}
                    <div className="max-h-80 overflow-y-auto p-2">
                        {loading && (
                            <div className="flex items-center justify-center py-8">
                                <div className="h-5 w-5 animate-spin rounded-full border-2 border-muted-foreground border-t-transparent" />
                            </div>
                        )}

                        {!loading && q.length >= 2 && items.length === 0 && (
                            <p className="py-8 text-center text-sm text-muted-foreground">
                                Sin resultados para "{q}"
                            </p>
                        )}

                        {!loading && q.length < 2 && (
                            <p className="py-8 text-center text-sm text-muted-foreground">
                                Escribe al menos 2 caracteres para buscar
                            </p>
                        )}

                        {!loading && items.length > 0 && (
                            <div className="space-y-0.5">
                                {items.map((item) => {
                                    const getLink = typeLinks[item.type];
                                    const href = getLink ? getLink(item.id) : '#';
                                    return (
                                        <Link
                                            key={`${item.type}-${item.id}`}
                                            href={href}
                                            onClick={onClose}
                                            className="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm transition-colors hover:bg-accent hover:text-accent-foreground"
                                        >
                                            <span className="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-muted text-xs font-medium text-muted-foreground">
                                                {item.type === 'user' ? 'U' : item.type === 'rider' ? 'R' : item.type === 'team' ? 'T' : 'C'}
                                            </span>
                                            <span className="truncate">{item.name}</span>
                                            <span className="ml-auto shrink-0 text-xs text-muted-foreground">
                                                {item.type === 'user' ? 'Jugador' : item.type === 'rider' ? 'Corredor' : item.type === 'team' ? 'Equipo' : 'Competición'}
                                            </span>
                                        </Link>
                                    );
                                })}
                            </div>
                        )}
                    </div>

                    {q.length >= 2 && !loading && items.length > 0 && (
                        <div className="border-t px-4 py-2 text-center">
                            <span className="text-xs text-muted-foreground">
                                {items.length} resultado{items.length !== 1 ? 's' : ''}
                            </span>
                        </div>
                    )}
                </DialogPanel>
            </div>
        </Dialog>
    );
}
