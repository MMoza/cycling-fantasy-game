import { render, screen, fireEvent } from '@testing-library/react';
import { describe, it, expect, vi, beforeAll, beforeEach } from 'vitest';
import Index from '@/Pages/Season/Index';
import type { ReactNode } from 'react';

const { routerGet, routerPost, pageFlashRef } = vi.hoisted(() => ({
    routerGet: vi.fn(),
    routerPost: vi.fn(),
    pageFlashRef: { current: undefined as { success?: string; error?: string } | undefined },
}));

vi.mock('@inertiajs/react', () => ({
    Head: () => null,
    Link: ({ href, children, ...rest }: { href: string; children: ReactNode }) => (
        <a href={href} {...rest}>
            {children}
        </a>
    ),
    router: {
        get: (...args: unknown[]) => routerGet(...args),
        post: (...args: unknown[]) => routerPost(...args),
    },
    usePage: () => ({ props: { flash: pageFlashRef.current } }),
}));

vi.mock('@/Layouts/AppLayout', () => ({ default: ({ children }: { children: ReactNode }) => <div>{children}</div> }));

beforeAll(() => {
    (globalThis as any).route = Object.assign(
        (name: string, params?: unknown) =>
            `/mock/${name}${params !== undefined ? `/${[].concat(params as never).join('/')}` : ''}`,
        { current: () => false },
    );
});

beforeEach(() => {
    routerGet.mockClear();
    routerPost.mockClear();
    pageFlashRef.current = undefined;
});

const competition = (overrides: Record<string, unknown> = {}) => ({
    editionId: 'ed-1',
    competitionId: 'c-1',
    competitionName: 'Tour de Francia',
    competitionType: 'gc',
    typeLabel: 'Gran Vuelta',
    countryId: 'FR',
    countryName: 'Francia',
    coverImageUrl: null,
    logoImageUrl: null,
    editionStatus: 'ongoing',
    officialLeagueId: 'league-1',
    memberCount: 10,
    isUserMember: false,
    canJoin: true,
    year: 2027,
    ...overrides,
});

const renderPage = (overrides: Record<string, unknown> = {}) =>
    render(
        <Index
            year={2027}
            years={[2025, 2026, 2027]}
            competitions={[
                competition({ editionId: 'ed-1', competitionName: 'Tour de Francia', isUserMember: true }),
                competition({ editionId: 'ed-2', competitionName: 'Giro de Italia' }),
                competition({ editionId: 'ed-3', competitionName: 'Vuelta a España' }),
                competition({
                    editionId: 'ed-4',
                    competitionName: 'Il Lombardia',
                    canJoin: false,
                    editionStatus: 'finished',
                }),
            ]}
            user_joined_count={1}
            total_competitions={4}
            {...overrides}
        />,
    );

describe('Season/Index', () => {
    it('centers the season title with side navigation', () => {
        renderPage();

        expect(screen.getByRole('heading', { name: 'Temporada 2027' })).toBeInTheDocument();

        // 2027 es la última temporada: siguiente deshabilitado, anterior activo
        expect(screen.getByRole('button', { name: 'Temporada siguiente' })).toBeDisabled();

        fireEvent.click(screen.getByRole('button', { name: 'Temporada anterior' }));
        expect(routerGet).toHaveBeenCalledWith('/mock/season.index/2026');
    });

    it('enables both arrows on intermediate seasons and disables them at the ends', () => {
        renderPage({ year: 2026 });

        fireEvent.click(screen.getByRole('button', { name: 'Temporada anterior' }));
        expect(routerGet).toHaveBeenCalledWith('/mock/season.index/2025');

        fireEvent.click(screen.getByRole('button', { name: 'Temporada siguiente' }));
        expect(routerGet).toHaveBeenCalledWith('/mock/season.index/2027');
    });

    it('renders the year pills and navigates when tapping one', () => {
        renderPage();

        fireEvent.click(screen.getByRole('button', { name: '2025' }));
        expect(routerGet).toHaveBeenCalledWith('/mock/season.index/2025');

        // la píldora activa queda marcada
        expect(screen.getByRole('button', { name: '2027' })).toHaveAttribute('aria-current', 'true');
    });

    it('hides the pills when there is only one season', () => {
        renderPage({ years: [2027] });

        expect(screen.queryByRole('button', { name: '2025' })).not.toBeInTheDocument();
    });

    it('renders the segmented control with Clasificación and disabled Estadísticas', () => {
        renderPage();

        const link = screen.getByRole('link', { name: /Clasificación/ });
        expect(link).toHaveAttribute('href', '/mock/season.classification');

        const stats = screen.getByRole('button', { name: 'Estadísticas' });
        expect(stats).toBeDisabled();
        expect(stats).toHaveAttribute('title', 'Próximamente');
    });

    it('shows the joined summary and the bulk join button with the pending count', () => {
        renderPage();

        expect(screen.getByText('Estás en 1 de 4 competiciones')).toBeInTheDocument();

        fireEvent.click(screen.getByRole('button', { name: /Inscribirme en las 2 que faltan/ }));
        expect(routerPost).toHaveBeenCalledWith(
            '/mock/season.join-all',
            { year: 2027 },
            expect.objectContaining({ preserveScroll: true }),
        );
    });

    it('hides the bulk button when there is nothing joinable', () => {
        renderPage({
            competitions: [
                competition({ editionId: 'ed-1', isUserMember: true }),
                competition({ editionId: 'ed-2', canJoin: false, editionStatus: 'finished' }),
            ],
            user_joined_count: 1,
            total_competitions: 2,
        });

        expect(screen.queryByRole('button', { name: /Inscribirme/ })).not.toBeInTheDocument();
        expect(screen.getByText('Las competiciones restantes ya han finalizado')).toBeInTheDocument();
    });

    it('shows the empty variant when the user is not in any competition', () => {
        renderPage({
            competitions: [competition()],
            user_joined_count: 0,
            total_competitions: 1,
        });

        expect(screen.getByText('Aún no estás inscrito en ninguna de las 1 competiciones')).toBeInTheDocument();
    });

    it('renders inline flash feedback', () => {
        pageFlashRef.current = { success: 'Te has unido a 2 competiciones.' };
        const { rerender } = renderPage();
        expect(screen.getByText('Te has unido a 2 competiciones.')).toBeInTheDocument();

        pageFlashRef.current = { error: 'Has alcanzado el límite de 5 ligas de tu plan Gratuito.' };
        rerender(
            <Index
                year={2027}
                years={[2025, 2026, 2027]}
                competitions={[competition()]}
                user_joined_count={0}
                total_competitions={1}
            />,
        );
        expect(screen.getByText('Has alcanzado el límite de 5 ligas de tu plan Gratuito.')).toBeInTheDocument();
    });
});
