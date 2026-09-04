export interface SpecialtyEntry {
    rank: number;
    userName: string;
    points: number;
    rankChange: number | null;
}

export interface SpecialtyCategory {
    name: string;
    uciColor: string;
    tagline: string;
    leaderboard: SpecialtyEntry[];
}

export const MOCK_SPECIALTIES: SpecialtyCategory[] = [
    {
        name: 'Grandes Vueltas',
        uciColor: '#0085C7',
        tagline: 'La resistencia se premia. Solo los más constantes llegan al podio.',
        leaderboard: [
            { rank: 1, userName: 'el_chava', points: 1245, rankChange: 1 },
            { rank: 2, userName: 'indurain_93', points: 1189, rankChange: -1 },
            { rank: 3, userName: 'Armstrong7', points: 1056, rankChange: null },
            { rank: 4, userName: 'MILON', points: 987, rankChange: 2 },
            { rank: 5, userName: 'Gepalgo', points: 934, rankChange: 3 },
            { rank: 6, userName: 'M4nety', points: 876, rankChange: -2 },
            { rank: 7, userName: 'erto_bici', points: 823, rankChange: 1 },
            { rank: 8, userName: 'pedaleando23', points: 765, rankChange: -1 },
            { rank: 9, userName: 'rowan_banana', points: 712, rankChange: null },
            { rank: 10, userName: 'pupuninhas', points: 654, rankChange: -3 },
        ],
    },
    {
        name: 'Monumentos',
        uciColor: '#E10600',
        tagline: 'Las carreras más duras del calendario. Solo los valientes ganan.',
        leaderboard: [
            { rank: 1, userName: 'MILON', points: 520, rankChange: 2 },
            { rank: 2, userName: 'Armstrong7', points: 487, rankChange: -1 },
            { rank: 3, userName: 'el_chava', points: 456, rankChange: 1 },
            { rank: 4, userName: 'Gepalgo', points: 423, rankChange: 4 },
            { rank: 5, userName: 'indurain_93', points: 398, rankChange: -2 },
            { rank: 6, userName: 'M4nety', points: 367, rankChange: null },
            { rank: 7, userName: 'erto_bici', points: 334, rankChange: 1 },
            { rank: 8, userName: 'rowan_banana', points: 301, rankChange: -1 },
            { rank: 9, userName: 'pedaleando23', points: 278, rankChange: 2 },
            { rank: 10, userName: 'miguelito_angel', points: 245, rankChange: null },
        ],
    },
    {
        name: 'Campeonatos',
        uciColor: '#00A84F',
        tagline: 'Defender los colores de tu país. El orgullo de ser campeón.',
        leaderboard: [
            { rank: 1, userName: 'Gepalgo', points: 342, rankChange: 5 },
            { rank: 2, userName: 'pupuninhas', points: 318, rankChange: 1 },
            { rank: 3, userName: 'el_chava', points: 295, rankChange: -1 },
            { rank: 4, userName: 'Armstrong7', points: 276, rankChange: 2 },
            { rank: 5, userName: 'indurain_93', points: 254, rankChange: -2 },
            { rank: 6, userName: 'MILON', points: 231, rankChange: null },
            { rank: 7, userName: 'M4nety', points: 208, rankChange: 1 },
            { rank: 8, userName: 'erto_bici', points: 187, rankChange: -1 },
            { rank: 9, userName: 'pedaleando23', points: 165, rankChange: 3 },
            { rank: 10, userName: 'rowan_banana', points: 142, rankChange: null },
        ],
    },
    {
        name: 'Top Clásicas',
        uciColor: '#FFD700',
        tagline: 'Explosividad y táctica. Las clásicas no perdonan errores.',
        leaderboard: [
            { rank: 1, userName: 'Armstrong7', points: 678, rankChange: null },
            { rank: 2, userName: 'MILON', points: 645, rankChange: 1 },
            { rank: 3, userName: 'el_chava', points: 612, rankChange: -1 },
            { rank: 4, userName: 'M4nety', points: 587, rankChange: 2 },
            { rank: 5, userName: 'Gepalgo', points: 554, rankChange: 3 },
            { rank: 6, userName: 'indurain_93', points: 523, rankChange: -2 },
            { rank: 7, userName: 'erto_bici', points: 498, rankChange: 1 },
            { rank: 8, userName: 'pupuninhas', points: 467, rankChange: -1 },
            { rank: 9, userName: 'rowan_banana', points: 434, rankChange: null },
            { rank: 10, userName: 'pedaleando23', points: 401, rankChange: 2 },
        ],
    },
    {
        name: 'Major Tours',
        uciColor: '#1a1a1a',
        tagline: 'Las vueltas de una semana. Consistencia y regularidad.',
        leaderboard: [
            { rank: 1, userName: 'indurain_93', points: 892, rankChange: 1 },
            { rank: 2, userName: 'el_chava', points: 867, rankChange: -1 },
            { rank: 3, userName: 'Gepalgo', points: 834, rankChange: 4 },
            { rank: 4, userName: 'Armstrong7', points: 801, rankChange: null },
            { rank: 5, userName: 'MILON', points: 778, rankChange: -2 },
            { rank: 6, userName: 'M4nety', points: 745, rankChange: 1 },
            { rank: 7, userName: 'erto_bici', points: 712, rankChange: 2 },
            { rank: 8, userName: 'pedaleando23', points: 689, rankChange: -1 },
            { rank: 9, userName: 'pupuninhas', points: 656, rankChange: null },
            { rank: 10, userName: 'rowan_banana', points: 623, rankChange: -3 },
        ],
    },
];
