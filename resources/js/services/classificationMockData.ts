export interface ClassificationEntry {
    rank: number;
    userName: string;
    points: number;
    rankChange: number | null;
}

export interface ClassificationCompetition {
    competition: string;
    year: number;
    countryId: string;
    brandColor: string;
    tagline: string;
    leaderboard: ClassificationEntry[];
}

export const MOCK_CLASSIFICATIONS: ClassificationCompetition[] = [
    {
        competition: "Giro d'Italia",
        year: 2026,
        countryId: 'IT',
        brandColor: '#FFC0CB',
        tagline: 'La carrera más bella del mundo. Para los enamorados del ciclismo.',
        leaderboard: [
            { rank: 1, userName: 'el_chava', points: 342, rankChange: 2 },
            { rank: 2, userName: 'indurain_93', points: 289, rankChange: -1 },
            { rank: 3, userName: 'Armstrong7', points: 256, rankChange: null },
            { rank: 4, userName: 'MILON', points: 201, rankChange: 3 },
            { rank: 5, userName: 'M4nety', points: 178, rankChange: -2 },
            { rank: 6, userName: 'erto_bici', points: 165, rankChange: 1 },
            { rank: 7, userName: 'pedaleando23', points: 142, rankChange: null },
            { rank: 8, userName: 'rowan_banana', points: 128, rankChange: -1 },
            { rank: 9, userName: 'Gepalgo', points: 115, rankChange: null },
            { rank: 10, userName: 'pupuninhas', points: 98, rankChange: -3 },
        ],
    },
    {
        competition: 'Paris-Roubaix',
        year: 2026,
        countryId: 'FR',
        brandColor: '#1a1a1a',
        tagline: 'El infierno del norte. ¿Aguantarás el ritmo en los adoquines?',
        leaderboard: [
            { rank: 1, userName: 'MILON', points: 120, rankChange: 1 },
            { rank: 2, userName: 'el_chava', points: 98, rankChange: -1 },
            { rank: 3, userName: 'M4nety', points: 87, rankChange: 4 },
            { rank: 4, userName: 'indurain_93', points: 74, rankChange: null },
            { rank: 5, userName: 'Gepalgo', points: 65, rankChange: 5 },
            { rank: 6, userName: 'Armstrong7', points: 58, rankChange: -3 },
            { rank: 7, userName: 'erto_bici', points: 52, rankChange: 2 },
            { rank: 8, userName: 'pedaleando23', points: 45, rankChange: -1 },
            { rank: 9, userName: 'rowan_banana', points: 38, rankChange: null },
            { rank: 10, userName: 'miguelito_angel', points: 30, rankChange: -2 },
        ],
    },
    {
        competition: 'Tour de France',
        year: 2026,
        countryId: 'FR',
        brandColor: '#FFD700',
        tagline: 'La Gran Boucle. No puede faltar en tu calendario',
        leaderboard: [
            { rank: 1, userName: 'Armstrong7', points: 487, rankChange: null },
            { rank: 2, userName: 'el_chava', points: 412, rankChange: 1 },
            { rank: 3, userName: 'indurain_93', points: 378, rankChange: -2 },
            { rank: 4, userName: 'erto_bici', points: 345, rankChange: 2 },
            { rank: 5, userName: 'MILON', points: 298, rankChange: -1 },
            { rank: 6, userName: 'Gepalgo', points: 276, rankChange: 3 },
            { rank: 7, userName: 'miguelito_angel', points: 254, rankChange: -1 },
            { rank: 8, userName: 'pedaleando23', points: 231, rankChange: null },
            { rank: 9, userName: 'rowan_banana', points: 198, rankChange: -2 },
            { rank: 10, userName: 'pupuninhas', points: 175, rankChange: 1 },
        ],
    },
    {
        competition: 'Strade Bianche',
        year: 2026,
        countryId: 'IT',
        brandColor: '#C2956B',
        tagline: 'Inicia la temporada metiendo rueda en los caminos blandos de la Toscana.',
        leaderboard: [
            { rank: 1, userName: 'erto_bici', points: 85, rankChange: 3 },
            { rank: 2, userName: 'Armstrong7', points: 72, rankChange: -1 },
            { rank: 3, userName: 'MILON', points: 68, rankChange: 1 },
            { rank: 4, userName: 'el_chava', points: 54, rankChange: -2 },
            { rank: 5, userName: 'Gepalgo', points: 47, rankChange: 2 },
            { rank: 6, userName: 'M4nety', points: 42, rankChange: -1 },
            { rank: 7, userName: 'rowan_banana', points: 38, rankChange: null },
            { rank: 8, userName: 'pupuninhas', points: 33, rankChange: -3 },
            { rank: 9, userName: 'miguelito_angel', points: 28, rankChange: 1 },
            { rank: 10, userName: 'pedaleando23', points: 22, rankChange: null },
        ],
    },
    {
        competition: 'Vuelta a España',
        year: 2026,
        countryId: 'ES',
        brandColor: '#E10600',
        tagline: 'La vuelta. Para los especialistas en rampas de garage.',
        leaderboard: [
            { rank: 1, userName: 'pupuninhas', points: 398, rankChange: 2 },
            { rank: 2, userName: 'erto_bici', points: 367, rankChange: -1 },
            { rank: 3, userName: 'el_chava', points: 312, rankChange: 1 },
            { rank: 4, userName: 'Gepalgo', points: 276, rankChange: 6 },
            { rank: 5, userName: 'indurain_93', points: 245, rankChange: -2 },
            { rank: 6, userName: 'Armstrong7', points: 223, rankChange: null },
            { rank: 7, userName: 'MILON', points: 198, rankChange: -1 },
            { rank: 8, userName: 'miguelito_angel', points: 176, rankChange: 3 },
            { rank: 9, userName: 'pedaleando23', points: 154, rankChange: -1 },
            { rank: 10, userName: 'rowan_banana', points: 132, rankChange: null },
        ],
    },
];
