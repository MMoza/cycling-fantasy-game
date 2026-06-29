<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domain\ValueObjects\StageStatus;
use App\Domain\ValueObjects\StageType;
use App\Infrastructure\Persistence\Models\CompetitionModel;
use App\Infrastructure\Persistence\Models\CompetitionParticipantModel;
use App\Infrastructure\Persistence\Models\EditionModel;
use App\Infrastructure\Persistence\Models\RiderModel;
use App\Infrastructure\Persistence\Models\TeamModel;
use App\Infrastructure\Persistence\Models\TeamRosterModel;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TourDeFrance2026Seeder extends Seeder
{
    private const YEAR = 2026;

    private array $teamIds = [];

    private array $riderIds = [];

    public function run(): void
    {
        $edition = EditionModel::whereHas('competition', fn ($q) => $q->where('name', 'Tour de Francia'))
            ->where('year', self::YEAR)
            ->first();

        if (! $edition) {
            return;
        }

        $this->createTeams();
        $this->createRosters();
        $this->createParticipants($edition->id);
        $this->createStages($edition->id);
    }

    private function createTeams(): void
    {
        $teams = [
            ['name' => 'Alpecin - Premier Tech', 'abbr' => 'ADC', 'country' => 'BE'],
            ['name' => 'Bahrain - Victorious', 'abbr' => 'TBV', 'country' => 'BH'],
            ['name' => 'Decathlon CMA CGM Team', 'abbr' => 'DCM', 'country' => 'FR'],
            ['name' => 'EF Education - EasyPost', 'abbr' => 'EFE', 'country' => 'US'],
            ['name' => 'Groupama - FDJ United', 'abbr' => 'GFC', 'country' => 'FR'],
            ['name' => 'Netcompany INEOS', 'abbr' => 'IGD', 'country' => 'GB'],
            ['name' => 'Lidl - Trek', 'abbr' => 'LTK', 'country' => 'US'],
            ['name' => 'Lotto Intermarché', 'abbr' => 'LTD', 'country' => 'BE'],
            ['name' => 'Movistar Team', 'abbr' => 'MOV', 'country' => 'ES'],
            ['name' => 'NSN Cycling Team', 'abbr' => 'NSN', 'country' => 'NL'],
            ['name' => 'Red Bull - BORA - hansgrohe', 'abbr' => 'RBH', 'country' => 'DE'],
            ['name' => 'Soudal Quick-Step', 'abbr' => 'SOQ', 'country' => 'BE'],
            ['name' => 'Team Jayco AlUla', 'abbr' => 'JAY', 'country' => 'AU'],
            ['name' => 'Team Picnic PostNL', 'abbr' => 'TPP', 'country' => 'NL'],
            ['name' => 'Team Visma | Lease a Bike', 'abbr' => 'TVL', 'country' => 'NL'],
            ['name' => 'UAE Team Emirates - XRG', 'abbr' => 'UAD', 'country' => 'AE'],
            ['name' => 'Uno-X Mobility', 'abbr' => 'UXM', 'country' => 'NO'],
            ['name' => 'XDS Astana Team', 'abbr' => 'XAT', 'country' => 'KZ'],
            ['name' => 'Cofidis', 'abbr' => 'COF', 'country' => 'FR'],
            ['name' => 'Tudor Pro Cycling Team', 'abbr' => 'TUD', 'country' => 'CH'],
            ['name' => 'TotalEnergies', 'abbr' => 'TEN', 'country' => 'FR'],
            ['name' => 'Caja Rural - Seguros RGA', 'abbr' => 'CJR', 'country' => 'ES'],
            ['name' => 'Pinarello Q36.5 Pro Cycling Team', 'abbr' => 'Q36', 'country' => 'CH'],
        ];

        foreach ($teams as $t) {
            $team = TeamModel::firstOrCreate(['name' => $t['name']], [
                'id' => Str::uuid()->toString(),
                'abbreviation' => $t['abbr'],
                'country_id' => $t['country'],
            ]);

            $this->teamIds[$t['abbr']] = $team->id;
        }
    }

    private function createRosters(): void
    {
        $ridersByTeam = [
            'ADC' => [
                ['first' => 'Jasper', 'last' => 'Philipsen', 'country' => 'BE'],
                ['first' => 'Mathieu', 'last' => 'van der Poel', 'country' => 'NL'],
                ['first' => 'Kaden', 'last' => 'Groves', 'country' => 'AU'],
                ['first' => 'Emiel', 'last' => 'Verstrynge', 'country' => 'BE'],
                ['first' => 'Jonas', 'last' => 'Rickaert', 'country' => 'BE'],
            ],
            'TBV' => [
                ['first' => 'Antonio', 'last' => 'Tiberi', 'country' => 'IT'],
                ['first' => 'Lenny', 'last' => 'Martinez', 'country' => 'FR'],
                ['first' => 'Matej', 'last' => 'Mohorič', 'country' => 'SI'],
                ['first' => 'Phil', 'last' => 'Bauhaus', 'country' => 'DE'],
                ['first' => 'Damiano', 'last' => 'Caruso', 'country' => 'IT'],
                ['first' => 'Kamil', 'last' => 'Gradek', 'country' => 'PL'],
                ['first' => 'Robert', 'last' => 'Stannard', 'country' => 'NZ'],
                ['first' => 'Vlad', 'last' => 'Van Mechelen', 'country' => 'BE'],
            ],
            'DCM' => [
                ['first' => 'Olav', 'last' => 'Kooij', 'country' => 'NL'],
                ['first' => 'Tiesj', 'last' => 'Benoot', 'country' => 'BE'],
                ['first' => 'Daan', 'last' => 'Hoole', 'country' => 'NL'],
                ['first' => 'Stefan', 'last' => 'Bissegger', 'country' => 'CH'],
                ['first' => 'Paul', 'last' => 'Seixas', 'country' => 'FR'],
            ],
            'EFE' => [
                ['first' => 'Ben', 'last' => 'Healy', 'country' => 'IE'],
                ['first' => 'Kasper', 'last' => 'Asgreen', 'country' => 'DK'],
                ['first' => 'Richard', 'last' => 'Carapaz', 'country' => 'EC'],
                ['first' => 'Alex', 'last' => 'Baudin', 'country' => 'FR'],
            ],
            'GFC' => [
                ['first' => 'David', 'last' => 'Gaudu', 'country' => 'FR'],
                ['first' => 'Guillaume', 'last' => 'Martin', 'country' => 'FR'],
                ['first' => 'Romain', 'last' => 'Grégoire', 'country' => 'FR'],
            ],
            'IGD' => [
                ['first' => 'Filippo', 'last' => 'Ganna', 'country' => 'IT'],
                ['first' => 'Carlos', 'last' => 'Rodríguez', 'country' => 'ES'],
                ['first' => 'Michał', 'last' => 'Kwiatkowski', 'country' => 'PL'],
                ['first' => 'Kévin', 'last' => 'Vauquelin', 'country' => 'FR'],
                ['first' => 'Dorian', 'last' => 'Godon', 'country' => 'FR'],
                ['first' => 'Thymen', 'last' => 'Arensman', 'country' => 'NL'],
            ],
            'LTK' => [
                ['first' => 'Giulio', 'last' => 'Ciccone', 'country' => 'IT'],
                ['first' => 'Juan', 'last' => 'Ayuso', 'country' => 'ES'],
                ['first' => 'Mads', 'last' => 'Pedersen', 'country' => 'DK'],
                ['first' => 'Mathias', 'last' => 'Vacek', 'country' => 'CZ'],
                ['first' => 'Mattias', 'last' => 'Skjelmose', 'country' => 'DK'],
                ['first' => 'Quinn', 'last' => 'Simmons', 'country' => 'US'],
                ['first' => 'Søren', 'last' => 'Kragh Andersen', 'country' => 'DK'],
            ],
            'LTD' => [
                ['first' => 'Arnaud', 'last' => 'De Lie', 'country' => 'BE'],
                ['first' => 'Lennert', 'last' => 'Van Eetvelt', 'country' => 'BE'],
                ['first' => 'Georg', 'last' => 'Zimmermann', 'country' => 'DE'],
                ['first' => 'Huub', 'last' => 'Artz', 'country' => 'NL'],
            ],
            'MOV' => [
                ['first' => 'Cian', 'last' => 'Uijtdebroeks', 'country' => 'BE'],
                ['first' => 'Einer', 'last' => 'Rubio', 'country' => 'CO'],
                ['first' => 'Pablo', 'last' => 'Castrillo', 'country' => 'ES'],
                ['first' => 'Roger', 'last' => 'Adrià', 'country' => 'ES'],
            ],
            'NSN' => [
                ['first' => 'Biniam', 'last' => 'Girmay', 'country' => 'ER'],
                ['first' => 'Jake', 'last' => 'Stewart', 'country' => 'GB'],
                ['first' => 'Lewis', 'last' => 'Askey', 'country' => 'GB'],
                ['first' => 'Krists', 'last' => 'Neilands', 'country' => 'LV'],
                ['first' => 'Marco', 'last' => 'Frigo', 'country' => 'IT'],
                ['first' => 'Matis', 'last' => 'Louvel', 'country' => 'FR'],
                ['first' => 'George', 'last' => 'Bennett', 'country' => 'NZ'],
                ['first' => 'Tom', 'last' => 'Van Asbroeck', 'country' => 'BE'],
            ],
            'RBH' => [
                ['first' => 'Remco', 'last' => 'Evenepoel', 'country' => 'BE'],
                ['first' => 'Florian', 'last' => 'Lipowitz', 'country' => 'DE'],
                ['first' => 'Jai', 'last' => 'Hindley', 'country' => 'AU'],
                ['first' => 'Nico', 'last' => 'Denz', 'country' => 'DE'],
                ['first' => 'Mattia', 'last' => 'Cattaneo', 'country' => 'IT'],
                ['first' => 'Jan', 'last' => 'Tratnik', 'country' => 'SI'],
                ['first' => 'Maxim', 'last' => 'Van Gils', 'country' => 'BE'],
                ['first' => 'Tim', 'last' => 'Van Dijke', 'country' => 'NL'],
            ],
            'SOQ' => [
                ['first' => 'Mikel', 'last' => 'Landa', 'country' => 'ES'],
                ['first' => 'Jasper', 'last' => 'Stuyven', 'country' => 'BE'],
                ['first' => 'Tim', 'last' => 'Merlier', 'country' => 'BE'],
                ['first' => 'Valentin', 'last' => 'Paret-Peintre', 'country' => 'FR'],
                ['first' => 'Ilan', 'last' => 'Van Wilder', 'country' => 'BE'],
                ['first' => 'Louis', 'last' => 'Vervaecke', 'country' => 'BE'],
                ['first' => 'Dylan', 'last' => 'Van Baarle', 'country' => 'NL'],
                ['first' => 'Bert', 'last' => 'Van Lerberghe', 'country' => 'BE'],
            ],
            'JAY' => [
                ['first' => 'Michael', 'last' => 'Matthews', 'country' => 'AU'],
                ['first' => 'Luke', 'last' => 'Plapp', 'country' => 'AU'],
                ['first' => 'Pascal', 'last' => 'Ackermann', 'country' => 'DE'],
                ['first' => 'Ben', 'last' => 'O\'Connor', 'country' => 'AU'],
                ['first' => 'Mauro', 'last' => 'Schmid', 'country' => 'CH'],
                ['first' => 'Kelland', 'last' => 'O\'Brien', 'country' => 'AU'],
                ['first' => 'Felix', 'last' => 'Engelhardt', 'country' => 'DE'],
                ['first' => 'Luke', 'last' => 'Durbridge', 'country' => 'AU'],
            ],
            'TPP' => [
                ['first' => 'Pavel', 'last' => 'Bittner', 'country' => 'CZ'],
                ['first' => 'Warren', 'last' => 'Barguil', 'country' => 'FR'],
                ['first' => 'Frank', 'last' => 'Van den Broek', 'country' => 'NL'],
            ],
            'TVL' => [
                ['first' => 'Jonas', 'last' => 'Vingegaard', 'country' => 'DK'],
                ['first' => 'Matteo', 'last' => 'Jorgenson', 'country' => 'US'],
                ['first' => 'Sepp', 'last' => 'Kuss', 'country' => 'US'],
                ['first' => 'Victor', 'last' => 'Campenaerts', 'country' => 'BE'],
                ['first' => 'Edoardo', 'last' => 'Affini', 'country' => 'IT'],
                ['first' => 'Per Strand', 'last' => 'Hagenes', 'country' => 'NO'],
                ['first' => 'Bruno', 'last' => 'Armirail', 'country' => 'FR'],
                ['first' => 'Davide', 'last' => 'Piganzoli', 'country' => 'IT'],
            ],
            'UAD' => [
                ['first' => 'Tadej', 'last' => 'Pogačar', 'country' => 'SI'],
                ['first' => 'Isaac', 'last' => 'Del Toro', 'country' => 'MX'],
                ['first' => 'Adam', 'last' => 'Yates', 'country' => 'GB'],
                ['first' => 'Tim', 'last' => 'Wellens', 'country' => 'BE'],
                ['first' => 'Brandon', 'last' => 'McNulty', 'country' => 'US'],
                ['first' => 'Florian', 'last' => 'Vermeersch', 'country' => 'BE'],
                ['first' => 'Nils', 'last' => 'Politt', 'country' => 'DE'],
            ],
            'UXM' => [
                ['first' => 'Magnus', 'last' => 'Cort', 'country' => 'DK'],
                ['first' => 'Tobias Halland', 'last' => 'Johannessen', 'country' => 'NO'],
                ['first' => 'Anders', 'last' => 'Skaarseth', 'country' => 'NO'],
                ['first' => 'Søren', 'last' => 'Wærenskjold', 'country' => 'NO'],
                ['first' => 'Anthon', 'last' => 'Charmig', 'country' => 'DK'],
                ['first' => 'Jonas', 'last' => 'Abrahamsen', 'country' => 'NO'],
                ['first' => 'Torstein', 'last' => 'Træen', 'country' => 'NO'],
                ['first' => 'Anders Halland', 'last' => 'Johannessen', 'country' => 'NO'],
            ],
            'XAT' => [
                ['first' => 'Harold', 'last' => 'Tejada', 'country' => 'CO'],
                ['first' => 'Sergio', 'last' => 'Higuita', 'country' => 'CO'],
                ['first' => 'Lorenzo', 'last' => 'Fortunato', 'country' => 'IT'],
                ['first' => 'Max', 'last' => 'Kanter', 'country' => 'DE'],
                ['first' => 'Nicolas', 'last' => 'Vinokurov', 'country' => 'KZ'],
                ['first' => 'Mike', 'last' => 'Teunissen', 'country' => 'NL'],
            ],
            'COF' => [
                ['first' => 'Ion', 'last' => 'Izagirre', 'country' => 'ES'],
                ['first' => 'Alex', 'last' => 'Aranburu', 'country' => 'ES'],
                ['first' => 'Emanuel', 'last' => 'Buchmann', 'country' => 'DE'],
                ['first' => 'Alex', 'last' => 'Kirsch', 'country' => 'LU'],
                ['first' => 'Hugo', 'last' => 'Page', 'country' => 'FR'],
                ['first' => 'Milan', 'last' => 'Fretin', 'country' => 'BE'],
                ['first' => 'Piet', 'last' => 'Allegaert', 'country' => 'BE'],
                ['first' => 'Jenthe', 'last' => 'Biermans', 'country' => 'BE'],
                ['first' => 'Benjamin', 'last' => 'Thomas', 'country' => 'FR'],
            ],
            'TUD' => [
                ['first' => 'Julian', 'last' => 'Alaphilippe', 'country' => 'FR'],
                ['first' => 'Michael', 'last' => 'Storer', 'country' => 'AU'],
                ['first' => 'Matteo', 'last' => 'Trentin', 'country' => 'IT'],
                ['first' => 'Rick', 'last' => 'Pluimers', 'country' => 'NL'],
            ],
            'TEN' => [
                ['first' => 'Jordan', 'last' => 'Jegat', 'country' => 'FR'],
            ],
            'CJR' => [
                ['first' => 'Alex', 'last' => 'Molenaar', 'country' => 'NL'],
                ['first' => 'Joel', 'last' => 'Nicolau', 'country' => 'ES'],
            ],
            'Q36' => [
                ['first' => 'Tom', 'last' => 'Pidcock', 'country' => 'GB'],
                ['first' => 'Fred', 'last' => 'Wright', 'country' => 'GB'],
                ['first' => 'Quinten', 'last' => 'Hermans', 'country' => 'BE'],
            ],
        ];

        foreach ($ridersByTeam as $abbr => $riders) {
            $teamId = $this->teamIds[$abbr] ?? null;
            if (! $teamId) {
                continue;
            }

            foreach ($riders as $r) {
                $rider = RiderModel::firstOrCreate([
                    'first_name' => $r['first'],
                    'last_name' => $r['last'],
                ], [
                    'id' => Str::uuid()->toString(),
                    'country_id' => $r['country'],
                ]);

                $this->riderIds[$rider->id] = true;

                TeamRosterModel::firstOrCreate([
                    'team_id' => $teamId,
                    'rider_id' => $rider->id,
                    'year' => self::YEAR,
                ], [
                    'id' => Str::uuid()->toString(),
                ]);
            }
        }
    }

    private function createParticipants(string $editionId): void
    {
        $competition = CompetitionModel::where('name', 'Tour de Francia')->first();
        if (! $competition) {
            return;
        }

        $rosters = TeamRosterModel::where('year', self::YEAR)
            ->whereIn('team_id', array_values($this->teamIds))
            ->get();

        foreach ($rosters as $roster) {
            CompetitionParticipantModel::firstOrCreate([
                'competition_id' => $competition->id,
                'edition_id' => $editionId,
                'team_id' => $roster->team_id,
                'rider_id' => $roster->rider_id,
            ], [
                'id' => Str::uuid()->toString(),
            ]);
        }
    }

    private function createStages(string $editionId): void
    {
        $stages = [
            ['num' => 1, 'name' => 'Barcelona - Barcelona (TTT)', 'date' => '2026-07-04', 'type' => StageType::TeamTimeTrial, 'dist' => 19.4, 'origin' => 'Barcelona', 'dest' => 'Barcelona', 'diff' => 2, 'elev' => 120],
            ['num' => 2, 'name' => 'Tarragona - Barcelona', 'date' => '2026-07-05', 'type' => StageType::Flat, 'dist' => 169.2, 'origin' => 'Tarragona', 'dest' => 'Barcelona', 'diff' => 1, 'elev' => 560],
            ['num' => 3, 'name' => 'Granollers - Les Angles', 'date' => '2026-07-06', 'type' => StageType::Mountain, 'dist' => 196.2, 'origin' => 'Granollers', 'dest' => 'Les Angles', 'diff' => 3, 'elev' => 3200],
            ['num' => 4, 'name' => 'Carcassonne - Foix', 'date' => '2026-07-07', 'type' => StageType::Hill, 'dist' => 182.4, 'origin' => 'Carcassonne', 'dest' => 'Foix', 'diff' => 2, 'elev' => 2100],
            ['num' => 5, 'name' => 'Lannemezan - Pau', 'date' => '2026-07-08', 'type' => StageType::Mountain, 'dist' => 158.3, 'origin' => 'Lannemezan', 'dest' => 'Pau', 'diff' => 2, 'elev' => 2400],
            ['num' => 6, 'name' => 'Pau - Gavarnie-Gèdre', 'date' => '2026-07-09', 'type' => StageType::HighMountain, 'dist' => 186.4, 'origin' => 'Pau', 'dest' => 'Gavarnie-Gèdre', 'diff' => 3, 'elev' => 4100],
            ['num' => 7, 'name' => 'Hagetmau - Bordeaux', 'date' => '2026-07-10', 'type' => StageType::Flat, 'dist' => 175.0, 'origin' => 'Hagetmau', 'dest' => 'Bordeaux', 'diff' => 1, 'elev' => 320],
            ['num' => 8, 'name' => 'Périgueux - Bergerac', 'date' => '2026-07-11', 'type' => StageType::Flat, 'dist' => 180.0, 'origin' => 'Périgueux', 'dest' => 'Bergerac', 'diff' => 1, 'elev' => 450],
            ['num' => 9, 'name' => 'Malemort - Ussel', 'date' => '2026-07-12', 'type' => StageType::Hill, 'dist' => 185.0, 'origin' => 'Malemort', 'dest' => 'Ussel', 'diff' => 2, 'elev' => 1800],
            ['num' => 10, 'name' => 'Aurillac - Le Lioran', 'date' => '2026-07-14', 'type' => StageType::Mountain, 'dist' => 167.3, 'origin' => 'Aurillac', 'dest' => 'Le Lioran', 'diff' => 3, 'elev' => 3100],
            ['num' => 11, 'name' => 'Vichy - Nevers', 'date' => '2026-07-15', 'type' => StageType::Flat, 'dist' => 161.0, 'origin' => 'Vichy', 'dest' => 'Nevers', 'diff' => 1, 'elev' => 380],
            ['num' => 12, 'name' => 'Circuit de Nevers Magny-Cours - Chalon-sur-Saône', 'date' => '2026-07-16', 'type' => StageType::Flat, 'dist' => 179.1, 'origin' => 'Circuit de Nevers Magny-Cours', 'dest' => 'Chalon-sur-Saône', 'diff' => 1, 'elev' => 420],
            ['num' => 13, 'name' => 'Dole - Belfort', 'date' => '2026-07-17', 'type' => StageType::Hill, 'dist' => 206.2, 'origin' => 'Dole', 'dest' => 'Belfort', 'diff' => 2, 'elev' => 1900],
            ['num' => 14, 'name' => 'Mulhouse - Le Markstein', 'date' => '2026-07-18', 'type' => StageType::Mountain, 'dist' => 155.7, 'origin' => 'Mulhouse', 'dest' => 'Le Markstein', 'diff' => 3, 'elev' => 2800],
            ['num' => 15, 'name' => 'Champagnole - Plateau de Solaison', 'date' => '2026-07-19', 'type' => StageType::HighMountain, 'dist' => 184.2, 'origin' => 'Champagnole', 'dest' => 'Plateau de Solaison', 'diff' => 3, 'elev' => 3800],
            ['num' => 16, 'name' => 'Évian Les-Bains - Thonon Les-Bains (ITT)', 'date' => '2026-07-21', 'type' => StageType::TimeTrial, 'dist' => 26.0, 'origin' => 'Évian Les-Bains', 'dest' => 'Thonon Les-Bains', 'diff' => 2, 'elev' => 240],
            ['num' => 17, 'name' => 'Chambéry - Voiron', 'date' => '2026-07-22', 'type' => StageType::Hill, 'dist' => 175.0, 'origin' => 'Chambéry', 'dest' => 'Voiron', 'diff' => 2, 'elev' => 1600],
            ['num' => 18, 'name' => 'Voiron - Orcières Merlette', 'date' => '2026-07-23', 'type' => StageType::Mountain, 'dist' => 185.6, 'origin' => 'Voiron', 'dest' => 'Orcières Merlette', 'diff' => 3, 'elev' => 3400],
            ['num' => 19, 'name' => 'Gap - Alpe d\'Huez', 'date' => '2026-07-24', 'type' => StageType::HighMountain, 'dist' => 128.3, 'origin' => 'Gap', 'dest' => 'Alpe d\'Huez', 'diff' => 3, 'elev' => 3600],
            ['num' => 20, 'name' => 'Le Bourg d\'Oisans - Alpe d\'Huez', 'date' => '2026-07-25', 'type' => StageType::HighMountain, 'dist' => 172.1, 'origin' => 'Le Bourg d\'Oisans', 'dest' => 'Alpe d\'Huez', 'diff' => 3, 'elev' => 4200],
            ['num' => 21, 'name' => 'Thoiry - Paris', 'date' => '2026-07-26', 'type' => StageType::Flat, 'dist' => 132.6, 'origin' => 'Thoiry', 'dest' => 'Paris', 'diff' => 1, 'elev' => 280],
        ];

        foreach ($stages as $s) {
            $existing = DB::table('stages')
                ->where('edition_id', $editionId)
                ->where('number', $s['num'])
                ->exists();

            if ($existing) {
                continue;
            }

            DB::table('stages')->insert([
                'id' => Str::uuid()->toString(),
                'edition_id' => $editionId,
                'number' => $s['num'],
                'name' => $s['name'],
                'date' => $s['date'],
                'type' => $s['type']->value,
                'distance' => $s['dist'],
                'origin' => $s['origin'],
                'destination' => $s['dest'],
                'difficulty' => $s['diff'],
                'elevation_gain' => $s['elev'],
                'status' => StageStatus::Upcoming->value,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
