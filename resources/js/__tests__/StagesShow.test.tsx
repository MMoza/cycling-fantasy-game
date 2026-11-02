import { render, screen, fireEvent, waitFor } from '@testing-library/react';
import { describe, it, expect, vi, beforeAll, beforeEach } from 'vitest';
import Show from '@/Pages/Stages/Show';

const postMock = vi.fn();

vi.mock('@inertiajs/react', () => ({
    Head: () => null,
    Link: ({ href, children, ...rest }: any) => <a href={href} {...rest}>{children}</a>,
    router: { post: (...args: any[]) => postMock(...args) },
    usePage: () => ({ props: { errors: {}, auth: { user: { id: 'u1' } } } }),
}));

vi.mock('@/Layouts/AppLayout', () => ({ default: ({ children }: any) => <div>{children}</div> }));

vi.mock('@/components/ui/searchable-select', () => ({
    default: ({ options, value, onChange, placeholder }: any) => (
        <select aria-label={placeholder ?? 'select'} value={value ?? ''} onChange={(e) => onChange(e.target.value)}>
            <option value="">{placeholder ?? 'Seleccionar'}</option>
            {options.map((o: any) => (
                <option key={o.value} value={o.value}>{o.label}</option>
            ))}
        </select>
    ),
}));

vi.mock('@/components/ui/stage-type-icon', () => ({
    StageTypeIcon: () => null,
}));

vi.mock('@/Pages/Leagues/components/StageLeaderboard', () => ({
    StageLeaderboard: () => null,
}));

beforeAll(() => {
    (globalThis as any).route = (name: string) => `/mock/${name}`;
});

beforeEach(() => {
    postMock.mockClear();
    (globalThis as any).fetch = vi.fn().mockResolvedValue({
        ok: true,
        status: 200,
        json: async () => ({ predictions: {} }),
    });
});

const makeProps = (overrides: Record<string, any> = {}) => ({
    league_id: 'league-1',
    league_name: 'Liga de prueba',
    stage: {
        id: 'stage-1',
        number: 5,
        name: 'Lille → Paris',
        date: '2026-07-05',
        type: 'Llano',
        type_value: 'flat',
        distance: '180.5 km',
        elevation_gain: 850,
        difficulty: 2,
        profile_image: null,
        origin: 'Lille',
        destination: 'Paris',
        status: 'upcoming',
        scheduled_start: null,
        live_stream_url: null,
    },
    is_finished: false,
    is_locked: false,
    predictions: {},
    stage_results: [],
    stage_classification: [],
    navigation: { prev: null, next: null },
    all_stages: [],
    availableRiders: [
        { value: 'r1', label: 'Rider One' },
        { value: 'r2', label: 'Rider Two' },
        { value: 'r3', label: 'Rider Three' },
    ],
    availableTeams: [{ value: 't1', label: 'Team One' }],
    pcs_slug: null,
    edition_year: 2026,
    stage_rules: [
        { category: 'stage_winner', label: 'Ganador de etapa', points: 10 },
        { category: 'stage_second', label: '2º clasificado', points: 6 },
        { category: 'stage_third', label: '3º clasificado', points: 4 },
        { category: 'stage_leader', label: 'Líder general', points: 8 },
        { category: 'stage_combativo', label: 'Combativo', points: 3 },
    ],
    total_possible_points: 31,
    ...overrides,
});

const selects = () => screen.getAllByRole('combobox') as HTMLSelectElement[];

