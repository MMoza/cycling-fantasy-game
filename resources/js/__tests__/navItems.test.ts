import { describe, it, expect, beforeAll } from 'vitest';
import { buildNavItems } from '@/Layouts/navItems';

beforeAll(() => {
    (globalThis as any).route = Object.assign(
        (name: string, params?: unknown) => `/mock/${name}${params !== undefined ? `/${params}` : ''}`,
        { current: () => false },
    );
});

describe('buildNavItems', () => {
    it('always includes Clasificación pointing to the season classification', () => {
        const withoutLeague = buildNavItems(null, false);
        const withLeague = buildNavItems({ id: 'league-1' }, false);

        for (const items of [withoutLeague, withLeague]) {
            const clasificacion = items.find((item) => item.label === 'Clasificación');
            expect(clasificacion).toBeDefined();
            expect(clasificacion!.route).toBe('season.classification');
            expect(clasificacion!.href).toContain('season.classification');
        }
    });

    it('shows Etapa only when there is an active league', () => {
        expect(buildNavItems(null, false).find((item) => item.label === 'Etapa')).toBeUndefined();
        expect(buildNavItems({ id: 'league-1' }, false).find((item) => item.label === 'Etapa')).toBeDefined();
    });

    it('shows Admin only for admins', () => {
        expect(buildNavItems(null, false).find((item) => item.label === 'Admin')).toBeUndefined();
        expect(buildNavItems(null, true).find((item) => item.label === 'Admin')).toBeDefined();
    });

    it('keeps Dashboard and Temporada always visible', () => {
        const items = buildNavItems(null, false);
        expect(items.map((item) => item.label)).toEqual(['Dashboard', 'Temporada', 'Clasificación']);
    });
});
