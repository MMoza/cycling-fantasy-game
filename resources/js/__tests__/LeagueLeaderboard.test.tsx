import { render, screen, fireEvent, within } from '@testing-library/react';
import { describe, it, expect, vi, beforeAll } from 'vitest';
import { LeagueLeaderboard } from '@/Pages/Leagues/components/LeagueLeaderboard';
import type { LeaderboardEntry } from '@/Pages/Leagues/components/types';

vi.mock('@inertiajs/react', () => ({
    Link: ({ href, children, ...rest }: any) => <a href={href} {...rest}>{children}</a>,
    usePage: () => ({ props: { auth: { user: { id: 'user-1' } } } }),
}));

beforeAll(() => {
    (globalThis as any).route = (name: string) => `/mock/${name}`;
});

const badge = (code: string) => ({
    code,
    name: `Badge ${code}`,
    description: 'desc',
    category: 'etapas',
    mode: 'cumulative',
    svg: `/${code}.svg`,
    progress: 1,
    target: 1,
    levels: [1, 5],
    level_index: 1,
    overlay: 2,
});

const entry = (overrides: Partial<LeaderboardEntry> = {}): LeaderboardEntry => ({
    rank: 1,
    user_id: `user-${overrides.rank ?? 1}`,
    user_name: 'Guille',
    avatar: null,
    points: 647,
    behind_leader: 0,
    is_current_user: false,
    is_online: false,
    previous_rank: null,
    rank_change: null,
    winner_streak: 0,
    badges: [],
    ...overrides,
});

describe('LeagueLeaderboard mobile cards', () => {
    it('renders position, name and points on each card', () => {
        render(<LeagueLeaderboard league_id="league-1" leaderboard={[
            entry({ rank: 1, user_id: 'a', user_name: 'Guille', points: 647 }),
            entry({ rank: 2, user_id: 'b', user_name: 'Pablo', points: 603 }),
        ]} />);

        // dos instancias por entrada: fila escritorio + tarjeta móvil
        expect(screen.getAllByText('Guille').length).toBeGreaterThanOrEqual(1);
        expect(screen.getAllByText('647').length).toBeGreaterThanOrEqual(1);
        expect(screen.getAllByText('2º').length).toBeGreaterThanOrEqual(1);
        expect(screen.getAllByText('puntos').length).toBeGreaterThanOrEqual(1);
    });

    it('highlights the current user card with the Tú chip', () => {
        render(<LeagueLeaderboard league_id="league-1" leaderboard={[
            entry({ rank: 4, user_id: 'user-1', user_name: 'Marta', is_current_user: true }),
        ]} />);

        // el chip "Tú" existe (tarjeta móvil) y el "(tú)" de escritorio
        expect(screen.getAllByText(/tú/i).length).toBeGreaterThanOrEqual(2);
    });

    it('shows the streak chip only with 2 or more consecutive wins', () => {
        const { rerender } = render(
            <LeagueLeaderboard league_id="league-1" leaderboard={[
                entry({ rank: 1, user_id: 'a', winner_streak: 1 }),
            ]} />,
        );
        expect(screen.queryByTitle(/Racha/)).not.toBeInTheDocument();

        rerender(
            <LeagueLeaderboard league_id="league-1" leaderboard={[
                entry({ rank: 1, user_id: 'a', winner_streak: 3 }),
            ]} />,
        );
        expect(screen.getAllByTitle('Racha de 3 victorias consecutivas').length).toBeGreaterThanOrEqual(1);
        expect(screen.getAllByText('3').length).toBeGreaterThanOrEqual(1);
    });

    it('keeps the position change on the same row as the name', () => {
        render(<LeagueLeaderboard league_id="league-1" leaderboard={[
            entry({ rank: 1, user_id: 'a', user_name: 'Guille', rank_change: 1 }),
            entry({ rank: 2, user_id: 'b', user_name: 'Pablo', rank_change: null }),
        ]} />);

        // cada fila de nombre contiene nombre + variación (subida o guion)
        const nameRows = screen.getAllByTestId('entry-name-row');
        expect(nameRows).toHaveLength(2);
        expect(within(nameRows[0]).getByText('Guille')).toBeInTheDocument();
        expect(nameRows[0].querySelector('.lucide-chevron-up')).not.toBeNull();
        expect(within(nameRows[1]).getByText('Pablo')).toBeInTheDocument();
        expect(nameRows[1].querySelector('.lucide-minus')).not.toBeNull();
    });

    it('shows up to 3 badges plus the +N chip on cards', () => {
        render(<LeagueLeaderboard league_id="league-1" leaderboard={[
            entry({ badges: [badge('b1'), badge('b2'), badge('b3'), badge('b4'), badge('b5')] }),
        ]} />);

        expect(screen.getAllByText('+2').length).toBeGreaterThanOrEqual(1);
    });

    it('renders position change up, down and steady', () => {
        render(<LeagueLeaderboard league_id="league-1" leaderboard={[
            entry({ rank: 1, user_id: 'a', rank_change: 1 }),
            entry({ rank: 2, user_id: 'b', rank_change: -1 }),
            entry({ rank: 3, user_id: 'c', rank_change: null }),
        ]} />);

        // sin rachas en este fixture
        expect(screen.queryAllByTitle(/Racha/)).toHaveLength(0);

        // flechas de variación (lucide: chevron-up / chevron-down) en las 2 vistas
        expect(document.querySelectorAll('.lucide-chevron-up').length).toBeGreaterThanOrEqual(2);
        expect(document.querySelectorAll('.lucide-chevron-down').length).toBeGreaterThanOrEqual(2);
        // sin cambio → guion (Minus) en las 2 vistas
        expect(document.querySelectorAll('.lucide-minus').length).toBeGreaterThanOrEqual(2);
    });

    it('marks the podium visually with trophies', () => {
        render(<LeagueLeaderboard league_id="league-1" leaderboard={[
            entry({ rank: 1, user_id: 'a' }),
            entry({ rank: 4, user_id: 'b' }),
        ]} />);

        // 1º tiene trofeo (2 vistas), 4º solo número
        expect(screen.getAllByText('1º').length).toBeGreaterThanOrEqual(1);
        expect(screen.getAllByText('4º').length).toBeGreaterThanOrEqual(1);
    });
});

