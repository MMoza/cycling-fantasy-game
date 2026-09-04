export interface ClassificationEntry {
    rank: number;
    userName: string;
    points: number;
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
        tagline: 'La carrera más bella del mundo',
        leaderboard: [
            { rank: 1, userName: 'el_chava', points: 342 },
            { rank: 2, userName: 'indurain_93', points: 289 },
            { rank: 3, userName: 'Armstrong7', points: 256 },
            { rank: 4, userName: 'MILON', points: 201 },
            { rank: 5, userName: 'M4nety', points: 178 },
            { rank: 6, userName: 'erto_bici', points: 165 },
            { rank: 7, userName: 'pedaleando23', points: 142 },
            { rank: 8, userName: 'rowan_banana', points: 128 },
            { rank: 9, userName: 'miguelito_angel', points: 115 },
            { rank: 10, userName: 'pupuninhas', points: 98 },
        ],
    },
    {
        competition: 'Paris-Roubaix',
        year: 2026,
        countryId: 'FR',
        brandColor: '#1a1a1a',
        tagline: 'El infierno del norte',
        leaderboard: [
            { rank: 1, userName: 'MILON', points: 120 },
            { rank: 2, userName: 'el_chava', points: 98 },
            { rank: 3, userName: 'M4nety', points: 87 },
            { rank: 4, userName: 'indurain_93', points: 74 },
            { rank: 5, userName: 'pupuninhas', points: 65 },
            { rank: 6, userName: 'Armstrong7', points: 58 },
            { rank: 7, userName: 'erto_bici', points: 52 },
            { rank: 8, userName: 'pedaleando23', points: 45 },
            { rank: 9, userName: 'rowan_banana', points: 38 },
            { rank: 10, userName: 'miguelito_angel', points: 30 },
        ],
    },
    {
        competition: 'Tour de France',
        year: 2026,
        countryId: 'FR',
        brandColor: '#FFD700',
        tagline: 'La Grande Boucle',
        leaderboard: [
            { rank: 1, userName: 'Armstrong7', points: 487 },
            { rank: 2, userName: 'el_chava', points: 412 },
            { rank: 3, userName: 'indurain_93', points: 378 },
            { rank: 4, userName: 'erto_bici', points: 345 },
            { rank: 5, userName: 'MILON', points: 298 },
            { rank: 6, userName: 'M4nety', points: 276 },
            { rank: 7, userName: 'miguelito_angel', points: 254 },
            { rank: 8, userName: 'pedaleando23', points: 231 },
            { rank: 9, userName: 'rowan_banana', points: 198 },
            { rank: 10, userName: 'pupuninhas', points: 175 },
        ],
    },
    {
        competition: 'Strade Bianche',
        year: 2026,
        countryId: 'IT',
        brandColor: '#C2956B',
        tagline: 'Los caminos blancos de Toscana',
        leaderboard: [
            { rank: 1, userName: 'erto_bici', points: 85 },
            { rank: 2, userName: 'Armstrong7', points: 72 },
            { rank: 3, userName: 'MILON', points: 68 },
            { rank: 4, userName: 'el_chava', points: 54 },
            { rank: 5, userName: 'indurain_93', points: 47 },
            { rank: 6, userName: 'M4nety', points: 42 },
            { rank: 7, userName: 'rowan_banana', points: 38 },
            { rank: 8, userName: 'pupuninhas', points: 33 },
            { rank: 9, userName: 'miguelito_angel', points: 28 },
            { rank: 10, userName: 'pedaleando23', points: 22 },
        ],
    },
    {
        competition: 'Vuelta a España',
        year: 2026,
        countryId: 'ES',
        brandColor: '#E10600',
        tagline: 'La roja',
        leaderboard: [
            { rank: 1, userName: 'pupuninhas', points: 398 },
            { rank: 2, userName: 'erto_bici', points: 367 },
            { rank: 3, userName: 'el_chava', points: 312 },
            { rank: 4, userName: 'M4nety', points: 276 },
            { rank: 5, userName: 'indurain_93', points: 245 },
            { rank: 6, userName: 'Armstrong7', points: 223 },
            { rank: 7, userName: 'MILON', points: 198 },
            { rank: 8, userName: 'miguelito_angel', points: 176 },
            { rank: 9, userName: 'pedaleando23', points: 154 },
            { rank: 10, userName: 'rowan_banana', points: 132 },
        ],
    },
];
