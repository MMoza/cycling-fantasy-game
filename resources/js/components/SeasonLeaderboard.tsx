import { useEffect, useState } from 'react';
import { Link } from '@inertiajs/react';
import { Trophy } from 'lucide-react';
import ScrollReveal from '@/components/ScrollReveal';
import Avatar from '@/components/Avatar';

interface LeaderboardEntry {
    position: number;
    user_name: string;
    avatar: string | null;
    total_points: number;
}

export default function SeasonLeaderboard() {
    const [leaderboard, setLeaderboard] = useState<LeaderboardEntry[]>([]);

    useEffect(() => {
        fetch(route('api.public.season-classification'))
            .then((res) => res.json())
            .then((data) => setLeaderboard(data.leaderboard || []))
            .catch(() => setLeaderboard([]));
    }, []);

    if (leaderboard.length === 0) {
        return null;
    }

    return (
        <section className="py-20 sm:py-28">
            <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <ScrollReveal>
                    <div className="mx-auto max-w-2xl text-center">
                        <h2 className="text-3xl font-bold tracking-tight text-foreground sm:text-4xl">
                            Clasificación de la temporada
                        </h2>
                        <p className="mt-4 text-muted-foreground">
                            Los mejores pronosticadores de Pedales
                        </p>
                    </div>
                </ScrollReveal>
                <ScrollReveal delay={0.2}>
                    <div className="mx-auto mt-16 max-w-2xl">
                        <div className="overflow-hidden rounded-lg border bg-background">
                            <div className="divide-y">
                                {leaderboard.map((entry) => (
                                    <div
                                        key={entry.user_name}
                                        className="flex items-center justify-between px-6 py-4"
                                    >
                                        <div className="flex items-center gap-4">
                                            <span className="w-6 text-center text-sm font-bold text-muted-foreground">
                                                #{entry.position}
                                            </span>
                                            <Avatar
                                                user={{ name: entry.user_name, avatar: entry.avatar }}
                                                size="sm"
                                            />
                                            <span className="font-medium">{entry.user_name}</span>
                                        </div>
                                        <div className="flex items-center gap-1.5 text-accent-500">
                                            <Trophy className="h-4 w-4" />
                                            <span className="font-bold tabular-nums">{entry.total_points}</span>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        </div>
                        <div className="mt-6 text-center">
                            <Link
                                href={route('register')}
                                className="text-sm text-muted-foreground hover:text-foreground transition-colors"
                            >
                                Inicia sesión para ver tu posición →
                            </Link>
                        </div>
                    </div>
                </ScrollReveal>
            </div>
        </section>
    );
}
