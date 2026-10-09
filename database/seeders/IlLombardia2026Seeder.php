<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domain\ValueObjects\StageStatus;
use App\Domain\ValueObjects\StageType;
use App\Infrastructure\Persistence\Models\CompetitionModel;
use App\Infrastructure\Persistence\Models\CompetitionParticipantModel;
use App\Infrastructure\Persistence\Models\EditionModel;
use App\Infrastructure\Persistence\Models\RiderModel;
use App\Infrastructure\Persistence\Models\StageModel;
use App\Infrastructure\Persistence\Models\StageParticipantModel;
use App\Infrastructure\Persistence\Models\TeamModel;
use App\Infrastructure\Persistence\Models\TeamRosterModel;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Startlist completo de Il Lombardía 2026.
 *
 * Fuente: https://www.procyclingstats.com/race/il-lombardia-2026-result/startlist
 * 174 corredores confirmados (25 equipos, 7 corredores salvo Netcompany INEOS con 6).
 *
 * Seguro para ejecutar en producción:
 * - Riders y equipos se reutilizan con `firstOrCreate` (nunca se duplican ni se borran).
 * - Solo toca los `competition_participants` y `stage_participants` de la edición 2026.
 * - La etapa se conserva si ya existe (no pisa resultados ni estado).
 * - Las predicciones referencian `stages`/`riders`, no participantes: no se ven afectadas.
 *
 * Ejecución en prod:
 *   php artisan db:seed --class=IlLombardia2026Seeder --force
 *
 * Para actualizar: ampliar `startlist()` con los corredores nuevos (formato
 * ['first' => '...', 'last' => '...', 'country' => 'XX']) y volver a ejecutar
 * el seeder. Los participantes de la edición se reconstruyen con el startlist actual.
 */
class IlLombardia2026Seeder extends Seeder
{
    private const YEAR = 2026;

    /**
     * Nombres con los que la competición puede estar registrada en la DB,
     * por orden de prioridad.
     */
    private const COMPETITION_NAMES = ['Il Lombardía', 'Il Lombardia', 'Giro de Lombardía'];

    private const PCS_SLUG = 'il-lombardia';

    private const STAGE_NUMBER = 1;

    /** @var array<string, string> abbr => team id */
    private array $teamIds = [];

    /** @var array<string, array<string, string>> abbr => list of rider ids */
    private array $riderIds = [];

    public function run(): void
    {
        $edition = $this->findEdition();

        if (! $edition) {
            $this->command?->warn('Il Lombardia '.self::YEAR.' edition not found. Nothing seeded.');

            return;
        }

        $this->createTeams();
        $this->createRiders();
        $this->createParticipants($edition);
        $this->createStage($edition);
    }

    private function findEdition(): ?EditionModel
    {
        $competition = $this->findCompetition();

        if (! $competition) {
            return null;
        }

        return EditionModel::where('competition_id', $competition->id)
            ->where('year', self::YEAR)
            ->first();
    }

    private function findCompetition(): ?CompetitionModel
    {
        foreach (self::COMPETITION_NAMES as $name) {
            $competition = CompetitionModel::where('name', $name)->first();

            if ($competition) {
                return $competition;
            }
        }

        return CompetitionModel::where('pcs_slug', self::PCS_SLUG)->first();
    }

    private function createTeams(): void
    {
        foreach ($this->startlist() as $abbr => $team) {
            $teamModel = TeamModel::firstOrCreate(['name' => $team['name']], [
                'id' => Str::uuid()->toString(),
                'abbreviation' => $abbr,
                'country_id' => $team['country'],
            ]);

            $this->teamIds[$abbr] = $teamModel->id;
        }
    }

    private function createRiders(): void
    {
        foreach ($this->startlist() as $abbr => $team) {
            $teamId = $this->teamIds[$abbr] ?? null;

            if (! $teamId) {
                continue;
            }

            foreach ($team['riders'] as $rider) {
                $riderModel = RiderModel::firstOrCreate([
                    'first_name' => $rider['first'],
                    'last_name' => $rider['last'],
                ], [
                    'id' => Str::uuid()->toString(),
                    'country_id' => $rider['country'],
                ]);

                $this->riderIds[$abbr][] = $riderModel->id;

                TeamRosterModel::firstOrCreate([
                    'team_id' => $teamId,
                    'rider_id' => $riderModel->id,
                    'year' => self::YEAR,
                ], [
                    'id' => Str::uuid()->toString(),
                ]);
            }
        }
    }