describe('Stage prediction form', () => {
    it('allows selecting the same rider in different positions', () => {
        render(<Show {...makeProps()} />);

        fireEvent.change(selects()[0], { target: { value: 'r1' } });
        fireEvent.change(selects()[1], { target: { value: 'r1' } });

        expect(selects()[1].value).toBe('r1');
        // La lista de opciones no se filtra: r1 sigue disponible en el resto de posiciones
        expect([...selects()[2].options].map((o) => o.value)).toContain('r1');
    });

    it('blocks saving with duplicated riders and names them', async () => {
        render(<Show {...makeProps()} />);

        fireEvent.change(selects()[0], { target: { value: 'r1' } });
        fireEvent.change(selects()[1], { target: { value: 'r1' } });
        fireEvent.click(screen.getByRole('button', { name: 'Guardar pronósticos' }));

        expect(postMock).not.toHaveBeenCalled();
        expect(
            await screen.findByText(/«Rider One» está seleccionado en varias posiciones \(Ganador de etapa, 2º clasificado\)/),
        ).toBeTruthy();
    });

    it('keeps the selections after a duplicate error so they can be corrected', async () => {
        render(<Show {...makeProps()} />);

        fireEvent.change(selects()[0], { target: { value: 'r1' } });
        fireEvent.change(selects()[1], { target: { value: 'r1' } });
        fireEvent.click(screen.getByRole('button', { name: 'Guardar pronósticos' }));
        await screen.findByText(/Corrige las posiciones antes de guardar/);

        expect(selects()[0].value).toBe('r1');
        expect(selects()[1].value).toBe('r1');
    });

    it('saves when every position has a different rider', () => {
        render(<Show {...makeProps()} />);

        fireEvent.change(selects()[0], { target: { value: 'r1' } });
        fireEvent.change(selects()[1], { target: { value: 'r2' } });
        fireEvent.change(selects()[2], { target: { value: 'r3' } });
        fireEvent.change(selects()[3], { target: { value: 'r1' } });
        fireEvent.click(screen.getByRole('button', { name: 'Guardar pronósticos' }));

        expect(postMock).toHaveBeenCalledTimes(1);
        expect(postMock.mock.calls[0][1]).toEqual({
            predictions: [
                { category: 'stage_winner', value: 'r1' },
                { category: 'stage_second', value: 'r2' },
                { category: 'stage_third', value: 'r3' },
                { category: 'stage_leader', value: 'r1' },
            ],
        });
    });

    it('allows the same rider as winner, leader and combative', () => {
        render(<Show {...makeProps()} />);

        fireEvent.change(selects()[0], { target: { value: 'r1' } });
        fireEvent.change(selects()[3], { target: { value: 'r1' } });
        fireEvent.change(selects()[4], { target: { value: 'r1' } });
        fireEvent.click(screen.getByRole('button', { name: 'Guardar pronósticos' }));

        expect(postMock).toHaveBeenCalledTimes(1);
    });
});

describe('AI prediction proposal', () => {
    it('fills the form with the AI proposal without saving', async () => {
        (globalThis as any).fetch = vi.fn().mockResolvedValue({
            ok: true,
            status: 200,
            json: async () => ({
                predictions: {
                    stage_winner: 'r2',
                    stage_second: 'r3',
                    stage_third: 'r1',
                    stage_leader: 'r2',
                    stage_combativo: 'r3',
                },
            }),
        });

        render(<Show {...makeProps()} />);

        fireEvent.click(screen.getByRole('button', { name: 'Hacer pronóstico con IA' }));

        expect(await screen.findByText(/Propuesta generada por IA/)).toBeTruthy();

        await waitFor(() => {
            const current = selects();
            expect(current[0].value).toBe('r2');
            expect(current[1].value).toBe('r3');
            expect(current[2].value).toBe('r1');
            expect(current[3].value).toBe('r2');
            expect(current[4].value).toBe('r3');
        });

        // La propuesta no se guarda automáticamente
        expect(postMock).not.toHaveBeenCalled();
    });

    it('shows a loading indicator while the proposal is generated', async () => {
        let resolveFetch: (value: any) => void = () => {};
        (globalThis as any).fetch = vi.fn().mockImplementation(
            () => new Promise((resolve) => { resolveFetch = resolve; }),
        );

        render(<Show {...makeProps()} />);

        fireEvent.click(screen.getByRole('button', { name: 'Hacer pronóstico con IA' }));

        expect(await screen.findByRole('button', { name: /Generando propuesta/ })).toBeTruthy();

        resolveFetch({ ok: true, status: 200, json: async () => ({ predictions: {} }) });
    });

    it('shows an error when the AI fails and keeps manual editing', async () => {
        (globalThis as any).fetch = vi.fn().mockResolvedValue({
            ok: false,
            status: 422,
            json: async () => ({ message: 'El asistente de IA no está disponible en este momento. Haz tu pronóstico manualmente.' }),
        });

        render(<Show {...makeProps()} />);

        fireEvent.click(screen.getByRole('button', { name: 'Hacer pronóstico con IA' }));

        expect(await screen.findByText(/El asistente de IA no está disponible/)).toBeTruthy();

        // Se puede seguir haciendo el pronóstico manualmente
        fireEvent.change(selects()[0], { target: { value: 'r1' } });
        fireEvent.change(selects()[1], { target: { value: 'r2' } });
        fireEvent.change(selects()[2], { target: { value: 'r3' } });
        fireEvent.click(screen.getByRole('button', { name: 'Guardar pronósticos' }));

        expect(postMock).toHaveBeenCalledTimes(1);
    });

    it('shows a friendly message on rate limit errors', async () => {
        (globalThis as any).fetch = vi.fn().mockResolvedValue({
            ok: false,
            status: 429,
            json: async () => ({ message: 'Too Many Attempts.' }),
        });

        render(<Show {...makeProps()} />);

        fireEvent.click(screen.getByRole('button', { name: 'Hacer pronóstico con IA' }));

        expect(await screen.findByText(/Demasiadas peticiones al asistente de IA/)).toBeTruthy();
    });
});
