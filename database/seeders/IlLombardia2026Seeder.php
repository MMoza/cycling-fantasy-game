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
 * Startlist parcial de Il Lombardía 2026.
 *
 * Fuente: https://www.procyclingstats.com/race/il-lombardia-2026-result/startlist
 * 33 corredores confirmados (aún hay pocos equipos con corredores confirmados).
 *
 * Para actualizar: ampliar `ridersByTeam` con los corredores nuevos (formato
 * ['first' => '...', 'last' => '...', 'country' => 'XX']) y volver a ejecutar
 * el seeder. Los corredores ya existentes no se duplican y los participantes
 * de la edición se reconstruyen con el startlist actual.
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
     * Equipos y corredores confirmados en el startlist de PCS.
     *
     * Los equipos sin `riders` están confirmados en PCS pero aún no tienen
     * corredores asignados: se crean para facilitar la actualización futura.
     *
     * @return array<string, array{name: string, country: string, riders: array<int, array{first: string, last: string, country: string}>}>
     */
    private function startlist(): array
    {
        return [
            'LTD' => [
                'name' => 'Lotto Intermarché',
                'country' => 'BE',
                'riders' => [
                    ['first' => 'Lennert', 'last' => 'Van Eetvelt', 'country' => 'BE'],
                    ['first' => 'Jarno', 'last' => 'Widar', 'country' => 'BE'],
                ],
            ],
            'DCM' => [
                'name' => 'Decathlon CMA CGM Team',
                'country' => 'FR',
                'riders' => [
                    ['first' => 'Nicolas', 'last' => 'Prodhomme', 'country' => 'FR'],
                    ['first' => 'Tiesj', 'last' => 'Benoot', 'country' => 'BE'],
                    ['first' => 'Paul', 'last' => 'Seixas', 'country' => 'FR'],
                ],
            ],
            'ADC' => [
                'name' => 'Alpecin - Premier Tech',
                'country' => 'BE',
                'riders' => [],
            ],
            'TBV' => [
                'name' => 'Bahrain - Victorious',
                'country' => 'BH',
                'riders' => [
                    ['first' => 'Damiano', 'last' => 'Caruso', 'country' => 'IT'],
                    ['first' => 'Pello', 'last' => 'Bilbao', 'country' => 'ES'],
                    ['first' => 'Antonio', 'last' => 'Tiberi', 'country' => 'IT'],
                    ['first' => 'Afonso', 'last' => 'Eulalio', 'country' => 'PT'],
                ],
            ],
            'IGD' => [
                'name' => 'Netcompany INEOS',
                'country' => 'GB',
                'riders' => [],
            ],
            'LTK' => [
                'name' => 'Lidl - Trek',
                'country' => 'US',
                'riders' => [
                    ['first' => 'Juan', 'last' => 'Ayuso', 'country' => 'ES'],
                    ['first' => 'Giulio', 'last' => 'Ciccone', 'country' => 'IT'],
                    ['first' => 'Bauke', 'last' => 'Mollema', 'country' => 'NL'],
                ],
            ],
            'MOV' => [
                'name' => 'Movistar Team',
                'country' => 'ES',
                'riders' => [],
            ],
            'NSN' => [
                'name' => 'NSN Cycling Team',
                'country' => 'NL',
                'riders' => [],
            ],
            'RBH' => [
                'name' => 'Red Bull - BORA - hansgrohe',
                'country' => 'DE',
                'riders' => [
                    ['first' => 'Remco', 'last' => 'Evenepoel', 'country' => 'BE'],
                    ['first' => 'Giulio', 'last' => 'Pellizzari', 'country' => 'IT'],
                ],
            ],
            'SOQ' => [
                'name' => 'Soudal Quick-Step',
                'country' => 'BE',
                'riders' => [],
            ],
            'JAY' => [
                'name' => 'Team Jayco AlUla',
                'country' => 'AU',
                'riders' => [],
            ],
            'TPP' => [
                'name' => 'Team Picnic PostNL',
                'country' => 'NL',
                'riders' => [
                    ['first' => 'Warren', 'last' => 'Barguil', 'country' => 'FR'],
                    ['first' => 'Frank', 'last' => 'Van Den Broek', 'country' => 'NL'],
                    ['first' => 'Alexy', 'last' => 'Faure Prost', 'country' => 'FR'],
                    ['first' => 'James', 'last' => 'Knox', 'country' => 'GB'],
                    ['first' => 'Gijs', 'last' => 'Leemreize', 'country' => 'NL'],
                    ['first' => 'Juan Guillermo', 'last' => 'Martinez', 'country' => 'CO'],
                    ['first' => 'Timo', 'last' => 'Roosen', 'country' => 'NL'],
                ],
            ],
            'TVL' => [
                'name' => 'Team Visma | Lease a Bike',
                'country' => 'NL',
                'riders' => [
                    ['first' => 'Ben', 'last' => 'Tulett', 'country' => 'GB'],
                    ['first' => 'Davide', 'last' => 'Piganzoli', 'country' => 'IT'],
                    ['first' => 'Bruno', 'last' => 'Armirail', 'country' => 'FR'],
                    ['first' => 'Louis', 'last' => 'Barré', 'country' => 'FR'],
                    ['first' => 'Matteo', 'last' => 'Jorgenson', 'country' => 'US'],
                ],
            ],
            'UAD' => [
                'name' => 'UAE Team Emirates - XRG',
                'country' => 'AE',
                'riders' => [
                    ['first' => 'Brandon', 'last' => 'McNulty', 'country' => 'US'],
                    ['first' => 'Domen', 'last' => 'Novak', 'country' => 'SI'],
                    ['first' => 'Jan', 'last' => 'Christen', 'country' => 'CH'],
                ],
            ],
            'UXM' => [
                'name' => 'Uno-X Mobility',
                'country' => 'NO',
                'riders' => [],
            ],
            'XAT' => [
                'name' => 'XDS Astana Team',
                'country' => 'KZ',
                'riders' => [],
            ],
            'EFE' => [
                'name' => 'EF Education - EasyPost',
                'country' => 'US',
                'riders' => [
                    ['first' => 'Ben', 'last' => 'Healy', 'country' => 'IE'],
                ],
            ],
            'GFC' => [
                'name' => 'Groupama - FDJ United',
                'country' => 'FR',
                'riders' => [
                    ['first' => 'Valentin', 'last' => 'Madouas', 'country' => 'FR'],
                ],
            ],
            'TUD' => [
                'name' => 'Tudor Pro Cycling Team',
                'country' => 'CH',
                'riders' => [
                    ['first' => 'Mathys', 'last' => 'Rondel', 'country' => 'FR'],
                ],
            ],
            'BCF' => [
                'name' => 'Bardiani CSF 7 Saber',
                'country' => 'IT',
                'riders' => [],
            ],
            'Q36' => [
                'name' => 'Pinarello Q36.5 Pro Cycling Team',
                'country' => 'CH',
                'riders' => [
                    ['first' => 'Tom', 'last' => 'Pidcock', 'country' => 'GB'],
                ],
            ],
            'COF' => [
                'name' => 'Cofidis',
                'country' => 'FR',
                'riders' => [],
            ],
            'MBH' => [
                'name' => 'MBH Bank CSB Telecom Fort',
                'country' => 'HU',
                'riders' => [],
            ],
            'PTV' => [
                'name' => 'Team Polti VisitMalta',
                'country' => 'IT',
                'riders' => [],
            ],
            'URR' => [
                'name' => 'Unibet Rose Rockets',
                'country' => 'BE',
                'riders' => [],
            ],
        ];
    }
}
