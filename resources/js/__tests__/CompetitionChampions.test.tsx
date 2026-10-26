import { render, screen } from '@testing-library/react';
import { describe, it, expect, vi, beforeAll } from 'vitest';
import Show from '@/Pages/Competitions/Show';

vi.mock('@inertiajs/react', () => ({
    Head: () => null,
    Link: ({ href, children, ...rest }: any) => <a href={href} {...rest}>{children}</a>,
}));

vi.mock('@/Layouts/AppLayout', () => ({ default: ({ children }: any) => <div>{children}</div> }));

beforeAll(() => {
    (globalThis as any).route = (name: string) => `/mock/${name}`;
});

const baseCompetition = {
    id: 'comp-1',
    name: 'Tour de France',
    type: 'gc',
    typeLabel: 'Gran Vuelta',
    countryId: 'FR',
    countryName: 'Francia',
    coverImageUrl: null,
    logoImageUrl: null,
    editionId: 'ed-1',
    year: 2027,
    editionStatus: 'finished',
    editionStartDate: '01 Jul 2027',
    editionEndDate: '27 Jul 2027',
    officialLeagueId: 'league-1',
    officialLeagueName: 'Liga Oficial Tour 2027',
    stagesCount: 21,
    teamsCount: 23,
    ridersCount: 184,
    champions: [],
};

describe('Competitions/Show champions', () => {
    it('renders the champions section with league, user and points', () => {
        render(
            <Show
                competition={{
                    ...baseCompetition,
                    champions: [
                        {
                            leagueName: 'Liga Palmarés',
                            userId: 'user-1',
                            userName: 'Campeón User',
                            avatar: null,
                            points: 645,
                        },
                    ],
                }}
            />,
        );

        expect(screen.getByText('Campeones')).toBeInTheDocument();
        expect(screen.getByText('Campeón User')).toBeInTheDocument();
        expect(screen.getByText('Liga Palmarés')).toBeInTheDocument();
        expect(screen.getByText('645 pts')).toBeInTheDocument();
    });

    it('renders one row per champion league', () => {
        render(
            <Show
                competition={{
                    ...baseCompetition,
                    champions: [
                        { leagueName: 'Liga A', userId: 'user-1', userName: 'Ana', avatar: null, points: 100 },
                        { leagueName: 'Liga B', userId: 'user-2', userName: 'Berto', avatar: null, points: 80 },
                    ],
                }}
            />,
        );

        expect(screen.getByText('Ana')).toBeInTheDocument();
        expect(screen.getByText('Berto')).toBeInTheDocument();
        expect(screen.getByText('Liga A')).toBeInTheDocument();
        expect(screen.getByText('Liga B')).toBeInTheDocument();
    });

    it('hides the section when there are no champions', () => {
        render(<Show competition={baseCompetition} />);

        expect(screen.queryByText('Campeones')).not.toBeInTheDocument();
    });
});