    private function createParticipants(EditionModel $edition): void
    {
        // El set de participantes siempre refleja el startlist actual del seeder.
        CompetitionParticipantModel::where('edition_id', $edition->id)->delete();

        foreach ($this->riderIds as $abbr => $riderIds) {
            $teamId = $this->teamIds[$abbr] ?? null;

            if (! $teamId) {
                continue;
            }

            foreach ($riderIds as $riderId) {
                CompetitionParticipantModel::firstOrCreate([
                    'competition_id' => $edition->competition_id,
                    'edition_id' => $edition->id,
                    'team_id' => $teamId,
                    'rider_id' => $riderId,
                ], [
                    'id' => Str::uuid()->toString(),
                ]);
            }
        }
    }

    private function createStage(EditionModel $edition): void
    {
        $stage = StageModel::firstOrCreate([
            'edition_id' => $edition->id,
            'number' => self::STAGE_NUMBER,
        ], [
            'id' => Str::uuid()->toString(),
            'name' => 'Bergamo - Como',
            'date' => '2026-10-10',
            'scheduled_start' => '2026-10-10 10:00:00',
            'type' => StageType::Hill,
            'distance' => 239.0,
            'origin' => 'Bergamo',
            'destination' => 'Como',
            'difficulty' => 3,
            'elevation_gain' => 4000,
            'status' => StageStatus::Upcoming,
        ]);

        StageParticipantModel::where('stage_id', $stage->id)->delete();

        foreach ($this->riderIds as $abbr => $riderIds) {
            $teamId = $this->teamIds[$abbr] ?? null;

            if (! $teamId) {
                continue;
            }

            foreach ($riderIds as $riderId) {
                StageParticipantModel::firstOrCreate([
                    'stage_id' => $stage->id,
                    'rider_id' => $riderId,
                ], [
                    'id' => Str::uuid()->toString(),
                    'team_id' => $teamId,
                ]);
            }
        }
    }

