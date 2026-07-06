<?php

declare(strict_types=1);

namespace App\Presentation\Console;

use App\Infrastructure\Persistence\Models\CompetitionModel;
use App\Infrastructure\Persistence\Models\CompetitionParticipantModel;
use App\Infrastructure\Persistence\Models\EditionModel;
use App\Infrastructure\Persistence\Models\RiderModel;
use App\Infrastructure\Persistence\Models\TeamModel;
use App\Infrastructure\Persistence\Models\TeamRosterModel;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SyncRidersCommand extends Command
{
    protected $signature = 'race:sync-riders';

    protected $description = 'Sync TDF 2026 startlist: riders, teams, rosters and competition participants.';

    private const YEAR = 2026;

    private array $teamIds = [];

    public function handle(): int
    {
        Log::info('race:sync-riders started');

        $edition = EditionModel::whereHas('competition', fn ($q) => $q->where('name', 'Tour de Francia'))
            ->where('year', self::YEAR)
            ->first();

        if (! $edition) {
            $this->error('Tour de Francia 2026 edition not found');

            return self::FAILURE;
        }

        $competition = CompetitionModel::where('name', 'Tour de Francia')->first();

        $this->syncTeams();
        $this->syncRiders();
        $this->syncRosters();
        $this->syncParticipants($competition->id, $edition->id);

        $this->info('Sync complete.');
        Log::info('race:sync-riders finished');

        return self::SUCCESS;
    }

    private function syncTeams(): void
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

        $this->info('Teams synced: '.count($this->teamIds));
    }

    private function syncRiders(): void
    {
        $ridersByTeam = $this->getStartlistByTeam();
        $added = 0;
        $skipped = 0;

        foreach ($ridersByTeam as $riders) {
            foreach ($riders as $r) {
                $existing = RiderModel::where('first_name', $r['first'])
                    ->where('last_name', $r['last'])
                    ->first();

                if ($existing) {
                    $skipped++;

                    continue;
                }

                RiderModel::create([
                    'id' => Str::uuid()->toString(),
                    'first_name' => $r['first'],
                    'last_name' => $r['last'],
                    'country_id' => $r['country'],
                ]);

                $this->info("  Added rider: {$r['first']} {$r['last']}");
                $added++;
            }
        }

        $this->info("Riders: {$added} added, {$skipped} skipped");
    }

    private function syncRosters(): void
    {
        $ridersByTeam = $this->getStartlistByTeam();
        $created = 0;

        foreach ($ridersByTeam as $abbr => $riders) {
            $teamId = $this->teamIds[$abbr] ?? null;
            if (! $teamId) {
                continue;
            }

            foreach ($riders as $r) {
                $rider = RiderModel::where('first_name', $r['first'])
                    ->where('last_name', $r['last'])
                    ->first();

                if (! $rider) {
                    continue;
                }

                $roster = TeamRosterModel::firstOrCreate([
                    'team_id' => $teamId,
                    'rider_id' => $rider->id,
                    'year' => self::YEAR,
                ], [
                    'id' => Str::uuid()->toString(),
                ]);

                if ($roster->wasRecentlyCreated) {
                    $created++;
                }
            }
        }

        $this->info("Rosters: {$created} new entries");
    }

    private function syncParticipants(string $competitionId, string $editionId): void
    {
        $rosters = TeamRosterModel::where('year', self::YEAR)
            ->whereIn('team_id', array_values($this->teamIds))
            ->get();

        $created = 0;

        foreach ($rosters as $roster) {
            $participant = CompetitionParticipantModel::firstOrCreate([
                'competition_id' => $competitionId,
                'edition_id' => $editionId,
                'team_id' => $roster->team_id,
                'rider_id' => $roster->rider_id,
            ], [
                'id' => Str::uuid()->toString(),
            ]);

            if ($participant->wasRecentlyCreated) {
                $created++;
            }
        }

        $this->info("Participants: {$created} new entries");
    }

    private function getStartlistByTeam(): array
    {
        return [
            'UAD' => [
                ['first' => 'Tadej', 'last' => 'Pogačar', 'country' => 'SI'],
                ['first' => 'Isaac', 'last' => 'Del Toro', 'country' => 'MX'],
                ['first' => 'Felix', 'last' => 'Großschartner', 'country' => 'AT'],
                ['first' => 'Brandon', 'last' => 'McNulty', 'country' => 'US'],
                ['first' => 'Nils', 'last' => 'Politt', 'country' => 'DE'],
                ['first' => 'Florian', 'last' => 'Vermeersch', 'country' => 'BE'],
                ['first' => 'Tim', 'last' => 'Wellens', 'country' => 'BE'],
                ['first' => 'Adam', 'last' => 'Yates', 'country' => 'GB'],
            ],
            'TVL' => [
                ['first' => 'Jonas', 'last' => 'Vingegaard', 'country' => 'DK'],
                ['first' => 'Edoardo', 'last' => 'Affini', 'country' => 'IT'],
                ['first' => 'Bruno', 'last' => 'Armirail', 'country' => 'FR'],
                ['first' => 'Victor', 'last' => 'Campenaerts', 'country' => 'BE'],
                ['first' => 'Per Strand', 'last' => 'Hagenes', 'country' => 'NO'],
                ['first' => 'Matteo', 'last' => 'Jorgenson', 'country' => 'US'],
                ['first' => 'Sepp', 'last' => 'Kuss', 'country' => 'US'],
                ['first' => 'Davide', 'last' => 'Piganzoli', 'country' => 'IT'],
            ],
            'RBH' => [
                ['first' => 'Remco', 'last' => 'Evenepoel', 'country' => 'BE'],
                ['first' => 'Mattia', 'last' => 'Cattaneo', 'country' => 'IT'],
                ['first' => 'Nico', 'last' => 'Denz', 'country' => 'DE'],
                ['first' => 'Jai', 'last' => 'Hindley', 'country' => 'AU'],
                ['first' => 'Florian', 'last' => 'Lipowitz', 'country' => 'DE'],
                ['first' => 'Jan', 'last' => 'Tratnik', 'country' => 'SI'],
                ['first' => 'Tim', 'last' => 'Van Dijke', 'country' => 'NL'],
                ['first' => 'Maxim', 'last' => 'Van Gils', 'country' => 'BE'],
            ],
            'LTK' => [
                ['first' => 'Juan', 'last' => 'Ayuso', 'country' => 'ES'],
                ['first' => 'Derek', 'last' => 'Gee', 'country' => 'CA'],
                ['first' => 'Mads', 'last' => 'Pedersen', 'country' => 'DK'],
                ['first' => 'Quinn', 'last' => 'Simmons', 'country' => 'US'],
                ['first' => 'Mattias', 'last' => 'Skjelmose', 'country' => 'DK'],
                ['first' => 'Toms', 'last' => 'Skujiņš', 'country' => 'LV'],
                ['first' => 'Mathias', 'last' => 'Vacek', 'country' => 'CZ'],
                ['first' => 'Carlos', 'last' => 'Verona', 'country' => 'ES'],
            ],
            'EFE' => [
                ['first' => 'Richard', 'last' => 'Carapaz', 'country' => 'EC'],
                ['first' => 'Kasper', 'last' => 'Asgreen', 'country' => 'DK'],
                ['first' => 'Alex', 'last' => 'Baudin', 'country' => 'FR'],
                ['first' => 'Ben', 'last' => 'Healy', 'country' => 'IE'],
                ['first' => 'Sean', 'last' => 'Quinn', 'country' => 'US'],
                ['first' => 'Georg', 'last' => 'Steinhauser', 'country' => 'DE'],
                ['first' => 'Michael', 'last' => 'Valgren', 'country' => 'DK'],
                ['first' => 'Max', 'last' => 'Walker', 'country' => 'GB'],
            ],
            'DCM' => [
                ['first' => 'Paul', 'last' => 'Seixas', 'country' => 'FR'],
                ['first' => 'Tiesj', 'last' => 'Benoot', 'country' => 'BE'],
                ['first' => 'Cees', 'last' => 'Bol', 'country' => 'NL'],
                ['first' => 'Daan', 'last' => 'Hoole', 'country' => 'NL'],
                ['first' => 'Olav', 'last' => 'Kooij', 'country' => 'NL'],
                ['first' => 'Aurélien', 'last' => 'Paret-Peintre', 'country' => 'FR'],
                ['first' => 'Nicolas', 'last' => 'Prodhomme', 'country' => 'FR'],
                ['first' => 'Matthew', 'last' => 'Riccitello', 'country' => 'US'],
            ],
            'XAT' => [
                ['first' => 'Sergio', 'last' => 'Higuita', 'country' => 'CO'],
                ['first' => 'Davide', 'last' => 'Ballerini', 'country' => 'IT'],
                ['first' => 'Aaron', 'last' => 'Gate', 'country' => 'NZ'],
                ['first' => 'Max', 'last' => 'Kanter', 'country' => 'DE'],
                ['first' => 'Harold', 'last' => 'Tejada', 'country' => 'CO'],
                ['first' => 'Mike', 'last' => 'Teunissen', 'country' => 'NL'],
                ['first' => 'Simone', 'last' => 'Velasco', 'country' => 'IT'],
                ['first' => 'Nicolas', 'last' => 'Vinokurov', 'country' => 'KZ'],
            ],
            'TBV' => [
                ['first' => 'Lenny', 'last' => 'Martinez', 'country' => 'FR'],
                ['first' => 'Phil', 'last' => 'Bauhaus', 'country' => 'DE'],
                ['first' => 'Damiano', 'last' => 'Caruso', 'country' => 'IT'],
                ['first' => 'Kamil', 'last' => 'Gradek', 'country' => 'PL'],
                ['first' => 'Matej', 'last' => 'Mohorič', 'country' => 'SI'],
                ['first' => 'Robert', 'last' => 'Stannard', 'country' => 'AU'],
                ['first' => 'Antonio', 'last' => 'Tiberi', 'country' => 'IT'],
                ['first' => 'Vlad', 'last' => 'Van Mechelen', 'country' => 'BE'],
            ],
            'IGD' => [
                ['first' => 'Egan', 'last' => 'Bernal', 'country' => 'CO'],
                ['first' => 'Thymen', 'last' => 'Arensman', 'country' => 'NL'],
                ['first' => 'Tobias', 'last' => 'Foss', 'country' => 'NO'],
                ['first' => 'Filippo', 'last' => 'Ganna', 'country' => 'IT'],
                ['first' => 'Dorian', 'last' => 'Godon', 'country' => 'FR'],
                ['first' => 'Michał', 'last' => 'Kwiatkowski', 'country' => 'PL'],
                ['first' => 'Joshua', 'last' => 'Tarling', 'country' => 'GB'],
                ['first' => 'Kévin', 'last' => 'Vauquelin', 'country' => 'FR'],
            ],
            'SOQ' => [
                ['first' => 'Tim', 'last' => 'Merlier', 'country' => 'BE'],
                ['first' => 'Pascal', 'last' => 'Eenkhoorn', 'country' => 'NL'],
                ['first' => 'Valentin', 'last' => 'Paret-Peintre', 'country' => 'FR'],
                ['first' => 'Jasper', 'last' => 'Stuyven', 'country' => 'BE'],
                ['first' => 'Dylan', 'last' => 'Van Baarle', 'country' => 'NL'],
                ['first' => 'Bert', 'last' => 'Van Lerberghe', 'country' => 'BE'],
                ['first' => 'Ilan', 'last' => 'Van Wilder', 'country' => 'BE'],
                ['first' => 'Louis', 'last' => 'Vervaeke', 'country' => 'BE'],
            ],
            'ADC' => [
                ['first' => 'Mathieu', 'last' => 'Van der Poel', 'country' => 'NL'],
                ['first' => 'Ramses', 'last' => 'Debruyne', 'country' => 'BE'],
                ['first' => 'Silvan', 'last' => 'Dillier', 'country' => 'CH'],
                ['first' => 'Tim', 'last' => 'Marsman', 'country' => 'NL'],
                ['first' => 'Jasper', 'last' => 'Philipsen', 'country' => 'BE'],
                ['first' => 'Edward', 'last' => 'Planckaert', 'country' => 'BE'],
                ['first' => 'Jonas', 'last' => 'Rickaert', 'country' => 'BE'],
                ['first' => 'Emiel', 'last' => 'Verstrynge', 'country' => 'BE'],
            ],
            'JAY' => [
                ['first' => 'Ben', 'last' => "O'Connor", 'country' => 'AU'],
                ['first' => 'Pascal', 'last' => 'Ackermann', 'country' => 'DE'],
                ['first' => 'Luke', 'last' => 'Durbridge', 'country' => 'AU'],
                ['first' => 'Felix', 'last' => 'Engelhardt', 'country' => 'DE'],
                ['first' => 'Michael', 'last' => 'Matthews', 'country' => 'AU'],
                ['first' => 'Kelland', 'last' => "O'Brien", 'country' => 'AU'],
                ['first' => 'Luke', 'last' => 'Plapp', 'country' => 'AU'],
                ['first' => 'Mauro', 'last' => 'Schmid', 'country' => 'CH'],
            ],
            'UXM' => [
                ['first' => 'Tobias Halland', 'last' => 'Johannessen', 'country' => 'NO'],
                ['first' => 'Jonas', 'last' => 'Abrahamsen', 'country' => 'NO'],
                ['first' => 'Anthon', 'last' => 'Charmig', 'country' => 'DK'],
                ['first' => 'Magnus', 'last' => 'Cort', 'country' => 'DK'],
                ['first' => 'Anders Halland', 'last' => 'Johannessen', 'country' => 'NO'],
                ['first' => 'Anders', 'last' => 'Skaarseth', 'country' => 'NO'],
                ['first' => 'Torstein', 'last' => 'Træen', 'country' => 'NO'],
                ['first' => 'Søren', 'last' => 'Wærenskjold', 'country' => 'NO'],
            ],
            'NSN' => [
                ['first' => 'Biniam', 'last' => 'Girmay', 'country' => 'ER'],
                ['first' => 'Lewis', 'last' => 'Askey', 'country' => 'GB'],
                ['first' => 'George', 'last' => 'Bennett', 'country' => 'NZ'],
                ['first' => 'Marco', 'last' => 'Frigo', 'country' => 'IT'],
                ['first' => 'Matis', 'last' => 'Louvel', 'country' => 'FR'],
                ['first' => 'Krists', 'last' => 'Neilands', 'country' => 'LV'],
                ['first' => 'Jake', 'last' => 'Stewart', 'country' => 'GB'],
                ['first' => 'Tom', 'last' => 'Van Asbroeck', 'country' => 'BE'],
            ],
            'MOV' => [
                ['first' => 'Cian', 'last' => 'Uijtdebroeks', 'country' => 'BE'],
                ['first' => 'Pablo', 'last' => 'Castrillo', 'country' => 'ES'],
                ['first' => 'Jefferson', 'last' => 'Cepeda', 'country' => 'EC'],
                ['first' => 'Raúl', 'last' => 'García Pierna', 'country' => 'ES'],
                ['first' => 'Michel', 'last' => 'Hessmann', 'country' => 'DE'],
                ['first' => 'Nelson', 'last' => 'Oliveira', 'country' => 'PT'],
                ['first' => 'Javier', 'last' => 'Romo', 'country' => 'ES'],
                ['first' => 'Einer', 'last' => 'Rubio', 'country' => 'CO'],
            ],
            'LTD' => [
                ['first' => 'Arnaud', 'last' => 'De Lie', 'country' => 'BE'],
                ['first' => 'Huub', 'last' => 'Artz', 'country' => 'NL'],
                ['first' => 'Jenno', 'last' => 'Berckmoes', 'country' => 'BE'],
                ['first' => 'Lars', 'last' => 'Craps', 'country' => 'BE'],
                ['first' => 'Liam', 'last' => 'Slock', 'country' => 'BE'],
                ['first' => 'Lennert', 'last' => 'Van Eetvelt', 'country' => 'BE'],
                ['first' => 'Baptiste', 'last' => 'Veistroffer', 'country' => 'FR'],
                ['first' => 'Georg', 'last' => 'Zimmermann', 'country' => 'DE'],
            ],
            'COF' => [
                ['first' => 'Ion', 'last' => 'Izagirre', 'country' => 'ES'],
                ['first' => 'Piet', 'last' => 'Allegaert', 'country' => 'BE'],
                ['first' => 'Alex', 'last' => 'Aranburu', 'country' => 'ES'],
                ['first' => 'Jenthe', 'last' => 'Biermans', 'country' => 'BE'],
                ['first' => 'Milan', 'last' => 'Fretin', 'country' => 'FR'],
                ['first' => 'Alex', 'last' => 'Kirsch', 'country' => 'LU'],
                ['first' => 'Hugo', 'last' => 'Page', 'country' => 'FR'],
                ['first' => 'Benjamin', 'last' => 'Thomas', 'country' => 'FR'],
            ],
            'Q36' => [
                ['first' => 'Tom', 'last' => 'Pidcock', 'country' => 'GB'],
                ['first' => 'Xabier Mikel', 'last' => 'Azparren', 'country' => 'ES'],
                ['first' => 'Chris', 'last' => 'Harper', 'country' => 'AU'],
                ['first' => 'Quinten', 'last' => 'Hermans', 'country' => 'BE'],
                ['first' => 'Damien', 'last' => 'Howson', 'country' => 'AU'],
                ['first' => 'Xandro', 'last' => 'Meurisse', 'country' => 'BE'],
                ['first' => 'Brent', 'last' => 'Van Moer', 'country' => 'BE'],
                ['first' => 'Fred', 'last' => 'Wright', 'country' => 'GB'],
            ],
            'GFC' => [
                ['first' => 'Romain', 'last' => 'Grégoire', 'country' => 'FR'],
                ['first' => 'Clément', 'last' => 'Berthet', 'country' => 'FR'],
                ['first' => 'Clément', 'last' => 'Braz Afonso', 'country' => 'FR'],
                ['first' => 'Ewen', 'last' => 'Costiou', 'country' => 'FR'],
                ['first' => 'Lorenzo', 'last' => 'Germani', 'country' => 'IT'],
                ['first' => 'Guillaume', 'last' => 'Martin', 'country' => 'FR'],
                ['first' => 'Quentin', 'last' => 'Pacher', 'country' => 'FR'],
                ['first' => 'Clément', 'last' => 'Russo', 'country' => 'FR'],
            ],
            'TUD' => [
                ['first' => 'Julian', 'last' => 'Alaphilippe', 'country' => 'FR'],
                ['first' => 'Arvid', 'last' => 'De Kleijn', 'country' => 'NL'],
                ['first' => 'Marco', 'last' => 'Haller', 'country' => 'AT'],
                ['first' => 'Marc', 'last' => 'Hirschi', 'country' => 'CH'],
                ['first' => 'Rick', 'last' => 'Pluimers', 'country' => 'NL'],
                ['first' => 'Michael', 'last' => 'Storer', 'country' => 'CH'],
                ['first' => 'Matteo', 'last' => 'Trentin', 'country' => 'IT'],
                ['first' => 'Yannis', 'last' => 'Voisard', 'country' => 'CH'],
            ],
            'TEN' => [
                ['first' => 'Jordan', 'last' => 'Jegat', 'country' => 'FR'],
                ['first' => 'Nicolas', 'last' => 'Breuillard', 'country' => 'FR'],
                ['first' => 'Joris', 'last' => 'Delbove', 'country' => 'FR'],
                ['first' => 'Alexandre', 'last' => 'Delettre', 'country' => 'FR'],
                ['first' => 'Thibault', 'last' => 'Guernalec', 'country' => 'FR'],
                ['first' => 'Mathis', 'last' => 'Le Berre', 'country' => 'FR'],
                ['first' => 'Anthony', 'last' => 'Turgis', 'country' => 'FR'],
                ['first' => 'Mattéo', 'last' => 'Vercher', 'country' => 'FR'],
            ],
            'TPP' => [
                ['first' => 'Warren', 'last' => 'Barguil', 'country' => 'FR'],
                ['first' => 'Frits', 'last' => 'Biesterbos', 'country' => 'NL'],
                ['first' => 'Pavel', 'last' => 'Bittner', 'country' => 'CZ'],
                ['first' => 'John', 'last' => 'Degenkolb', 'country' => 'DE'],
                ['first' => 'Robbe', 'last' => 'Dhondt', 'country' => 'BE'],
                ['first' => 'Niklas', 'last' => 'Märkl', 'country' => 'DE'],
                ['first' => 'Julius', 'last' => 'Van den Berg', 'country' => 'NL'],
                ['first' => 'Frank', 'last' => 'Van den Broek', 'country' => 'NL'],
            ],
            'CJR' => [
                ['first' => 'Fernando', 'last' => 'Gaviria', 'country' => 'CO'],
                ['first' => 'Abel', 'last' => 'Balderstone', 'country' => 'ES'],
                ['first' => 'Sebastian', 'last' => 'Berwick', 'country' => 'AU'],
                ['first' => 'Alex', 'last' => 'Molenaar', 'country' => 'NL'],
                ['first' => 'Joel', 'last' => 'Nicolau', 'country' => 'ES'],
                ['first' => 'Stefano', 'last' => 'Oldani', 'country' => 'IT'],
                ['first' => 'Jakub', 'last' => 'Otruba', 'country' => 'CZ'],
                ['first' => 'José Félix', 'last' => 'Parra', 'country' => 'ES'],
            ],
        ];
    }
}
