export type UciColor = 'blue' | 'red' | 'black' | 'yellow' | 'green';

export interface CalendarRace {
    name: string;
    countryId: string;
    startDate: string;
    endDate: string;
}

export interface CalendarCategory {
    name: string;
    color: UciColor;
    races: CalendarRace[];
}

const MOCK_CATEGORIES: CalendarCategory[] = [
    {
        name: 'Grandes Vueltas',
        color: 'blue',
        races: [
            { name: "Giro d'Italia", countryId: 'IT', startDate: '2026-05-09', endDate: '2026-05-31' },
            { name: 'Tour de France', countryId: 'FR', startDate: '2026-07-04', endDate: '2026-07-26' },
            { name: 'Vuelta a España', countryId: 'ES', startDate: '2026-08-22', endDate: '2026-09-13' },
        ],
    },
    {
        name: 'Monumentos',
        color: 'red',
        races: [
            { name: 'Milano-Sanremo', countryId: 'IT', startDate: '2026-03-21', endDate: '2026-03-21' },
            { name: 'Ronde van Vlaanderen', countryId: 'BE', startDate: '2026-04-05', endDate: '2026-04-05' },
            { name: 'Paris-Roubaix', countryId: 'FR', startDate: '2026-04-12', endDate: '2026-04-12' },
            { name: 'Liège-Bastogne-Liège', countryId: 'BE', startDate: '2026-04-26', endDate: '2026-04-26' },
            { name: 'Il Lombardia', countryId: 'IT', startDate: '2026-10-10', endDate: '2026-10-10' },
        ],
    },
    {
        name: 'Major Tours',
        color: 'black',
        races: [
            { name: 'Paris-Nice', countryId: 'FR', startDate: '2026-03-08', endDate: '2026-03-15' },
            { name: 'Tirreno-Adriatico', countryId: 'IT', startDate: '2026-03-09', endDate: '2026-03-15' },
            { name: 'Volta a Catalunya', countryId: 'ES', startDate: '2026-03-23', endDate: '2026-03-29' },
            { name: 'Itzulia Basque Country', countryId: 'ES', startDate: '2026-04-06', endDate: '2026-04-11' },
            { name: 'Tour de Romandie', countryId: 'CH', startDate: '2026-04-28', endDate: '2026-05-03' },
            { name: 'Tour Auvergne-Rhône-Alpes', countryId: 'FR', startDate: '2026-06-07', endDate: '2026-06-14' },
            { name: 'Tour de Suisse', countryId: 'CH', startDate: '2026-06-17', endDate: '2026-06-21' },
        ],
    },
    {
        name: 'Top Clásicas',
        color: 'yellow',
        races: [
            { name: 'Omloop Nieuwsblad', countryId: 'BE', startDate: '2026-02-28', endDate: '2026-02-28' },
            { name: 'Strade Bianche', countryId: 'IT', startDate: '2026-03-07', endDate: '2026-03-07' },
            { name: 'E3 Saxo Classic', countryId: 'BE', startDate: '2026-03-27', endDate: '2026-03-27' },
            { name: 'Gent-Wevelgem', countryId: 'BE', startDate: '2026-03-29', endDate: '2026-03-29' },
            { name: 'Dwars door Vlaanderen', countryId: 'BE', startDate: '2026-04-01', endDate: '2026-04-01' },
            { name: 'Amstel Gold Race', countryId: 'NL', startDate: '2026-04-19', endDate: '2026-04-19' },
            { name: 'La Flèche Wallonne', countryId: 'BE', startDate: '2026-04-22', endDate: '2026-04-22' },
            { name: 'Eschborn-Frankfurt', countryId: 'DE', startDate: '2026-05-01', endDate: '2026-05-01' },
            { name: 'Donostia San Sebastián Klasikoa', countryId: 'ES', startDate: '2026-08-01', endDate: '2026-08-01' },
            { name: 'Bretagne Classic', countryId: 'FR', startDate: '2026-08-30', endDate: '2026-08-30' },
            { name: 'GP Québec', countryId: 'CA', startDate: '2026-09-11', endDate: '2026-09-11' },
            { name: 'GP Montréal', countryId: 'CA', startDate: '2026-09-13', endDate: '2026-09-13' },
        ],
    },
    {
        name: 'Campeonatos',
        color: 'green',
        races: [
            { name: 'World Championships', countryId: 'CA', startDate: '2026-09-20', endDate: '2026-09-27' },
        ],
    },
];

export async function getCalendarCategories(): Promise<CalendarCategory[]> {
    // TODO: replace with real API call
    // const response = await fetch('/api/calendar');
    // return response.json();
    return MOCK_CATEGORIES;
}
