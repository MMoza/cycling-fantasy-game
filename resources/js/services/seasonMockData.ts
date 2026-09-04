export interface SeasonEntry {
    rank: number;
    userName: string;
    points: number;
    rankChange: number | null;
}

export interface SeasonSnapshot {
    label: string;
    leaderboard: SeasonEntry[];
}

export const MOCK_SEASON: SeasonSnapshot[] = [
    {
        label: 'Inicio de temporada',
        leaderboard: [
            { rank: 1, userName: 'el_chava', points: 45, rankChange: null },
            { rank: 2, userName: 'MILON', points: 38, rankChange: null },
            { rank: 3, userName: 'indurain_93', points: 32, rankChange: null },
            { rank: 4, userName: 'Armstrong7', points: 28, rankChange: null },
            { rank: 5, userName: 'Gepalgo', points: 22, rankChange: null },
            { rank: 6, userName: 'M4nety', points: 18, rankChange: null },
            { rank: 7, userName: 'erto_bici', points: 15, rankChange: null },
            { rank: 8, userName: 'pedaleando23', points: 12, rankChange: null },
            { rank: 9, userName: 'pupuninhas', points: 8, rankChange: null },
            { rank: 10, userName: 'rowan_banana', points: 5, rankChange: null },
        ],
    },
    {
        label: 'Tras el Giro',
        leaderboard: [
            { rank: 1, userName: 'el_chava', points: 387, rankChange: 0 },
            { rank: 2, userName: 'indurain_93', points: 321, rankChange: 1 },
            { rank: 3, userName: 'Gepalgo', points: 298, rankChange: 2 },
            { rank: 4, userName: 'MILON', points: 267, rankChange: -2 },
            { rank: 5, userName: 'Armstrong7', points: 234, rankChange: -1 },
            { rank: 6, userName: 'M4nety', points: 198, rankChange: 0 },
            { rank: 7, userName: 'erto_bici', points: 176, rankChange: 0 },
            { rank: 8, userName: 'pedaleando23', points: 145, rankChange: 0 },
            { rank: 9, userName: 'pupuninhas', points: 123, rankChange: 0 },
            { rank: 10, userName: 'rowan_banana', points: 98, rankChange: 0 },
        ],
    },
    {
        label: 'Tras el Tour',
        leaderboard: [
            { rank: 1, userName: 'Gepalgo', points: 854, rankChange: 2 },
            { rank: 2, userName: 'el_chava', points: 812, rankChange: -1 },
            { rank: 3, userName: 'indurain_93', points: 756, rankChange: -1 },
            { rank: 4, userName: 'MILON', points: 698, rankChange: 0 },
            { rank: 5, userName: 'Armstrong7', points: 645, rankChange: 0 },
            { rank: 6, userName: 'M4nety', points: 587, rankChange: 0 },
            { rank: 7, userName: 'erto_bici', points: 534, rankChange: 0 },
            { rank: 8, userName: 'pedaleando23', points: 478, rankChange: 0 },
            { rank: 9, userName: 'pupuninhas', points: 423, rankChange: 0 },
            { rank: 10, userName: 'rowan_banana', points: 367, rankChange: 0 },
        ],
    },
    {
        label: 'Tras la Vuelta',
        leaderboard: [
            { rank: 1, userName: 'Gepalgo', points: 1245, rankChange: 0 },
            { rank: 2, userName: 'el_chava', points: 1198, rankChange: 0 },
            { rank: 3, userName: 'MILON', points: 1134, rankChange: 1 },
            { rank: 4, userName: 'M4nety', points: 1067, rankChange: 2 },
            { rank: 5, userName: 'erto_bici', points: 987, rankChange: 2 },
            { rank: 6, userName: 'indurain_93', points: 923, rankChange: -2 },
            { rank: 7, userName: 'Armstrong7', points: 856, rankChange: -2 },
            { rank: 8, userName: 'pedaleando23', points: 789, rankChange: 0 },
            { rank: 9, userName: 'pupuninhas', points: 712, rankChange: 0 },
            { rank: 10, userName: 'rowan_banana', points: 645, rankChange: 0 },
        ],
    },
    {
        label: 'Final de temporada',
        leaderboard: [
            { rank: 1, userName: 'MILON', points: 1456, rankChange: 2 },
            { rank: 2, userName: 'Gepalgo', points: 1398, rankChange: -1 },
            { rank: 3, userName: 'M4nety', points: 1312, rankChange: 1 },
            { rank: 4, userName: 'erto_bici', points: 1245, rankChange: 1 },
            { rank: 5, userName: 'el_chava', points: 1167, rankChange: -3 },
            { rank: 6, userName: 'indurain_93', points: 1089, rankChange: 0 },
            { rank: 7, userName: 'Armstrong7', points: 1012, rankChange: 0 },
            { rank: 8, userName: 'pedaleando23', points: 934, rankChange: 0 },
            { rank: 9, userName: 'pupuninhas', points: 856, rankChange: 0 },
            { rank: 10, userName: 'rowan_banana', points: 778, rankChange: 0 },
        ],
    },
];
