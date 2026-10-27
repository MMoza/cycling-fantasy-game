import { render, screen, fireEvent, within } from '@testing-library/react';
import { describe, it, expect, vi, beforeAll } from 'vitest';
import Classification from '@/Pages/Season/Classification';

vi.mock('@inertiajs/react', () => ({
    Head: () => null,
    Link: ({ href, children, ...rest }: any) => <a href={href} {...rest}>{children}</a>,
}));

vi.mock('@/Layouts/AppLayout', () => ({ default: ({ children }: any) => <div>{children}</div> }));

beforeAll(() => {
    (globalThis as any).route = Object.assign(
        (name: string, params?: unknown) => `/mock/${name}${params !== undefined ? `/${[].concat(params as never).join('/')}` : ''}`,
        { current: () => false },
    );
});

const aggregated = [
    {
        rank: 1,
        userId: 'user-1',
        userName: 'Guille',
        avatar: null,
        totalPoints: 647,
        isCurrentUser: false,
        breakdown: [
            { competition_name: 'Tour de France', edition_id: 'ed-1', league_id: 'league-1', points: 400 },
            { competition_name: 'Il Lombardia', edition_id: 'ed-2', league_id: 'league-2', points: 247 },
        ],
    },
    {
        rank: 2,
        userId: 'user-2',
        userName: 'Marta',
        avatar: null,
        totalPoints: 600,
        isCurrentUser: true,
        breakdown: [{ competition_name: 'Tour de France', edition_id: 'ed-1', league_id: 'league-1', points: 600 }],
    },
];

const baseProps = {
    year: 2027,
    aggregated_leaderboard: aggregated,
    per_competition: [
        {
            editionId: 'ed-1',
            competitionName: 'Tour de France',
            competitionType: 'gc',
            typeLabel: 'Gran Vuelta',
            logoImageUrl: null,
            editionStatus: 'ongoing',
            leagueId: 'league-1',
            leaderboard: [
                { rank: 1, userId: 'user-1', userName: 'Guille', avatar: null, points: 400, isCurrentUser: false },
                { rank: 2, userId: 'user-2', userName: 'Marta', avatar: null, points: 350, isCurrentUser: true },
            ],
        },
        {
            editionId: 'ed-2',
            competitionName: 'Il Lombardia',
            competitionType: 'monument',
            typeLabel: 'Monumento',
            logoImageUrl: null,
            editionStatus: 'finished',
            leagueId: 'league-2',
            leaderboard: [],
        },
    ],
    by_type: [
        {
            type: 'gc',
            label: 'Grandes Vueltas',
            totalPoints: 900,
            leaderboard: aggregated,
        },
    ],
};

describe('Season/Classification', () => {
    it('renders the three classification tabs', () => {
        render(<Classification {...baseProps} />);

        expect(screen.getByText('General')).toBeInTheDocument();
        expect(screen.getByText('Por competición')).toBeInTheDocument();
        expect(screen.getByText('Por tipo')).toBeInTheDocument();
    });

    it('shows the general classification reusing mobile cards', () => {
        render(<Classification {...baseProps} />);

        // reutiliza LeaderboardList: nombre en filas de tarjeta (entry-name-row)
        const nameRows = screen.getAllByTestId('entry-name-row');
        expect(nameRows.length).toBeGreaterThanOrEqual(2);
        expect(screen.getAllByText('Guille').length).toBeGreaterThanOrEqual(1);
        expect(screen.getAllByText(/647/).length).toBeGreaterThanOrEqual(1);
    });

    it('opens the breakdown when tapping an aggregated entry', () => {
        render(<Classification {...baseProps} />);

        fireEvent.click(screen.getAllByText('Guille')[0]);
        // el desglose se renderiza en las dos vistas (tabla + tarjeta)
        expect(screen.getAllByText('Desglose por competición').length).toBeGreaterThanOrEqual(1);
        expect(screen.getAllByText('Tour de France').length).toBeGreaterThanOrEqual(1);
        expect(screen.getAllByText('Il Lombardia').length).toBeGreaterThanOrEqual(1);
    });

    it('switches to per-competition with member links', () => {
        render(<Classification {...baseProps} />);

        fireEvent.click(screen.getByText('Por competición'));

        // chips de selección de competición
        const chips = screen.getByTestId('selector-chips');
        expect(within(chips).getByText('Tour de France')).toBeInTheDocument();
        expect(within(chips).getByText('Il Lombardia')).toBeInTheDocument();

        // enlaces al perfil del miembro de la liga
        expect(document.querySelector('a[href="/mock/leagues.members.show/league-1/user-1"]')).not.toBeNull();
    });

    it('shows the empty state for competitions without scores', () => {
        render(<Classification {...baseProps} />);

        fireEvent.click(screen.getByText('Por competición'));
        fireEvent.click(screen.getByText('Il Lombardia'));
        expect(screen.getByText('Sin puntuaciones aún')).toBeInTheDocument();
    });

    it('switches to by-type with its chips and entries', () => {
        render(<Classification {...baseProps} />);

        fireEvent.click(screen.getByText('Por tipo'));

        const chips = screen.getByTestId('selector-chips');
        expect(within(chips).getByText('Grandes Vueltas')).toBeInTheDocument();
        expect(screen.getAllByText('Guille').length).toBeGreaterThanOrEqual(1);
    });

    it('renders empty states when there are no scores', () => {
        render(
            <Classification
                {...baseProps}
                aggregated_leaderboard={[]}
                per_competition={[]}
                by_type={[]}
            />,
        );

        expect(screen.getByText('Aún no hay puntuaciones en la temporada')).toBeInTheDocument();

        fireEvent.click(screen.getByText('Por competición'));
        expect(screen.getByText('No hay competiciones con puntuaciones')).toBeInTheDocument();
    });
});
