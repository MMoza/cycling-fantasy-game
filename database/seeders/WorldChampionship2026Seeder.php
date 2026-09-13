<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domain\ValueObjects\StageStatus;
use App\Domain\ValueObjects\StageType;
use App\Infrastructure\Persistence\Models\CompetitionModel;
use App\Infrastructure\Persistence\Models\CompetitionParticipantModel;
use App\Infrastructure\Persistence\Models\EditionModel;
use App\Infrastructure\Persistence\Models\RiderModel;
use App\Infrastructure\Persistence\Models\StageParticipantModel;
use App\Infrastructure\Persistence\Models\TeamModel;
use App\Infrastructure\Persistence\Models\TeamRosterModel;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class WorldChampionship2026Seeder extends Seeder
{
    private const YEAR = 2026;

    private array $teamIds = [];

    private array $riderIds = [];

    public function run(): void
    {
        $edition = EditionModel::whereHas('competition', fn ($q) => $q->where('name', 'Mundial 2026'))
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
            ['name' => 'Bélgica', 'abbr' => 'BEL', 'country' => 'BE'],
            ['name' => 'España', 'abbr' => 'ESP', 'country' => 'ES'],
            ['name' => 'Francia', 'abbr' => 'FRA', 'country' => 'FR'],
            ['name' => 'Italia', 'abbr' => 'ITA', 'country' => 'IT'],
            ['name' => 'Países Bajos', 'abbr' => 'NED', 'country' => 'NL'],
            ['name' => 'Eslovenia', 'abbr' => 'SLO', 'country' => 'SI'],
            ['name' => 'Dinamarca', 'abbr' => 'DEN', 'country' => 'DK'],
            ['name' => 'Reino Unido', 'abbr' => 'GBR', 'country' => 'GB'],
            ['name' => 'Colombia', 'abbr' => 'COL', 'country' => 'CO'],
            ['name' => 'Australia', 'abbr' => 'AUS', 'country' => 'AU'],
            ['name' => 'Portugal', 'abbr' => 'POR', 'country' => 'PT'],
            ['name' => 'Noruega', 'abbr' => 'NOR', 'country' => 'NO'],
            ['name' => 'Polonia', 'abbr' => 'POL', 'country' => 'PL'],
            ['name' => 'Suiza', 'abbr' => 'SUI', 'country' => 'CH'],
            ['name' => 'Alemania', 'abbr' => 'GER', 'country' => 'DE'],
            ['name' => 'Irlanda', 'abbr' => 'IRL', 'country' => 'IE'],
            ['name' => 'Sudáfrica', 'abbr' => 'RSA', 'country' => 'ZA'],
            ['name' => 'Eritrea', 'abbr' => 'ERI', 'country' => 'ER'],
            ['name' => 'Luxemburgo', 'abbr' => 'LUX', 'country' => 'LU'],
            ['name' => 'Estados Unidos', 'abbr' => 'USA', 'country' => 'US'],
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
        $competition = CompetitionModel::where('name', 'Mundial 2026')->first();
        if ($competition) {
            $edition = EditionModel::where('competition_id', $competition->id)
                ->where('year', self::YEAR)
                ->first();
            if ($edition) {
                CompetitionParticipantModel::where('edition_id', $edition->id)->delete();
            }
        }

        $ridersByTeam = [
            'BEL' => [
                ['first' => 'Remco', 'last' => 'Evenepoel', 'country' => 'BE'],
                ['first' => 'Wout', 'last' => 'van Aert', 'country' => 'BE'],
                ['first' => 'Jasper', 'last' => 'Philipsen', 'country' => 'BE'],
                ['first' => 'Tiesj', 'last' => 'Benoot', 'country' => 'BE'],
                ['first' => 'Yves', 'last' => 'Lampaert', 'country' => 'BE'],
                ['first' => 'Victor', 'last' => 'Campenaerts', 'country' => 'BE'],
                ['first' => 'Tim', 'last' => 'Merlier', 'country' => 'BE'],
                ['first' => 'Arnaud', 'last' => 'De Lie', 'country' => 'BE'],
            ],
            'ESP' => [
                ['first' => 'Juan', 'last' => 'Ayuso', 'country' => 'ES'],
                ['first' => 'Enric', 'last' => 'Mas', 'country' => 'ES'],
                ['first' => 'Mikel', 'last' => 'Landa', 'country' => 'ES'],
                ['first' => 'Alejandro', 'last' => 'Valverde', 'country' => 'ES'],
                ['first' => 'Pello', 'last' => 'Bilbao', 'country' => 'ES'],
                ['first' => 'Alex', 'last' => 'Aranburu', 'country' => 'ES'],
                ['first' => 'Gorka', 'last' => 'Izagirre', 'country' => 'ES'],
                ['first' => 'Ion', 'last' => 'Izagirre', 'country' => 'ES'],
            ],
            'FRA' => [
                ['first' => 'Romain', 'last' => 'Bardet', 'country' => 'FR'],
                ['first' => 'Julian', 'last' => 'Alaphilippe', 'country' => 'FR'],
                ['first' => 'David', 'last' => 'Gaudu', 'country' => 'FR'],
                ['first' => 'Guillaume', 'last' => 'Martin', 'country' => 'FR'],
                ['first' => 'Pierre', 'last' => 'Rolland', 'country' => 'FR'],
                ['first' => 'Bryan', 'last' => 'Coquard', 'country' => 'FR'],
                ['first' => 'Christophe', 'last' => 'Laporte', 'country' => 'FR'],
                ['first' => 'Stéphane', 'last' => 'Rossetto', 'country' => 'FR'],
            ],
            'ITA' => [
                ['first' => 'Filippo', 'last' => 'Ganna', 'country' => 'IT'],
                ['first' => 'Elia', 'last' => 'Viviani', 'country' => 'IT'],
                ['first' => 'Giulio', 'last' => 'Ciccone', 'country' => 'IT'],
                ['first' => 'Davide', 'last' => 'Formolo', 'country' => 'IT'],
                ['first' => 'Alberto', 'last' => 'Bettiol', 'country' => 'IT'],
                ['first' => 'Matteo', 'last' => 'Trentin', 'country' => 'IT'],
                ['first' => 'Stefano', 'last' => 'Oldani', 'country' => 'IT'],
                ['first' => 'Simone', 'last' => 'Consonni', 'country' => 'IT'],
            ],
            'NED' => [
                ['first' => 'Mathieu', 'last' => 'van der Poel', 'country' => 'NL'],
                ['first' => 'Fabio', 'last' => 'Jakobsen', 'country' => 'NL'],
                ['first' => 'Dylan', 'last' => 'Groenewegen', 'country' => 'NL'],
                ['first' => 'Tom', 'last' => 'Dumoulin', 'country' => 'NL'],
                ['first' => 'Bauke', 'last' => 'Mollema', 'country' => 'NL'],
                ['first' => 'Mike', 'last' => 'Teunissen', 'country' => 'NL'],
                ['first' => 'Nils', 'last' => 'Politt', 'country' => 'DE'],
                ['first' => 'Danny', 'last' => 'van Poppel', 'country' => 'NL'],
            ],
            'SLO' => [
                ['first' => 'Tadej', 'last' => 'Pogačar', 'country' => 'SI'],
                ['first' => 'Primož', 'last' => 'Roglič', 'country' => 'SI'],
                ['first' => 'Matej', 'last' => 'Mohorič', 'country' => 'SI'],
                ['first' => 'Jan', 'last' => 'Tratnik', 'country' => 'SI'],
                ['first' => 'Domen', 'last' => 'Novak', 'country' => 'SI'],
                ['first' => 'Luka', 'last' => 'Mezgec', 'country' => 'SI'],
            ],
            'DEN' => [
                ['first' => 'Jonas', 'last' => 'Vingegaard', 'country' => 'DK'],
                ['first' => 'Mads', 'last' => 'Pedersen', 'country' => 'DK'],
                ['first' => 'Michael', 'last' => 'Mørkøv', 'country' => 'DK'],
                ['first' => 'Kasper', 'last' => 'Asgreen', 'country' => 'DK'],
                ['first' => 'Mattias', 'last' => 'Skjelmose', 'country' => 'DK'],
                ['first' => 'Andreas', 'last' => 'Stokbro', 'country' => 'DK'],
            ],
            'GBR' => [
                ['first' => 'Geraint', 'last' => 'Thomas', 'country' => 'GB'],
                ['first' => 'Adam', 'last' => 'Yates', 'country' => 'GB'],
                ['first' => 'Simon', 'last' => 'Yates', 'country' => 'GB'],
                ['first' => 'Tom', 'last' => 'Pidcock', 'country' => 'GB'],
                ['first' => 'Ben', 'last' => 'Turner', 'country' => 'GB'],
                ['first' => 'Fred', 'last' => 'Wright', 'country' => 'GB'],
            ],
            'COL' => [
                ['first' => 'Egan', 'last' => 'Bernal', 'country' => 'CO'],
                ['first' => 'Rigoberto', 'last' => 'Urán', 'country' => 'CO'],
                ['first' => 'Nairo', 'last' => 'Quintana', 'country' => 'CO'],
                ['first' => 'Sergio', 'last' => 'Higuita', 'country' => 'CO'],
                ['first' => 'Daniel', 'last' => 'Martínez', 'country' => 'CO'],
                ['first' => 'Esteban', 'last' => 'Chaves', 'country' => 'CO'],
            ],
            'AUS' => [
                ['first' => 'Ben', 'last' => 'O\'Connor', 'country' => 'AU'],
                ['first' => 'Caleb', 'last' => 'Ewan', 'country' => 'AU'],
                ['first' => 'Michael', 'last' => 'Matthews', 'country' => 'AU'],
                ['first' => 'Jack', 'last' => 'Haig', 'country' => 'AU'],
                ['first' => 'Simon', 'last' => 'Yates', 'country' => 'AU'],
                ['first' => 'Luke', 'last' => 'Plapp', 'country' => 'AU'],
            ],
            'POR' => [
                ['first' => 'João', 'last' => 'Almeida', 'country' => 'PT'],
                ['first' => 'Rui', 'last' => 'Costa', 'country' => 'PT'],
                ['first' => 'Nelson', 'last' => 'Oliveira', 'country' => 'PT'],
                ['first' => 'Hélio', 'last' => 'Julião', 'country' => 'PT'],
                ['first' => 'Ruben', 'last' => 'Guerreiro', 'country' => 'PT'],
            ],
            'NOR' => [
                ['first' => 'Tobias', 'last' => 'Halland Johannessen', 'country' => 'NO'],
                ['first' => 'Anders', 'last' => 'Halland Johannessen', 'country' => 'NO'],
                ['first' => 'Søren', 'last' => 'Kragh Andersen', 'country' => 'DK'],
                ['first' => 'Mark', 'last' => 'Haller', 'country' => 'NO'],
                ['first' => 'Jonas', 'last' => 'Iversen', 'country' => 'NO'],
            ],
            'POL' => [
                ['first' => 'Rafał', 'last' => 'Majka', 'country' => 'PL'],
                ['first' => 'Michał', 'last' => 'Kwiatkowski', 'country' => 'PL'],
                ['first' => 'Maciej', 'last' => 'Paterski', 'country' => 'PL'],
                ['first' => 'Stanisław', 'last' => 'Aniołkowski', 'country' => 'PL'],
            ],
            'SUI' => [
                ['first' => 'Marc', 'last' => 'Hirschi', 'country' => 'CH'],
                ['first' => 'Stefan', 'last' => 'Küng', 'country' => 'CH'],
                ['first' => 'Gino', 'last' => 'Mäder', 'country' => 'CH'],
                ['first' => 'Silvan', 'last' => 'Dillier', 'country' => 'CH'],
                ['first' => 'Simon', 'last' => 'Pellaud', 'country' => 'CH'],
            ],
            'GER' => [
                ['first' => 'Emanuel', 'last' => 'Buchmann', 'country' => 'DE'],
                ['first' => 'Nils', 'last' => 'Politt', 'country' => 'DE'],
                ['first' => 'Lennard', 'last' => 'Kämna', 'country' => 'DE'],
                ['first' => 'Max', 'last' => 'Schachmann', 'country' => 'DE'],
                ['first' => 'Georg', 'last' => 'Zimmermann', 'country' => 'DE'],
            ],
            'IRL' => [
                ['first' => 'Sam', 'last' => 'Bennett', 'country' => 'IE'],
                ['first' => 'Dan', 'last' => 'Martin', 'country' => 'IE'],
                ['first' => 'Ryan', 'last' => 'Mullen', 'country' => 'IE'],
                ['first' => 'Rory', 'last' => 'Townshend', 'country' => 'IE'],
            ],
            'RSA' => [
                ['first' => 'Louis', 'last' => 'Meintjes', 'country' => 'ZA'],
                ['first' => 'Ryan', 'last' => 'Gibbons', 'country' => 'ZA'],
                ['first' => 'Daryl', 'last' => 'Impey', 'country' => 'ZA'],
            ],
            'ERI' => [
                ['first' => 'Biniam', 'last' => 'Girmay', 'country' => 'ER'],
                ['first' => 'Merhawi', 'last' => 'Kudus', 'country' => 'ER'],
                ['first' => 'Henok', 'last' => 'Mulubrhan', 'country' => 'ER'],
            ],
            'LUX' => [
                ['first' => 'Bob', 'last' => 'Jungels', 'country' => 'LU'],
                ['first' => 'Jean', 'last' => 'Kircheneuer', 'country' => 'LU'],
                ['first' => 'Alex', 'last' => 'Kirsch', 'country' => 'LU'],
            ],
            'USA' => [
                ['first' => 'Sepp', 'last' => 'Kuss', 'country' => 'US'],
                ['first' => 'Neilson', 'last' => 'Powless', 'country' => 'US'],
                ['first' => 'Ben', 'last' => 'King', 'country' => 'US'],
                ['first' => 'Magnus', 'last' => 'Cort', 'country' => 'DK'],
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
        $competition = CompetitionModel::where('name', 'Mundial 2026')->first();
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
            [
                'num' => 1,
                'name' => 'Contrareloj Individual — Montreal',
                'date' => '2026-09-21',
                'type' => StageType::TimeTrial,
                'dist' => 32.5,
                'origin' => 'Montreal',
                'dest' => 'Montreal',
                'diff' => 2,
                'elev' => 280,
                'scheduled_start' => '2026-09-21 10:00:00',
            ],
            [
                'num' => 2,
                'name' => 'Prueba en Línea — Montreal',
                'date' => '2026-09-27',
                'type' => StageType::Flat,
                'dist' => 280.0,
                'origin' => 'Montreal',
                'dest' => 'Montreal',
                'diff' => 2,
                'elev' => 3200,
                'scheduled_start' => '2026-09-27 10:00:00',
            ],
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
                'scheduled_start' => $s['scheduled_start'],
                'status' => StageStatus::Upcoming->value,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->createStageParticipants($editionId);
    }

    private function createStageParticipants(string $editionId): void
    {
        $competition = CompetitionModel::where('name', 'Mundial 2026')->first();
        if (! $competition) {
            return;
        }

        $ttStage = DB::table('stages')
            ->where('edition_id', $editionId)
            ->where('number', 1)
            ->first();

        $roadStage = DB::table('stages')
            ->where('edition_id', $editionId)
            ->where('number', 2)
            ->first();

        if (! $ttStage || ! $roadStage) {
            return;
        }

        StageParticipantModel::where('stage_id', $ttStage->id)->delete();
        StageParticipantModel::where('stage_id', $roadStage->id)->delete();

        $ttSpecialists = [
            'Remco', 'Evenepoel',
            'Filippo', 'Ganna',
            'Stefan', 'Küng',
            'Tom', 'Dumoulin',
            'Wout', 'van Aert',
            'Tadej', 'Pogačar',
            'Primož', 'Roglič',
            'Jonas', 'Vingegaard',
            'Geraint', 'Thomas',
            'Kasper', 'Asgreen',
            'Rafał', 'Majka',
            'Michał', 'Kwiatkowski',
            'Nelson', 'Oliveira',
            'Ryan', 'Mullen',
            'Victor', 'Campenaerts',
            'Yves', 'Lampaert',
            'Bob', 'Jungels',
            'Nils', 'Politt',
            'Max', 'Schachmann',
            'Lennard', 'Kämna',
            'Emanuel', 'Buchmann',
            'Ben', 'O\'Connor',
            'Jack', 'Haig',
            'Luke', 'Plapp',
            'Neilson', 'Powless',
            'Sepp', 'Kuss',
            'João', 'Almeida',
            'Rui', 'Costa',
            'Enric', 'Mas',
            'Juan', 'Ayuso',
            'Romain', 'Bardet',
            'Guillaume', 'Martin',
            'David', 'Gaudu',
            'Davide', 'Formolo',
            'Alberto', 'Bettiol',
            'Egan', 'Bernal',
            'Daniel', 'Martínez',
            'Sergio', 'Higuita',
            'Biniam', 'Girmay',
            'Marc', 'Hirschi',
            'Mattias', 'Skjelmose',
        ];

        $ttParticipants = $this->findParticipantsByRiderNames($editionId, $ttSpecialists, 2);

        foreach ($ttParticipants as $p) {
            StageParticipantModel::firstOrCreate([
                'stage_id' => $ttStage->id,
                'rider_id' => $p->rider_id,
            ], [
                'id' => Str::uuid()->toString(),
                'team_id' => $p->team_id,
            ]);
        }

        $allRiders = DB::table('competition_participants')
            ->where('edition_id', $editionId)
            ->get();

        foreach ($allRiders as $rider) {
            StageParticipantModel::firstOrCreate([
                'stage_id' => $roadStage->id,
                'rider_id' => $rider->rider_id,
            ], [
                'id' => Str::uuid()->toString(),
                'team_id' => $rider->team_id,
            ]);
        }
    }

    private function findParticipantsByRiderNames(string $editionId, array $names, int $chunkSize): Collection
    {
        $riders = [];

        for ($i = 0; $i < count($names); $i += 2) {
            $riders[] = ['first' => $names[$i], 'last' => $names[$i + 1] ?? ''];
        }

        $riderIds = [];

        foreach ($riders as $r) {
            $rider = RiderModel::where('first_name', $r['first'])
                ->where('last_name', $r['last'])
                ->first();

            if ($rider) {
                $riderIds[] = $rider->id;
            }
        }

        return DB::table('competition_participants')
            ->where('edition_id', $editionId)
            ->whereIn('rider_id', $riderIds)
            ->get();
    }
}
