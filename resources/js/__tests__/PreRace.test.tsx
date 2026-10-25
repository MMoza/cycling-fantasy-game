import { render, screen, fireEvent } from '@testing-library/react';
import { describe, it, expect, vi, beforeAll } from 'vitest';
import PreRace from '@/Pages/Predictions/PreRace';

const postMock = vi.fn();

vi.mock('@inertiajs/react', () => ({
    Head: () => null,
    Link: ({ href, children, ...rest }: any) => <a href={href} {...rest}>{children}</a>,
    router: { post: (...args: any[]) => postMock(...args) },
    usePage: () => ({ props: { errors: {} } }),
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

beforeAll(() => {
    (globalThis as any).route = (name: string) => `/mock/${name}`;
});

const rule = (type: string, points: number, extra: Partial<{ position: number | null; difficulty: number | null; context: string | null }> = {}) => ({
    id: `${type}-${points}-${extra.position ?? 'x'}`,
    type,
    label: type,
    points,
    position: extra.position ?? null,
    difficulty: extra.difficulty ?? null,
    context: extra.context ?? null,
});

const ONE_WEEK_RULES = [
    rule('gc_top_5', 70, { position: 1 }),
    rule('gc_top_5', 50, { position: 2 }),
    rule('gc_top_5', 35, { position: 3 }),
    rule('gc_top_5_partial', 15),
    rule('stage_winner', 8, { difficulty: 1, context: 'pre_stage' }),
];

const STANDARD_RULES = [
    ...[1, 2, 3, 4, 5].map((position) => rule('gc_top_5', 100 - position * 10, { position })),
    rule('gc_top_5_partial', 15),
    ...[1, 2, 3].map((position) => rule('points_winner', 40 - position * 10, { position })),
    rule('points_winner_partial', 8),
    ...[1, 2, 3].map((position) => rule('mountains_winner', 40 - position * 10, { position })),
    ...[1, 2, 3].map((position) => rule('youth_winner', 40 - position * 10, { position })),
    rule('teams_winner', 25),
    rule('super_combativo', 25),
    rule('stage_winner', 10, { difficulty: 1, context: 'pre_stage' }),
];

const makeProps = (rules: any[], overrides: Record<string, any> = {}) => ({
    league_id: 'league-1',
    league_name: 'Liga de prueba',
    competition: { name: 'Carrera de Prueba', year: 2027 },
    is_locked: false,
    predictions: {},
    availableRiders: [
        { value: 'r1', label: 'Rider One' },
        { value: 'r2', label: 'Rider Two' },
        { value: 'r3', label: 'Rider Three' },
    ],
    availableTeams: [{ value: 't1', label: 'Team One' }],
    scoring_system: {
        id: 'ss-1',
        name: 'Sistema',
        description: 'Descripción',
        rules,
    },
    ...overrides,
});

describe('PreRace slots by scoring system', () => {
    it('one-week races show only the GC top 3 slots', () => {
        render(<PreRace {...makeProps(ONE_WEEK_RULES)} />);

        expect(screen.getByText('Top 3 clasificación general')).toBeTruthy();
        // 3 huecos de GC y nada más (ni maillots, ni equipos, ni supercombativo)
        expect(screen.getAllByRole('combobox')).toHaveLength(3);
        expect(screen.queryByText('Clasificación maillot verde')).toBeNull();
        expect(screen.queryByText('Clasificación maillot montaña')).toBeNull();
        expect(screen.queryByText('Clasificación maillot blanco')).toBeNull();
        expect(screen.queryByText('Ganador clasificación equipos')).toBeNull();
        expect(screen.queryByText('Supercombativo final')).toBeNull();
    });

    it('grand tours keep the full form', () => {
        render(<PreRace {...makeProps(STANDARD_RULES)} />);

        expect(screen.getByText('Top 5 clasificación general')).toBeTruthy();
        expect(screen.getByText('Clasificación maillot verde')).toBeTruthy();
        expect(screen.getByText('Clasificación maillot montaña')).toBeTruthy();
        expect(screen.getByText('Clasificación maillot blanco')).toBeTruthy();
        expect(screen.getByText('Ganador clasificación equipos')).toBeTruthy();
        expect(screen.getByText('Supercombativo final')).toBeTruthy();
        // 5 GC + 3x3 podios + equipos + supercombativo
        expect(screen.getAllByRole('combobox')).toHaveLength(16);
    });

    it('scoring modal adapts to the scoring system', () => {
        render(<PreRace {...makeProps(ONE_WEEK_RULES)} />);
        fireEvent.click(screen.getByRole('button', { name: /Puntuación/ }));

        expect(screen.getByText('3º clasificado')).toBeTruthy();
        expect(screen.queryByText('4º clasificado')).toBeNull();
        expect(screen.queryByText('5º clasificado')).toBeNull();
        expect(screen.queryByText('Maillot verde')).toBeNull();
        expect(screen.queryByText('Otras predicciones pre-carrera')).toBeNull();
    });

    it('locked view shows only the scored slots', () => {
        render(<PreRace {...makeProps(ONE_WEEK_RULES, { is_locked: true })} />);

        expect(screen.getAllByText('Sin pronóstico')).toHaveLength(3);
        expect(screen.queryByText('Clasificación maillot verde')).toBeNull();
        expect(screen.queryByText('Supercombativo final')).toBeNull();
    });

    it('one-week submissions only include gc_top_5', () => {
        render(<PreRace {...makeProps(ONE_WEEK_RULES)} />);

        const selects = screen.getAllByRole('combobox');
        fireEvent.change(selects[0], { target: { value: 'r1' } });
        fireEvent.change(selects[1], { target: { value: 'r2' } });
        fireEvent.change(selects[2], { target: { value: 'r3' } });

        fireEvent.submit(document.querySelector('form')!);

        expect(postMock).toHaveBeenCalledWith(
            '/mock/predictions.pre-race.store',
            { predictions: [{ category: 'gc_top_5', value: 'r1,r2,r3' }] },
            expect.anything(),
        );
    });

    it('systems without pre-race rules fall back to the full form', () => {
        render(<PreRace {...makeProps([rule('stage_winner', 10, { difficulty: 1, context: 'pre_stage' })])} />);

        expect(screen.getByText('Top 5 clasificación general')).toBeTruthy();
        expect(screen.getByText('Clasificación maillot verde')).toBeTruthy();
        expect(screen.getAllByRole('combobox')).toHaveLength(16);
    });
});
