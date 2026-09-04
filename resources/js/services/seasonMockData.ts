export interface SeasonEntry {
    rank: number;
    userName: string;
    points: number;
}

export interface SeasonSnapshot {
    label: string;
    leaderboard: SeasonEntry[];
}

export const MOCK_SEASON: SeasonSnapshot[] = [
    {
        label: 'Inicio de temporada',
        leaderboard: [
            { rank: 1, userName: 'el_chava', points: 45 },
            { rank: 2, userName: 'MILON', points: 38 },
            { rank: 3, userName: 'indurain_93', points: 32 },
            { rank: 4, userName: 'Armstrong7', points: 28 },
            { rank: 5, userName: 'Gepalgo', points: 22 },
            { rank: 6, userName: 'M4nety', points: 18 },
            { rank: 7, userName: 'erto_bici', points: 15 },
            { rank: 8, userName: 'pedaleando23', points: 12 },
            { rank: 9, userName: 'pupuninhas', points: 8 },
            { rank: 10, userName: 'rowan_banana', points: 5 },
        ],
    },
    {
        label: 'Tras el Giro',
        leaderboard: [
            { rank: 1, userName: 'el_chava', points: 387 },
            { rank: 2, userName: 'indurain_93', points: 321 },
            { rank: 3, userName: 'MILON', points: 298 },
            { rank: 4, userName: 'Armstrong7', points: 267 },
            { rank: 5, userName: 'Gepalgo', points: 234 },
            { rank: 6, userName: 'M4nety', points: 198 },
            { rank: 7, userName: 'erto_bici', points: 176 },
            { rank: 8, userName: 'pedaleando23', points: 145 },
            { rank: 9, userName: 'pupuninhas', points: 123 },
            { rank: 10, userName: 'rowan_banana', points: 98 },
        ],
    },
    {
        label: 'Tras el Tour',
        leaderboard: [
            { rank: 1, userName: 'Armstrong7', points: 854 },
            { rank: 2, userName: 'el_chava', points: 812 },
            { rank: 3, userName: 'indurain_93', points: 756 },
            { rank: 4, userName: 'Gepalgo', points: 698 },
            { rank: 5, userName: 'MILON', points: 645 },
            { rank: 6, userName: 'M4nety', points: 587 },
            { rank: 7, userName: 'erto_bici', points: 534 },
            { rank: 8, userName: 'pedaleando23', points: 478 },
            { rank: 9, userName: 'pupuninhas', points: 423 },
            { rank: 10, userName: 'rowan_banana', points: 367 },
        ],
    },
    {
        label: 'Tras la Vuelta',
        leaderboard: [
            { rank: 1, userName: 'el_chava', points: 1245 },
            { rank: 2, userName: 'Armstrong7', points: 1198 },
            { rank: 3, userName: 'indurain_93', points: 1134 },
            { rank: 4, userName: 'Gepalgo', points: 1067 },
            { rank: 5, userName: 'MILON', points: 987 },
            { rank: 6, userName: 'M4nety', points: 923 },
            { rank: 7, userName: 'erto_bici', points: 856 },
            { rank: 8, userName: 'pedaleando23', points: 789 },
            { rank: 9, userName: 'pupuninhas', points: 712 },
            { rank: 10, userName: 'rowan_banana', points: 645 },
        ],
    },
    {
        label: 'Final de temporada',
        leaderboard: [
            { rank: 1, userName: 'el_chava', points: 1456 },
            { rank: 2, userName: 'Armstrong7', points: 1398 },
            { rank: 3, userName: 'indurain_93', points: 1312 },
            { rank: 4, userName: 'Gepalgo', points: 1245 },
            { rank: 5, userName: 'MILON', points: 1167 },
            { rank: 6, userName: 'M4nety', points: 1089 },
            { rank: 7, userName: 'erto_bici', points: 1012 },
            { rank: 8, userName: 'pedaleando23', points: 934 },
            { rank: 9, userName: 'pupuninhas', points: 856 },
            { rank: 10, userName: 'rowan_banana', points: 778 },
        ],
    },
];