    /**
     * Startlist completo del Il Lombardia 2026 en PCS: 174 corredores, 25 equipos.
     *
     * @return array<string, array{name: string, country: string, riders: array<int, array{first: string, last: string, country: string}>}>
     */
    private function startlist(): array
    {
        return [
            'UAD' => [
                'name' => 'UAE Team Emirates - XRG',
                'country' => 'AE',
                'riders' => [
                    ['first' => 'Brandon', 'last' => 'McNulty', 'country' => 'US'],
                    ['first' => 'Jan', 'last' => 'Christen', 'country' => 'CH'],
                    ['first' => 'Isaac', 'last' => 'Del Toro', 'country' => 'MX'],
                    ['first' => 'Felix', 'last' => 'Großschartner', 'country' => 'AT'],
                    ['first' => 'Domen', 'last' => 'Novak', 'country' => 'SI'],
                    ['first' => 'Pavel', 'last' => 'Sivakov', 'country' => 'FR'],
                    ['first' => 'Adam', 'last' => 'Yates', 'country' => 'GB'],
                ],
            ],
            'ADC' => [
                'name' => 'Alpecin - Premier Tech',
                'country' => 'BE',
                'riders' => [
                    ['first' => 'Ramses', 'last' => 'Debruyne', 'country' => 'BE'],
                    ['first' => 'Francesco', 'last' => 'Busatto', 'country' => 'IT'],
                    ['first' => 'Aaron', 'last' => 'Dockx', 'country' => 'BE'],
                    ['first' => 'Michael', 'last' => 'Gogl', 'country' => 'AT'],
                    ['first' => 'Hugo', 'last' => 'Houle', 'country' => 'CA'],
                    ['first' => 'Luca', 'last' => 'Vergallito', 'country' => 'IT'],
                    ['first' => 'Emiel', 'last' => 'Verstrynge', 'country' => 'BE'],
                ],
            ],
            'TBV' => [
                'name' => 'Bahrain - Victorious',
                'country' => 'BH',
                'riders' => [
                    ['first' => 'Pello', 'last' => 'Bilbao', 'country' => 'ES'],
                    ['first' => 'Santiago', 'last' => 'Buitrago', 'country' => 'CO'],
                    ['first' => 'Lenny', 'last' => 'Martinez', 'country' => 'FR'],
                    ['first' => 'Fran', 'last' => 'Miholjević', 'country' => 'HR'],
                    ['first' => 'Jakob', 'last' => 'Omrzel', 'country' => 'SI'],
                    ['first' => 'Antonio', 'last' => 'Tiberi', 'country' => 'IT'],
                    ['first' => 'Edoardo', 'last' => 'Zambanini', 'country' => 'IT'],
                ],
            ],
            'BCF' => [
                'name' => 'Bardiani CSF 7 Saber',
                'country' => 'IT',
                'riders' => [
                    ['first' => 'Martin', 'last' => 'Marcellusi', 'country' => 'IT'],
                    ['first' => 'Manuele', 'last' => 'Tarozzi', 'country' => 'IT'],
                    ['first' => 'Alex', 'last' => 'Tolio', 'country' => 'IT'],
                    ['first' => 'Filippo', 'last' => 'Turconi', 'country' => 'IT'],
                    ['first' => 'Vicente', 'last' => 'Rojas', 'country' => 'CL'],
                    ['first' => 'Edward', 'last' => 'Cruz', 'country' => 'CO'],
                    ['first' => 'Martin Santiago', 'last' => 'Herreño', 'country' => 'CO'],
                ],
            ],
            'COF' => [
                'name' => 'Cofidis',
                'country' => 'FR',
                'riders' => [
                    ['first' => 'Ion', 'last' => 'Izagirre', 'country' => 'ES'],
                    ['first' => 'Sam', 'last' => 'Maisonobe', 'country' => 'FR'],
                    ['first' => 'Sylvain', 'last' => 'Moniquet', 'country' => 'BE'],
                    ['first' => 'Louis', 'last' => 'Rouland', 'country' => 'FR'],
                    ['first' => 'Sergio', 'last' => 'Samitier', 'country' => 'ES'],
                    ['first' => 'Dylan', 'last' => 'Teuns', 'country' => 'BE'],
                    ['first' => 'Edoardo', 'last' => 'Zamperini', 'country' => 'IT'],
                ],
            ],
            'DCM' => [
                'name' => 'Decathlon CMA CGM Team',
                'country' => 'FR',
                'riders' => [
                    ['first' => 'Paul', 'last' => 'Seixas', 'country' => 'FR'],
                    ['first' => 'Tiesj', 'last' => 'Benoot', 'country' => 'BE'],
                    ['first' => 'Antoine', 'last' => "L'Hote", 'country' => 'FR'],
                    ['first' => 'Matthew', 'last' => 'Riccitello', 'country' => 'US'],
                    ['first' => 'Jordan', 'last' => 'Labrosse', 'country' => 'FR'],
                    ['first' => 'Callum', 'last' => 'Scotson', 'country' => 'AU'],
                    ['first' => 'Nicolas', 'last' => 'Prodhomme', 'country' => 'FR'],
                ],
            ],
            'EFE' => [
                'name' => 'EF Education - EasyPost',
                'country' => 'US',
                'riders' => [
                    ['first' => 'Ben', 'last' => 'Healy', 'country' => 'IE'],
                    ['first' => 'Alex', 'last' => 'Baudin', 'country' => 'FR'],
                    ['first' => 'Michael', 'last' => 'Leonard', 'country' => 'CA'],
                    ['first' => 'Lukas', 'last' => 'Nerurkar', 'country' => 'GB'],
                    ['first' => 'Sean', 'last' => 'Quinn', 'country' => 'US'],
                    ['first' => 'Juan Felipe', 'last' => 'Rodriguez', 'country' => 'CO'],
                    ['first' => 'Michael', 'last' => 'Valgren', 'country' => 'DK'],
                ],
            ],
            'GFC' => [
                'name' => 'Groupama - FDJ United',
                'country' => 'FR',
                'riders' => [
                    ['first' => 'Rudy', 'last' => 'Molard', 'country' => 'FR'],
                    ['first' => 'Clément', 'last' => 'Berthet', 'country' => 'FR'],
                    ['first' => 'Tom', 'last' => 'Donnenwirth', 'country' => 'FR'],
                    ['first' => 'Valentin', 'last' => 'Madouas', 'country' => 'FR'],
                    ['first' => 'Guillaume', 'last' => 'Martin', 'country' => 'FR'],
                    ['first' => 'Quentin', 'last' => 'Pacher', 'country' => 'FR'],
                    ['first' => 'Brieuc', 'last' => 'Rolland', 'country' => 'FR'],
                ],
            ],
            'LTK' => [
                'name' => 'Lidl - Trek',
                'country' => 'US',
                'riders' => [
                    ['first' => 'Giulio', 'last' => 'Ciccone', 'country' => 'IT'],
                    ['first' => 'Juan', 'last' => 'Ayuso', 'country' => 'ES'],
                    ['first' => 'Andrea', 'last' => 'Bagioli', 'country' => 'IT'],
                    ['first' => 'Patrick', 'last' => 'Konrad', 'country' => 'AT'],
                    ['first' => 'Bauke', 'last' => 'Mollema', 'country' => 'NL'],
                    ['first' => 'Sam', 'last' => 'Oomen', 'country' => 'NL'],
                    ['first' => 'Quinn', 'last' => 'Simmons', 'country' => 'US'],
                ],
            ],
            'LTD' => [
                'name' => 'Lotto Intermarché',
                'country' => 'BE',
                'riders' => [
                    ['first' => 'Lennert', 'last' => 'Van Eetvelt', 'country' => 'BE'],
                    ['first' => 'Lars', 'last' => 'Craps', 'country' => 'BE'],
                    ['first' => 'Simone', 'last' => 'Gualdi', 'country' => 'IT'],
                    ['first' => 'Lorenzo', 'last' => 'Rota', 'country' => 'IT'],
                    ['first' => 'Reuben', 'last' => 'Thompson', 'country' => 'NZ'],
                    ['first' => 'Jarno', 'last' => 'Widar', 'country' => 'BE'],
                    ['first' => 'Georg', 'last' => 'Zimmermann', 'country' => 'DE'],
                ],
            ],
            'MBH' => [
                'name' => 'MBH Bank CSB Telecom Fort',
                'country' => 'HU',
                'riders' => [
                    ['first' => 'Luca', 'last' => 'Cretti', 'country' => 'IT'],
                    ['first' => 'Márton', 'last' => 'Dina', 'country' => 'HU'],
                    ['first' => 'Alessandro', 'last' => 'Fancellu', 'country' => 'IT'],
                    ['first' => 'Florian Samuel', 'last' => 'Kajamini', 'country' => 'IT'],
                    ['first' => 'Lorenzo', 'last' => 'Masciarelli', 'country' => 'IT'],
                    ['first' => 'Fausto', 'last' => 'Masnada', 'country' => 'IT'],
                    ['first' => 'Alessandro', 'last' => 'Verre', 'country' => 'IT'],
                ],
            ],
            'MOV' => [
                'name' => 'Movistar Team',
                'country' => 'ES',
                'riders' => [
                    ['first' => 'Enric', 'last' => 'Mas', 'country' => 'ES'],
                    ['first' => 'Roger', 'last' => 'Adrià', 'country' => 'ES'],
                    ['first' => 'Carlos', 'last' => 'Canal', 'country' => 'ES'],
                    ['first' => 'Jefferson Alveiro', 'last' => 'Cepeda', 'country' => 'EC'],
                    ['first' => 'Juan Pedro', 'last' => 'López', 'country' => 'ES'],
                    ['first' => 'Diego', 'last' => 'Pescador', 'country' => 'CO'],
                    ['first' => 'Einer', 'last' => 'Rubio', 'country' => 'CO'],
                ],
            ],
            'IGD' => [
                'name' => 'Netcompany INEOS',
                'country' => 'GB',
                'riders' => [
                    ['first' => 'Thymen', 'last' => 'Arensman', 'country' => 'NL'],
                    ['first' => 'Jack', 'last' => 'Haig', 'country' => 'AU'],
                    ['first' => 'Lucas', 'last' => 'Hamilton', 'country' => 'AU'],
                    ['first' => 'Michał', 'last' => 'Kwiatkowski', 'country' => 'PL'],
                    ['first' => 'Oscar', 'last' => 'Onley', 'country' => 'GB'],
                    ['first' => 'Carlos', 'last' => 'Rodríguez', 'country' => 'ES'],
                ],
            ],
            'NSN' => [
                'name' => 'NSN Cycling Team',
                'country' => 'NL',
                'riders' => [
                    ['first' => 'George', 'last' => 'Bennett', 'country' => 'NZ'],
                    ['first' => 'Pier-André', 'last' => 'Côté', 'country' => 'CA'],
                    ['first' => 'Jan', 'last' => 'Hirt', 'country' => 'CZ'],
                    ['first' => 'Moritz', 'last' => 'Kretschy', 'country' => 'DE'],
                    ['first' => 'Pau', 'last' => 'Martí', 'country' => 'ES'],
                    ['first' => 'Dion', 'last' => 'Smith', 'country' => 'NZ'],
                    ['first' => 'Corbin', 'last' => 'Strong', 'country' => 'NZ'],
                ],
            ],
            'Q36' => [
                'name' => 'Pinarello Q36.5 Pro Cycling Team',
                'country' => 'CH',
                'riders' => [
                    ['first' => 'Tom', 'last' => 'Pidcock', 'country' => 'GB'],
                    ['first' => 'Xabier Mikel', 'last' => 'Azparren', 'country' => 'ES'],
                    ['first' => 'Marcel', 'last' => 'Camprubí', 'country' => 'ES'],
                    ['first' => 'Mark', 'last' => 'Donovan', 'country' => 'GB'],
                    ['first' => 'Quinten', 'last' => 'Hermans', 'country' => 'BE'],
                    ['first' => 'Damien', 'last' => 'Howson', 'country' => 'AU'],
                    ['first' => 'Xandro', 'last' => 'Meurisse', 'country' => 'BE'],
                ],
            ],
            'RBH' => [
                'name' => 'Red Bull - BORA - hansgrohe',
                'country' => 'DE',
                'riders' => [
                    ['first' => 'Remco', 'last' => 'Evenepoel', 'country' => 'BE'],
                    ['first' => 'Giovanni', 'last' => 'Aleotti', 'country' => 'IT'],
                    ['first' => 'Mattia', 'last' => 'Cattaneo', 'country' => 'IT'],
                    ['first' => 'Jai', 'last' => 'Hindley', 'country' => 'AU'],
                    ['first' => 'Florian', 'last' => 'Lipowitz', 'country' => 'DE'],
                    ['first' => 'Giulio', 'last' => 'Pellizzari', 'country' => 'IT'],
                    ['first' => 'Primož', 'last' => 'Roglič', 'country' => 'SI'],
                ],
            ],
            'SOQ' => [
                'name' => 'Soudal Quick-Step',
                'country' => 'BE',
                'riders' => [
                    ['first' => 'Mikel', 'last' => 'Landa', 'country' => 'ES'],
                    ['first' => 'Gianmarco', 'last' => 'Garofoli', 'country' => 'IT'],
                    ['first' => 'Gil', 'last' => 'Gelders', 'country' => 'BE'],
                    ['first' => 'Maximilian', 'last' => 'Schachmann', 'country' => 'DE'],
                    ['first' => 'Mauri', 'last' => 'Vansevenant', 'country' => 'BE'],
                    ['first' => 'Louis', 'last' => 'Vervaeke', 'country' => 'BE'],
                    ['first' => 'Filippo', 'last' => 'Zana', 'country' => 'IT'],
                ],
            ],
            'JAY' => [
                'name' => 'Team Jayco AlUla',
                'country' => 'AU',
                'riders' => [
                    ['first' => 'Michael', 'last' => 'Matthews', 'country' => 'AU'],
                    ['first' => 'Koen', 'last' => 'Bouwman', 'country' => 'NL'],
                    ['first' => 'Alessandro', 'last' => 'Covi', 'country' => 'IT'],
                    ['first' => 'Davide', 'last' => 'De Pretto', 'country' => 'IT'],
                    ['first' => 'Felix', 'last' => 'Engelhardt', 'country' => 'DE'],
                    ['first' => 'Anders', 'last' => 'Foldager', 'country' => 'DK'],
                    ['first' => 'Alan', 'last' => 'Hatherly', 'country' => 'ZA'],
                ],
            ],
            'TPP' => [
                'name' => 'Team Picnic PostNL',
                'country' => 'NL',
                'riders' => [
                    ['first' => 'James', 'last' => 'Knox', 'country' => 'GB'],
                    ['first' => 'Warren', 'last' => 'Barguil', 'country' => 'FR'],
                    ['first' => 'Alexy', 'last' => 'Faure Prost', 'country' => 'FR'],
                    ['first' => 'Juan Guillermo', 'last' => 'Martinez', 'country' => 'CO'],
                    ['first' => 'Gijs', 'last' => 'Leemreize', 'country' => 'NL'],
                    ['first' => 'Timo', 'last' => 'Roosen', 'country' => 'NL'],
                    ['first' => 'Frank', 'last' => 'Van Den Broek', 'country' => 'NL'],
                ],
            ],
            'PTV' => [
                'name' => 'Team Polti VisitMalta',
                'country' => 'IT',
                'riders' => [
                    ['first' => 'Andrea', 'last' => 'Mifsud', 'country' => 'MT'],
                    ['first' => 'Mattia', 'last' => 'Bais', 'country' => 'IT'],
                    ['first' => 'Ludovico', 'last' => 'Crescioli', 'country' => 'IT'],
                    ['first' => 'Fabrizio', 'last' => 'Crozzolo', 'country' => 'AR'],
                    ['first' => 'Germán Darío', 'last' => 'Gómez', 'country' => 'CO'],
                    ['first' => 'Francisco', 'last' => 'Muñoz', 'country' => 'ES'],
                    ['first' => 'Thomas', 'last' => 'Pesenti', 'country' => 'IT'],
                ],
            ],
            'TVL' => [
                'name' => 'Team Visma | Lease a Bike',
                'country' => 'NL',
                'riders' => [
                    ['first' => 'Davide', 'last' => 'Piganzoli', 'country' => 'IT'],
                    ['first' => 'Bruno', 'last' => 'Armirail', 'country' => 'FR'],
                    ['first' => 'Louis', 'last' => 'Barré', 'country' => 'FR'],
                    ['first' => 'Tijmen', 'last' => 'Graat', 'country' => 'NL'],
                    ['first' => 'Matteo', 'last' => 'Jorgenson', 'country' => 'US'],
                    ['first' => 'Tim', 'last' => 'Rex', 'country' => 'BE'],
                    ['first' => 'Ben', 'last' => 'Tulett', 'country' => 'GB'],
                ],
            ],
            'TUD' => [
                'name' => 'Tudor Pro Cycling Team',
                'country' => 'CH',
                'riders' => [
                    ['first' => 'Michael', 'last' => 'Storer', 'country' => 'AU'],
                    ['first' => 'Marc', 'last' => 'Hirschi', 'country' => 'CH'],
                    ['first' => 'Mathys', 'last' => 'Rondel', 'country' => 'FR'],
                    ['first' => 'Florian', 'last' => 'Stork', 'country' => 'DE'],
                    ['first' => 'Yannis', 'last' => 'Voisard', 'country' => 'CH'],
                    ['first' => 'Larry', 'last' => 'Warbasse', 'country' => 'US'],
                    ['first' => 'Fabian', 'last' => 'Weiss', 'country' => 'CH'],
                ],
            ],
            'URR' => [
                'name' => 'Unibet Rose Rockets',
                'country' => 'BE',
                'riders' => [
                    ['first' => 'Wout', 'last' => 'Poels', 'country' => 'NL'],
                    ['first' => 'Cedrik Bakke', 'last' => 'Christophersen', 'country' => 'NO'],
                    ['first' => 'Odd Christian', 'last' => 'Eiking', 'country' => 'NO'],
                    ['first' => 'Eivind Broholt', 'last' => 'Fougner', 'country' => 'NO'],
                    ['first' => 'Owen', 'last' => 'Geleijn', 'country' => 'NL'],
                    ['first' => 'Sergio', 'last' => 'Meris', 'country' => 'IT'],
                    ['first' => 'Jannis', 'last' => 'Peter', 'country' => 'DE'],
                ],
            ],
            'UXM' => [
                'name' => 'Uno-X Mobility',
                'country' => 'NO',
                'riders' => [
                    ['first' => 'Tobias Halland', 'last' => 'Johannessen', 'country' => 'NO'],
                    ['first' => 'Anthon', 'last' => 'Charmig', 'country' => 'DK'],
                    ['first' => 'Simon', 'last' => 'Dalby', 'country' => 'DK'],
                    ['first' => 'Andreas', 'last' => 'Kron', 'country' => 'DK'],
                    ['first' => 'Johannes', 'last' => 'Kulset', 'country' => 'NO'],
                    ['first' => 'Andreas', 'last' => 'Leknessund', 'country' => 'NO'],
                    ['first' => 'Anders', 'last' => 'Skaarseth', 'country' => 'NO'],
                ],
            ],
            'XAT' => [
                'name' => 'XDS Astana Team',
                'country' => 'KZ',
                'riders' => [
                    ['first' => 'Christian', 'last' => 'Scaroni', 'country' => 'IT'],
                    ['first' => 'Nicola', 'last' => 'Conci', 'country' => 'IT'],
                    ['first' => 'Lorenzo', 'last' => 'Fortunato', 'country' => 'IT'],
                    ['first' => 'Sergio', 'last' => 'Higuita', 'country' => 'CO'],
                    ['first' => 'Harold', 'last' => 'Tejada', 'country' => 'CO'],
                    ['first' => 'Diego', 'last' => 'Ulissi', 'country' => 'IT'],
                    ['first' => 'Simone', 'last' => 'Velasco', 'country' => 'IT'],
                ],
            ],
        ];
    }
}
