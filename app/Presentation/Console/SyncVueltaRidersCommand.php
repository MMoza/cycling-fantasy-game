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

class SyncVueltaRidersCommand extends Command
{
    protected $signature = 'race:sync-vuelta-riders {--force : Reset participants to official startlist}';

    protected $description = 'Sync Vuelta a España 2026 startlist: riders, teams, rosters and competition participants.';

    private const YEAR = 2026;

    private array $teamIds = [];

    public function handle(): int
    {
        Log::info('race:sync-vuelta-riders started');

        $edition = EditionModel::whereHas('competition', fn ($q) => $q->where('name', 'La Vuelta'))
            ->where('year', self::YEAR)
            ->first();

        if (! $edition) {
            $this->error('La Vuelta 2026 edition not found');

            return self::FAILURE;
        }

        $competition = CompetitionModel::where('name', 'La Vuelta')->first();

        $this->syncTeams();
        $this->syncRiders();
        $this->syncRosters();
        $this->syncParticipants($competition->id, $edition->id);

        $this->info('Sync complete.');
        Log::info('race:sync-vuelta-riders finished');

        return self::SUCCESS;
    }

    private function syncTeams(): void
    {
        $teams = [
            ['name' => 'Alpecin - Premier Tech', 'abbr' => 'ADC', 'country' => 'BE'],
            ['name' => 'Bahrain - Victorious', 'abbr' => 'TBV', 'country' => 'BH'],
            ['name' => 'Burgos Burpellet BH', 'abbr' => 'BBH', 'country' => 'ES'],
            ['name' => 'Cofidis', 'abbr' => 'COF', 'country' => 'FR'],
            ['name' => 'Decathlon CMA CGM Team', 'abbr' => 'DCM', 'country' => 'FR'],
            ['name' => 'EF Education - EasyPost', 'abbr' => 'EFE', 'country' => 'US'],
            ['name' => 'Equipo Kern Pharma', 'abbr' => 'EKP', 'country' => 'ES'],
            ['name' => 'Groupama - FDJ United', 'abbr' => 'GFC', 'country' => 'FR'],
            ['name' => 'Lidl - Trek', 'abbr' => 'LTK', 'country' => 'US'],
            ['name' => 'Lotto Intermarché', 'abbr' => 'LTD', 'country' => 'BE'],
            ['name' => 'Movistar Team', 'abbr' => 'MOV', 'country' => 'ES'],
            ['name' => 'Netcompany INEOS', 'abbr' => 'IGD', 'country' => 'GB'],
            ['name' => 'NSN Cycling Team', 'abbr' => 'NSN', 'country' => 'NL'],
            ['name' => 'Pinarello Q36.5 Pro Cycling Team', 'abbr' => 'Q36', 'country' => 'CH'],
            ['name' => 'Red Bull - BORA - hansgrohe', 'abbr' => 'RBH', 'country' => 'DE'],
            ['name' => 'Soudal Quick-Step', 'abbr' => 'SOQ', 'country' => 'BE'],
            ['name' => 'Team Jayco AlUla', 'abbr' => 'JAY', 'country' => 'AU'],
            ['name' => 'Team Picnic PostNL', 'abbr' => 'TPP', 'country' => 'NL'],
            ['name' => 'Team Visma | Lease a Bike', 'abbr' => 'TVL', 'country' => 'NL'],
            ['name' => 'Tudor Pro Cycling Team', 'abbr' => 'TUD', 'country' => 'CH'],
            ['name' => 'UAE Team Emirates - XRG', 'abbr' => 'UAD', 'country' => 'AE'],
            ['name' => 'Uno-X Mobility', 'abbr' => 'UXM', 'country' => 'NO'],
            ['name' => 'XDS Astana Team', 'abbr' => 'XAT', 'country' => 'KZ'],
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
        if ($this->option('force')) {
            $deleted = CompetitionParticipantModel::where('competition_id', $competitionId)
                ->where('edition_id', $editionId)
                ->delete();
            $this->info("Participants reset: {$deleted} removed");
        }

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
            'ADC' => [
                ['first' => 'Francesco', 'last' => 'Busatto', 'country' => 'IT'],
                ['first' => 'Ramses', 'last' => 'Debruyne', 'country' => 'BE'],
                ['first' => 'Lindsay', 'last' => 'De Vylder', 'country' => 'BE'],
                ['first' => 'Gal', 'last' => 'Glivar', 'country' => 'SI'],
                ['first' => 'Michael', 'last' => 'Gogl', 'country' => 'AT'],
                ['first' => 'Kaden', 'last' => 'Groves', 'country' => 'AU'],
                ['first' => 'Hugo', 'last' => 'Houle', 'country' => 'CA'],
                ['first' => 'Sente', 'last' => 'Sentjens', 'country' => 'BE'],
            ],
            'TBV' => [
                ['first' => 'Pello', 'last' => 'Bilbao', 'country' => 'ES'],
                ['first' => 'Santiago', 'last' => 'Buitrago', 'country' => 'CO'],
                ['first' => 'Roman', 'last' => 'Ermakov', 'country' => 'RU'],
                ['first' => 'Matevž', 'last' => 'Govekar', 'country' => 'SI'],
                ['first' => 'Pau', 'last' => 'Miquel', 'country' => 'ES'],
                ['first' => 'Jakob', 'last' => 'Omrzel', 'country' => 'SI'],
                ['first' => 'Mathijs', 'last' => 'Paasschens', 'country' => 'NL'],
                ['first' => 'Attila', 'last' => 'Valter', 'country' => 'HU'],
            ],
            'BBH' => [
                ['first' => 'Clément', 'last' => 'Alldritt', 'country' => 'FR'],
                ['first' => 'Mario', 'last' => 'Aparicio', 'country' => 'ES'],
                ['first' => 'Sergio Geovani', 'last' => 'Chumil', 'country' => 'GT'],
                ['first' => 'José Manuel', 'last' => 'Díaz', 'country' => 'ES'],
                ['first' => 'José Luis', 'last' => 'Faura', 'country' => 'ES'],
                ['first' => 'Sinuhé', 'last' => 'Fernández', 'country' => 'ES'],
                ['first' => 'Jesús', 'last' => 'Herrada', 'country' => 'ES'],
                ['first' => 'César', 'last' => 'Macías', 'country' => 'MX'],
            ],
            'COF' => [
                ['first' => 'Emanuel', 'last' => 'Buchmann', 'country' => 'DE'],
                ['first' => 'Bryan', 'last' => 'Coquard', 'country' => 'FR'],
                ['first' => 'Alex', 'last' => 'Kirsch', 'country' => 'LU'],
                ['first' => 'Sylvain', 'last' => 'Moniquet', 'country' => 'BE'],
                ['first' => 'Paul', 'last' => 'Ourselin', 'country' => 'FR'],
                ['first' => 'Alexis', 'last' => 'Renard', 'country' => 'FR'],
                ['first' => 'Louis', 'last' => 'Rouland', 'country' => 'FR'],
                ['first' => 'Sergio', 'last' => 'Samitier', 'country' => 'ES'],
            ],
            'DCM' => [
                ['first' => 'Léo', 'last' => 'Bisiaux', 'country' => 'FR'],
                ['first' => 'Oscar', 'last' => 'Chamberlain', 'country' => 'AU'],
                ['first' => 'Sander', 'last' => 'De Pestel', 'country' => 'BE'],
                ['first' => 'Felix', 'last' => 'Gall', 'country' => 'AT'],
                ['first' => 'Jordan', 'last' => 'Labrosse', 'country' => 'FR'],
                ['first' => 'Gregor', 'last' => 'Mühlberger', 'country' => 'AT'],
                ['first' => 'Matthew', 'last' => 'Riccitello', 'country' => 'US'],
                ['first' => 'Callum', 'last' => 'Scotson', 'country' => 'AU'],
            ],
            'EFE' => [
                ['first' => 'Vincenzo', 'last' => 'Albanese', 'country' => 'IT'],
                ['first' => 'Markel', 'last' => 'Beloki', 'country' => 'ES'],
                ['first' => 'Richard', 'last' => 'Carapaz', 'country' => 'EC'],
                ['first' => 'Chris', 'last' => 'Hamilton', 'country' => 'AU'],
                ['first' => 'Alastair', 'last' => 'Mackellar', 'country' => 'AU'],
                ['first' => 'Darren', 'last' => 'Rafferty', 'country' => 'IE'],
                ['first' => 'Juan Felipe', 'last' => 'Rodriguez', 'country' => 'CO'],
                ['first' => 'James', 'last' => 'Shaw', 'country' => 'GB'],
                ['first' => 'Georg', 'last' => 'Steinhauser', 'country' => 'DE'],
            ],
            'EKP' => [
                ['first' => 'Urko', 'last' => 'Berrade', 'country' => 'ES'],
                ['first' => 'Marc', 'last' => 'Brustenga', 'country' => 'ES'],
                ['first' => 'Iván', 'last' => 'Cobo', 'country' => 'ES'],
                ['first' => 'Iñigo', 'last' => 'Elosegui', 'country' => 'ES'],
                ['first' => 'Ibon', 'last' => 'Ruiz', 'country' => 'ES'],
                ['first' => 'Iván Ramiro', 'last' => 'Sosa', 'country' => 'CO'],
                ['first' => 'Diego', 'last' => 'Uriarte', 'country' => 'ES'],
                ['first' => 'Mats', 'last' => 'Wenzel', 'country' => 'LU'],
            ],
            'GFC' => [
                ['first' => 'Clément', 'last' => 'Berthet', 'country' => 'FR'],
                ['first' => 'Olivier', 'last' => 'Le Gac', 'country' => 'FR'],
                ['first' => 'Valentin', 'last' => 'Madouas', 'country' => 'FR'],
                ['first' => 'Guillaume', 'last' => 'Martin', 'country' => 'FR'],
                ['first' => 'Rudy', 'last' => 'Molard', 'country' => 'FR'],
                ['first' => 'Enzo', 'last' => 'Paleni', 'country' => 'FR'],
                ['first' => 'Rémy', 'last' => 'Rochas', 'country' => 'FR'],
                ['first' => 'Bastien', 'last' => 'Tronchon', 'country' => 'FR'],
            ],
            'LTK' => [
                ['first' => 'Julien', 'last' => 'Bernard', 'country' => 'FR'],
                ['first' => 'Lennard', 'last' => 'Kämna', 'country' => 'DE'],
                ['first' => 'Patrick', 'last' => 'Konrad', 'country' => 'AT'],
                ['first' => 'Jacopo', 'last' => 'Mosca', 'country' => 'IT'],
                ['first' => 'Mathias', 'last' => 'Norsgaard', 'country' => 'DK'],
                ['first' => 'Thibau', 'last' => 'Nys', 'country' => 'BE'],
                ['first' => 'Mads', 'last' => 'Pedersen', 'country' => 'DK'],
                ['first' => 'Mattias', 'last' => 'Skjelmose', 'country' => 'DK'],
            ],
            'LTD' => [
                ['first' => 'Vito', 'last' => 'Braet', 'country' => 'BE'],
                ['first' => 'Lars', 'last' => 'Craps', 'country' => 'BE'],
                ['first' => 'Steffen', 'last' => 'De Schuyteneer', 'country' => 'BE'],
                ['first' => 'Lorenzo', 'last' => 'Rota', 'country' => 'IT'],
                ['first' => 'Reuben', 'last' => 'Thompson', 'country' => 'NZ'],
                ['first' => 'Luca', 'last' => 'Van Boven', 'country' => 'BE'],
                ['first' => 'Roel', 'last' => 'Van Sintmaartensdijk', 'country' => 'NL'],
                ['first' => 'Jarno', 'last' => 'Widar', 'country' => 'BE'],
            ],
            'MOV' => [
                ['first' => 'Orluis', 'last' => 'Aular', 'country' => 'VE'],
                ['first' => 'Jorge', 'last' => 'Arcas', 'country' => 'ES'],
                ['first' => 'Carlos', 'last' => 'Canal', 'country' => 'ES'],
                ['first' => 'Pablo', 'last' => 'Castrillo', 'country' => 'ES'],
                ['first' => 'Raúl', 'last' => 'García Pierna', 'country' => 'ES'],
                ['first' => 'Enric', 'last' => 'Mas', 'country' => 'ES'],
                ['first' => 'Iván', 'last' => 'Romeo', 'country' => 'ES'],
                ['first' => 'Cian', 'last' => 'Uijtdebroeks', 'country' => 'BE'],
            ],
            'IGD' => [
                ['first' => 'Jack', 'last' => 'Haig', 'country' => 'AU'],
                ['first' => 'Lucas', 'last' => 'Hamilton', 'country' => 'AU'],
                ['first' => 'Axel', 'last' => 'Laurance', 'country' => 'FR'],
                ['first' => 'Oscar', 'last' => 'Onley', 'country' => 'GB'],
                ['first' => 'Carlos', 'last' => 'Rodríguez', 'country' => 'ES'],
                ['first' => 'Embret', 'last' => 'Svestad-Bårdseng', 'country' => 'NO'],
                ['first' => 'Joshua', 'last' => 'Tarling', 'country' => 'GB'],
                ['first' => 'Ben', 'last' => 'Turner', 'country' => 'GB'],
            ],
            'NSN' => [
                ['first' => 'George', 'last' => 'Bennett', 'country' => 'NZ'],
                ['first' => 'Jan', 'last' => 'Hirt', 'country' => 'CZ'],
                ['first' => 'Hugo', 'last' => 'Hofstetter', 'country' => 'FR'],
                ['first' => 'Moritz', 'last' => 'Kretschy', 'country' => 'DE'],
                ['first' => 'Alexey', 'last' => 'Lutsenko', 'country' => 'KZ'],
                ['first' => 'Pau', 'last' => 'Martí', 'country' => 'ES'],
                ['first' => 'Nick', 'last' => 'Schultz', 'country' => 'AU'],
                ['first' => 'Floris', 'last' => 'Van Tricht', 'country' => 'BE'],
            ],
            'Q36' => [
                ['first' => 'Xabier Mikel', 'last' => 'Azparren', 'country' => 'ES'],
                ['first' => 'Walter', 'last' => 'Calzoni', 'country' => 'IT'],
                ['first' => 'Marcel', 'last' => 'Camprubí', 'country' => 'ES'],
                ['first' => 'David', 'last' => 'de la Cruz', 'country' => 'ES'],
                ['first' => 'Eddie', 'last' => 'Dunbar', 'country' => 'IE'],
                ['first' => 'Thomas', 'last' => 'Gloag', 'country' => 'GB'],
                ['first' => 'David', 'last' => 'González', 'country' => 'ES'],
                ['first' => 'Milan', 'last' => 'Vader', 'country' => 'NL'],
            ],
            'RBH' => [
                ['first' => 'Finn', 'last' => 'Fisher-Black', 'country' => 'NZ'],
                ['first' => 'Jordi', 'last' => 'Meeus', 'country' => 'BE'],
                ['first' => 'Gianni', 'last' => 'Moscon', 'country' => 'IT'],
                ['first' => 'Primož', 'last' => 'Roglič', 'country' => 'SI'],
                ['first' => 'Callum', 'last' => 'Thornley', 'country' => 'GB'],
                ['first' => 'Luke', 'last' => 'Tuckwell', 'country' => 'AU'],
                ['first' => 'Gianni', 'last' => 'Vermeersch', 'country' => 'BE'],
                ['first' => 'Frederik', 'last' => 'Wandahl', 'country' => 'DK'],
            ],
            'SOQ' => [
                ['first' => 'Alberto', 'last' => 'Dainese', 'country' => 'IT'],
                ['first' => 'Gianmarco', 'last' => 'Garofoli', 'country' => 'IT'],
                ['first' => 'Ethan', 'last' => 'Hayter', 'country' => 'GB'],
                ['first' => 'Mikel', 'last' => 'Landa', 'country' => 'ES'],
                ['first' => 'Valentin', 'last' => 'Paret-Peintre', 'country' => 'FR'],
                ['first' => 'Fabio', 'last' => 'Van Den Bossche', 'country' => 'BE'],
                ['first' => 'Mauri', 'last' => 'Vansevenant', 'country' => 'BE'],
                ['first' => 'Filippo', 'last' => 'Zana', 'country' => 'IT'],
            ],
            'JAY' => [
                ['first' => 'Koen', 'last' => 'Bouwman', 'country' => 'NL'],
                ['first' => 'Alessandro', 'last' => 'Covi', 'country' => 'IT'],
                ['first' => 'Paul', 'last' => 'Double', 'country' => 'GB'],
                ['first' => 'Asbjørn', 'last' => 'Hellemose', 'country' => 'DK'],
                ['first' => 'Hamish', 'last' => 'McKenzie', 'country' => 'NZ'],
                ['first' => 'Finlay', 'last' => 'Pickering', 'country' => 'GB'],
                ['first' => 'Rudy', 'last' => 'Porter', 'country' => 'AU'],
                ['first' => 'Jasha', 'last' => 'Sütterlin', 'country' => 'DE'],
            ],
            'TPP' => [
                ['first' => 'Timo', 'last' => 'De Jong', 'country' => 'NL'],
                ['first' => 'Mattia', 'last' => 'Gaffuri', 'country' => 'IT'],
                ['first' => 'Chris', 'last' => 'Hamilton', 'country' => 'AU'],
                ['first' => 'Gijs', 'last' => 'Leemreize', 'country' => 'NL'],
                ['first' => 'Juan Guillermo', 'last' => 'Martinez', 'country' => 'CO'],
                ['first' => 'Oliver', 'last' => 'Peace', 'country' => 'GB'],
                ['first' => 'Henri-François', 'last' => 'Renard-Haquin', 'country' => 'FR'],
                ['first' => 'Timo', 'last' => 'Roosen', 'country' => 'NL'],
            ],
            'TVL' => [
                ['first' => 'Bruno', 'last' => 'Armirail', 'country' => 'FR'],
                ['first' => 'Matthew', 'last' => 'Brennan', 'country' => 'GB'],
                ['first' => 'Steven', 'last' => 'Kruijswijk', 'country' => 'NL'],
                ['first' => 'Sepp', 'last' => 'Kuss', 'country' => 'US'],
                ['first' => 'Christophe', 'last' => 'Laporte', 'country' => 'FR'],
                ['first' => 'Jørgen', 'last' => 'Nordhagen', 'country' => 'NO'],
                ['first' => 'Ben', 'last' => 'Tulett', 'country' => 'GB'],
                ['first' => 'Wout', 'last' => 'van Aert', 'country' => 'BE'],
            ],
            'TUD' => [
                ['first' => 'Will', 'last' => 'Barta', 'country' => 'US'],
                ['first' => 'Marco', 'last' => 'Brenner', 'country' => 'DE'],
                ['first' => 'Arthur', 'last' => 'Kluckers', 'country' => 'LU'],
                ['first' => 'Stefan', 'last' => 'Küng', 'country' => 'CH'],
                ['first' => 'Roland', 'last' => 'Thalmann', 'country' => 'CH'],
                ['first' => 'Larry', 'last' => 'Warbasse', 'country' => 'US'],
                ['first' => 'Fabian', 'last' => 'Weiss', 'country' => 'CH'],
                ['first' => 'Hannes', 'last' => 'Wilksch', 'country' => 'DE'],
            ],
            'UAD' => [
                ['first' => 'João', 'last' => 'Almeida', 'country' => 'PT'],
                ['first' => 'Domen', 'last' => 'Novak', 'country' => 'SI'],
                ['first' => 'Ivo', 'last' => 'Oliveira', 'country' => 'PT'],
                ['first' => 'Tadej', 'last' => 'Pogačar', 'country' => 'SI'],
                ['first' => 'Pavel', 'last' => 'Sivakov', 'country' => 'FR'],
                ['first' => 'Pablo', 'last' => 'Torres', 'country' => 'ES'],
                ['first' => 'Kevin', 'last' => 'Vermaerke', 'country' => 'US'],
                ['first' => 'Jay', 'last' => 'Vine', 'country' => 'AU'],
            ],
            'UXM' => [
                ['first' => 'Magnus', 'last' => 'Cort', 'country' => 'DK'],
                ['first' => 'Simon', 'last' => 'Dalby', 'country' => 'DK'],
                ['first' => 'Fredrik', 'last' => 'Dversnes Lavik', 'country' => 'NO'],
                ['first' => 'Tobias Halland', 'last' => 'Johannessen', 'country' => 'NO'],
                ['first' => 'Andreas', 'last' => 'Kron', 'country' => 'DK'],
                ['first' => 'Andreas', 'last' => 'Leknessund', 'country' => 'NO'],
                ['first' => 'Rasmus', 'last' => 'Tiller', 'country' => 'NO'],
                ['first' => 'Martin', 'last' => 'Tjøtta', 'country' => 'NO'],
            ],
            'XAT' => [
                ['first' => 'Yevgeniy', 'last' => 'Fedorov', 'country' => 'KZ'],
                ['first' => 'Lorenzo', 'last' => 'Fortunato', 'country' => 'IT'],
                ['first' => 'Victor', 'last' => 'Langellotti', 'country' => 'MC'],
                ['first' => 'Henok', 'last' => 'Mulubrhan', 'country' => 'ER'],
                ['first' => 'Cristián', 'last' => 'Rodríguez', 'country' => 'ES'],
                ['first' => 'Alessandro', 'last' => 'Romele', 'country' => 'IT'],
                ['first' => 'Harold', 'last' => 'Tejada', 'country' => 'CO'],
                ['first' => 'Darren', 'last' => 'Van Bekkum', 'country' => 'NL'],
            ],
        ];
    }
}