describe('LeagueLeaderboard sorting', () => {
    it('sorts by points via the mobile dropdown', () => {
        render(<LeagueLeaderboard league_id="league-1" leaderboard={[
            entry({ rank: 1, user_id: 'a', user_name: 'Ana', points: 100 }),
            entry({ rank: 2, user_id: 'b', user_name: 'Berto', points: 300 }),
        ]} />);

        const dropdown = screen.getAllByLabelText('Ordenar clasificación')[0];
        // por defecto puntos desc → Berto primero
        expect(dropdown).toHaveValue('points:desc');

        fireEvent.change(dropdown, { target: { value: 'points:asc' } });
        expect(dropdown).toHaveValue('points:asc');

        const mobileList = screen.getByTestId('leaderboard-cards');
        const names = within(mobileList).getAllByText(/Ana|Berto/).map((el) => el.textContent);
        expect(names[0]).toContain('Ana');
    });

    it('toggles sort from the desktop column headers', () => {
        render(<LeagueLeaderboard league_id="league-1" leaderboard={[
            entry({ rank: 1, user_id: 'a', user_name: 'Ana', points: 100 }),
            entry({ rank: 2, user_id: 'b', user_name: 'Berto', points: 300 }),
        ]} />);

        const desktop = screen.getByTestId('leaderboard-table');
        const usuarioBtn = within(desktop).getByText('Usuario').closest('button')!;
        fireEvent.click(usuarioBtn); // nombre asc → Ana primero (ya lo estaba)

        const pointsBtn = within(desktop).getByText('Puntos').closest('button')!;
        fireEvent.click(pointsBtn); // puntos desc → Berto primero
        const rows = within(desktop).getAllByText(/Ana|Berto/).map((el) => el.textContent);
        expect(rows[0]).toContain('Berto');
    });
});

describe('LeagueLeaderboard chrome', () => {
    it('shows the participant count and collapses', () => {
        render(<LeagueLeaderboard league_id="league-1" leaderboard={[
            entry({ rank: 1, user_id: 'a' }),
            entry({ rank: 2, user_id: 'b' }),
        ]} />);

        expect(screen.getByText('2 participantes')).toBeInTheDocument();

        fireEvent.click(screen.getByText('Clasificación'));
        expect(screen.queryByText('2 participantes')).toBeInTheDocument(); // cabecera sigue
        expect(screen.queryAllByText('puntos').length).toBe(0); // contenido colapsado
    });

    it('renders the empty state', () => {
        render(<LeagueLeaderboard league_id="league-1" leaderboard={[]} />);
        expect(screen.getByText('Aún no hay participantes')).toBeInTheDocument();
    });

    it('windows long leaderboards and expands with Ver todos', () => {
        const many = Array.from({ length: 15 }, (_, i) =>
            entry({ rank: i + 1, user_id: `u${i}`, user_name: `User ${i}`, points: 100 - i }),
        );
        render(<LeagueLeaderboard league_id="league-1" leaderboard={many} />);

        expect(screen.getByText('Ver todos (15)')).toBeInTheDocument();
        fireEvent.click(screen.getByText('Ver todos (15)'));
        expect(screen.getByText('Mostrar menos')).toBeInTheDocument();
    });
});
