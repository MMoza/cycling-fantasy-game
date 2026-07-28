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

class VueltaEspana2026Seeder extends Seeder
{
    private const YEAR = 2026;

    private array $teamIds = [];

    private array $riderIds = [];

    public function run(): void
    {
        $edition = EditionModel::whereHas('competition', fn ($q) => $q->where('name', 'La Vuelta'))
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
    }

    private function createRosters(): void
    {
        $competition = CompetitionModel::where('name', 'La Vuelta')->first();
        if ($competition) {
            $edition = EditionModel::where('competition_id', $competition->id)
                ->where('year', self::YEAR)
                ->first();
            if ($edition) {
                CompetitionParticipantModel::where('edition_id', $edition->id)->delete();
            }
        }

        $ridersByTeam = [
            'ADC' => [
                ['first' => 'Hugo', 'last' => 'Houle', 'country' => 'CA'],
                ['first' => 'Gal', 'last' => 'Glivar', 'country' => 'SI'],
            ],
            'TBV' => [
                ['first' => 'Pau', 'last' => 'Miquel', 'country' => 'ES'],
            ],
            'BBH' => [
                ['first' => 'Jesús', 'last' => 'Herrada', 'country' => 'ES'],
                ['first' => 'José Manuel', 'last' => 'Díaz', 'country' => 'ES'],
            ],
            'COF' => [
                ['first' => 'Bryan', 'last' => 'Coquard', 'country' => 'FR'],
            ],
            'DCM' => [
                ['first' => 'Matthew', 'last' => 'Riccitello', 'country' => 'US'],
                ['first' => 'Felix', 'last' => 'Gall', 'country' => 'AT'],
                ['first' => 'Léo', 'last' => 'Bisiaux', 'country' => 'FR'],
            ],
            'EFE' => [
                ['first' => 'Marijn', 'last' => 'van den Berg', 'country' => 'NL'],
            ],
            'EKP' => [],
            'GFC' => [
                ['first' => 'Clément', 'last' => 'Berthet', 'country' => 'FR'],
                ['first' => 'Guillaume', 'last' => 'Martin', 'country' => 'FR'],
            ],
            'LTK' => [
                ['first' => 'Mathias', 'last' => 'Norsgaard', 'country' => 'DK'],
                ['first' => 'Thibau', 'last' => 'Nys', 'country' => 'BE'],
                ['first' => 'Mads', 'last' => 'Pedersen', 'country' => 'DK'],
                ['first' => 'Mattias', 'last' => 'Skjelmose', 'country' => 'DK'],
            ],
            'LTD' => [
                ['first' => 'Jarno', 'last' => 'Widar', 'country' => 'BE'],
            ],
            'MOV' => [
                ['first' => 'Nairo', 'last' => 'Quintana', 'country' => 'CO'],
                ['first' => 'Iván', 'last' => 'Romeo', 'country' => 'ES'],
                ['first' => 'Cian', 'last' => 'Uijtdebroeks', 'country' => 'BE'],
                ['first' => 'Raúl', 'last' => 'García Pierna', 'country' => 'ES'],
                ['first' => 'Pablo', 'last' => 'Castrillo', 'country' => 'ES'],
                ['first' => 'Einer', 'last' => 'Rubio', 'country' => 'CO'],
                ['first' => 'Enric', 'last' => 'Mas', 'country' => 'ES'],
            ],
            'IGD' => [
                ['first' => 'Carlos', 'last' => 'Rodríguez', 'country' => 'ES'],
                ['first' => 'Axel', 'last' => 'Laurance', 'country' => 'FR'],
            ],
            'NSN' => [
                ['first' => 'Jan', 'last' => 'Hirt', 'country' => 'CZ'],
            ],
            'Q36' => [
                ['first' => 'David', 'last' => 'de la Cruz', 'country' => 'ES'],
                ['first' => 'Milan', 'last' => 'Vader', 'country' => 'NL'],
            ],
            'RBH' => [
                ['first' => 'Luke', 'last' => 'Tuckwell', 'country' => 'AU'],
                ['first' => 'Alexander', 'last' => 'Hajek', 'country' => 'AT'],
                ['first' => 'Primož', 'last' => 'Roglič', 'country' => 'SI'],
                ['first' => 'Gianni', 'last' => 'Vermeersch', 'country' => 'BE'],
            ],
            'SOQ' => [
                ['first' => 'Filippo', 'last' => 'Zana', 'country' => 'IT'],
                ['first' => 'Steff', 'last' => 'Cras', 'country' => 'BE'],
                ['first' => 'Junior', 'last' => 'Lecerf', 'country' => 'BE'],
                ['first' => 'Alberto', 'last' => 'Dainese', 'country' => 'IT'],
                ['first' => 'Mikel', 'last' => 'Landa', 'country' => 'ES'],
                ['first' => 'Ethan', 'last' => 'Hayter', 'country' => 'GB'],
            ],
            'JAY' => [
                ['first' => 'Paul', 'last' => 'Double', 'country' => 'GB'],
                ['first' => 'Luke', 'last' => 'Plapp', 'country' => 'AU'],
            ],
            'TPP' => [
                ['first' => 'Juan Guillermo', 'last' => 'Martinez', 'country' => 'CO'],
                ['first' => 'Max', 'last' => 'Poole', 'country' => 'GB'],
            ],
            'TVL' => [
                ['first' => 'Wout', 'last' => 'van Aert', 'country' => 'BE'],
                ['first' => 'Matthew', 'last' => 'Brennan', 'country' => 'GB'],
                ['first' => 'Jørgen', 'last' => 'Nordhagen', 'country' => 'NO'],
                ['first' => 'Ben', 'last' => 'Tulett', 'country' => 'GB'],
                ['first' => 'Steven', 'last' => 'Kruijswijk', 'country' => 'NL'],
                ['first' => 'Tijmen', 'last' => 'Graat', 'country' => 'NL'],
                ['first' => 'Menno', 'last' => 'Huising', 'country' => 'NL'],
                ['first' => 'Filippo', 'last' => 'Fiorelli', 'country' => 'IT'],
            ],
            'TUD' => [],
            'UAD' => [
                ['first' => 'João', 'last' => 'Almeida', 'country' => 'PT'],
                ['first' => 'Marc', 'last' => 'Soler', 'country' => 'ES'],
                ['first' => 'Ivo', 'last' => 'Oliveira', 'country' => 'PT'],
                ['first' => 'Domen', 'last' => 'Novak', 'country' => 'SI'],
            ],
            'UXM' => [
                ['first' => 'Erlend', 'last' => 'Blikra', 'country' => 'NO'],
                ['first' => 'Magnus', 'last' => 'Cort', 'country' => 'DK'],
            ],
            'XAT' => [
                ['first' => 'Alberto', 'last' => 'Bettiol', 'country' => 'IT'],
                ['first' => 'Clément', 'last' => 'Champoussin', 'country' => 'FR'],
                ['first' => 'Harold Martín', 'last' => 'López', 'country' => 'EC'],
                ['first' => 'Harold', 'last' => 'Tejada', 'country' => 'CO'],
                ['first' => 'Cristián', 'last' => 'Rodríguez', 'country' => 'ES'],
                ['first' => 'Lorenzo', 'last' => 'Fortunato', 'country' => 'IT'],
                ['first' => 'Davide', 'last' => 'Ballerini', 'country' => 'IT'],
                ['first' => 'Henok', 'last' => 'Mulubrhan', 'country' => 'ER'],
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
        $competition = CompetitionModel::where('name', 'La Vuelta')->first();
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
            ['num' => 1, 'name' => 'Monaco - Monaco (CRI)', 'date' => '2026-08-22', 'type' => StageType::TimeTrial, 'dist' => 9.0, 'origin' => 'Monaco', 'dest' => 'Monaco', 'diff' => 1, 'elev' => 40],
            ['num' => 2, 'name' => 'Monaco - Manosque', 'date' => '2026-08-23', 'type' => StageType::Hill, 'dist' => 215.2, 'origin' => 'Monaco', 'dest' => 'Manosque', 'diff' => 2, 'elev' => 1800],
            ['num' => 3, 'name' => 'Gruissan - Font Romeu', 'date' => '2026-08-24', 'type' => StageType::HighMountain, 'dist' => 166.7, 'origin' => 'Gruissan', 'dest' => 'Font Romeu', 'diff' => 3, 'elev' => 3600],
            ['num' => 4, 'name' => 'Andorra La Vella - Andorra La Vella', 'date' => '2026-08-25', 'type' => StageType::Mountain, 'dist' => 104.9, 'origin' => 'Andorra La Vella', 'dest' => 'Andorra La Vella', 'diff' => 3, 'elev' => 2800],
            ['num' => 5, 'name' => 'Falset - Roquetes', 'date' => '2026-08-26', 'type' => StageType::Flat, 'dist' => 171.1, 'origin' => 'Falset', 'dest' => 'Roquetes', 'diff' => 1, 'elev' => 420],
            ['num' => 6, 'name' => 'Alcossebre - Castellón', 'date' => '2026-08-27', 'type' => StageType::Hill, 'dist' => 176.8, 'origin' => 'Alcossebre', 'dest' => 'Castellón', 'diff' => 2, 'elev' => 1200],
            ['num' => 7, 'name' => "Vall d'Alba - Aramón Valdelinares", 'date' => '2026-08-28', 'type' => StageType::HighMountain, 'dist' => 149.9, 'origin' => "Vall d'Alba", 'dest' => 'Aramón Valdelinares', 'diff' => 3, 'elev' => 3400],
            ['num' => 8, 'name' => 'Puçol - Xeraco', 'date' => '2026-08-29', 'type' => StageType::Flat, 'dist' => 176.4, 'origin' => 'Puçol', 'dest' => 'Xeraco', 'diff' => 1, 'elev' => 380],
            ['num' => 9, 'name' => 'Villajoyosa - Alto de Aitana', 'date' => '2026-08-30', 'type' => StageType::HighMountain, 'dist' => 187.5, 'origin' => 'Villajoyosa', 'dest' => 'Alto de Aitana', 'diff' => 3, 'elev' => 3900],
            ['num' => 10, 'name' => 'Alcaraz - Elche de la Sierra', 'date' => '2026-09-01', 'type' => StageType::Hill, 'dist' => 184.5, 'origin' => 'Alcaraz', 'dest' => 'Elche de la Sierra', 'diff' => 2, 'elev' => 1400],
            ['num' => 11, 'name' => 'Cartagena - Lorca', 'date' => '2026-09-02', 'type' => StageType::Flat, 'dist' => 156.1, 'origin' => 'Cartagena', 'dest' => 'Lorca', 'diff' => 1, 'elev' => 350],
            ['num' => 12, 'name' => 'Vera - Calar Alto', 'date' => '2026-09-03', 'type' => StageType::HighMountain, 'dist' => 166.5, 'origin' => 'Vera', 'dest' => 'Calar Alto', 'diff' => 3, 'elev' => 3800],
            ['num' => 13, 'name' => 'Almuñécar - Loja', 'date' => '2026-09-04', 'type' => StageType::Hill, 'dist' => 193.2, 'origin' => 'Almuñécar', 'dest' => 'Loja', 'diff' => 2, 'elev' => 1600],
            ['num' => 14, 'name' => 'Jaén - Sierra de la Pandera', 'date' => '2026-09-05', 'type' => StageType::HighMountain, 'dist' => 152.7, 'origin' => 'Jaén', 'dest' => 'Sierra de la Pandera', 'diff' => 3, 'elev' => 3500],
            ['num' => 15, 'name' => 'Palma del Río - Córdoba', 'date' => '2026-09-06', 'type' => StageType::Hill, 'dist' => 181.2, 'origin' => 'Palma del Río', 'dest' => 'Córdoba', 'diff' => 2, 'elev' => 1100],
            ['num' => 16, 'name' => 'Cortegana - Palos de la Frontera', 'date' => '2026-09-08', 'type' => StageType::Flat, 'dist' => 186.0, 'origin' => 'Cortegana', 'dest' => 'Palos de la Frontera', 'diff' => 1, 'elev' => 420],
            ['num' => 17, 'name' => 'Dos Hermanas - Sevilla', 'date' => '2026-09-09', 'type' => StageType::Flat, 'dist' => 189.2, 'origin' => 'Dos Hermanas', 'dest' => 'Sevilla', 'diff' => 1, 'elev' => 280],
            ['num' => 18, 'name' => 'El Puerto de Santa María - Jerez de la Frontera (CRI)', 'date' => '2026-09-10', 'type' => StageType::TimeTrial, 'dist' => 32.5, 'origin' => 'El Puerto de Santa María', 'dest' => 'Jerez de la Frontera', 'diff' => 1, 'elev' => 120],
            ['num' => 19, 'name' => 'Vélez-Málaga - Peñas Blancas, Estepona', 'date' => '2026-09-11', 'type' => StageType::Hill, 'dist' => 205.1, 'origin' => 'Vélez-Málaga', 'dest' => 'Peñas Blancas, Estepona', 'diff' => 3, 'elev' => 2600],
            ['num' => 20, 'name' => 'La Calahorra - Collado del Alguacil', 'date' => '2026-09-12', 'type' => StageType::HighMountain, 'dist' => 206.7, 'origin' => 'La Calahorra', 'dest' => 'Collado del Alguacil', 'diff' => 3, 'elev' => 4200],
            ['num' => 21, 'name' => 'Carrefour Granada - Granada', 'date' => '2026-09-13', 'type' => StageType::Flat, 'dist' => 99.4, 'origin' => 'Carrefour Granada', 'dest' => 'Granada', 'diff' => 1, 'elev' => 240],
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
