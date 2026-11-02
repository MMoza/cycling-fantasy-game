<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domain\ValueObjects\CompetitionType;
use App\Domain\ValueObjects\EditionStatus;
use App\Domain\ValueObjects\ScoringSystemType;
use App\Domain\ValueObjects\StageStatus;
use App\Domain\ValueObjects\StageType;
use App\Infrastructure\Persistence\Models\CompetitionModel;
use App\Infrastructure\Persistence\Models\CompetitionParticipantModel;
use App\Infrastructure\Persistence\Models\CountryModel;
use App\Infrastructure\Persistence\Models\EditionModel;
use App\Infrastructure\Persistence\Models\LeagueModel;
use App\Infrastructure\Persistence\Models\RiderModel;
use App\Infrastructure\Persistence\Models\ScoringSystemModel;
use App\Infrastructure\Persistence\Models\StageModel;
use App\Infrastructure\Persistence\Models\StageParticipantModel;
use App\Infrastructure\Persistence\Models\TeamModel;
use App\Infrastructure\Persistence\Models\TeamRosterModel;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Competición ABIERTA con corredores de prueba para jugar en local.
 *
 * Crea una edición con las etapas sin comenzar (scheduled_start en el futuro),
 * de modo que «Mis pronósticos» y el asistente IA se pueden probar sin bloqueos:
 * formulario con selección libre de corredores, validación de duplicados,
 * botón «Hacer pronóstico con IA» y pre-race también abierto.
 *
 * Uso:
 *   php artisan db:seed --class=TestOpenPredictionsSeeder
 *
 * Login de prueba: carlos@test.com / password (también ana@test.com)
 *
 * Idempotente: se puede ejecutar varias veces sin duplicar datos.
 */
class TestOpenPredictionsSeeder extends Seeder
{
    private const COMPETITION_NAME = 'Carrera Abierta de Prueba';

    private const LEAGUE_NAME = 'Liga Abierta de Prueba';

    private const TEAMS = [
        ['name' => 'Equipo Trueno', 'abbr' => 'TRU'],
        ['name' => 'Equipo Ciclón', 'abbr' => 'CIC'],
        ['name' => 'Equipo Estela', 'abbr' => 'EST'],
    ];

    private const RIDERS = [
        ['first' => 'Álvaro', 'last' => 'Nieto', 'team' => 0],
        ['first' => 'Beatriz', 'last' => 'Cordero', 'team' => 0],
        ['first' => 'Carlos', 'last' => 'Iriarte', 'team' => 0],
        ['first' => 'Diana', 'last' => 'Salas', 'team' => 0],
        ['first' => 'Eduardo', 'last' => 'Vidal', 'team' => 0],
        ['first' => 'Fernando', 'last' => 'Otero', 'team' => 1],
        ['first' => 'Gloria', 'last' => 'Pardo', 'team' => 1],
        ['first' => 'Héctor', 'last' => 'Quintanilla', 'team' => 1],
        ['first' => 'Inés', 'last' => 'Barrios', 'team' => 1],
        ['first' => 'Javier', 'last' => 'Robles', 'team' => 1],
        ['first' => 'Karina', 'last' => 'Ledesma', 'team' => 2],
        ['first' => 'Luis', 'last' => 'Bermejo', 'team' => 2],
        ['first' => 'Marta', 'last' => 'Cifuentes', 'team' => 2],
        ['first' => 'Óscar', 'last' => 'Duarte', 'team' => 2],
        ['first' => 'Paula', 'last' => 'Escudero', 'team' => 2],
    ];

    private const USERS = [
        ['name' => 'Carlos Pérez', 'email' => 'carlos@test.com'],
        ['name' => 'Ana García', 'email' => 'ana@test.com'],
    ];

    /**
     * Etapas abiertas: difficulty 1/2/3 para que apliquen las reglas del
     * sistema Standard (ganador, 2º, 3º y combativo por dificultad + líder GC).
     */
    private const STAGES = [
        [
            'number' => 1,
            'name' => 'Ribera del Sol → Valle Verde',
            'type' => StageType::Flat,
            'distance' => 178.4,
            'elevation' => 650,
            'difficulty' => 1,
            'origin' => 'Ribera del Sol',
            'destination' => 'Valle Verde',
            'starts_in_days' => 1,
        ],
        [
            'number' => 2,
            'name' => 'Valle Verde → Puerto del Águila',
            'type' => StageType::Hill,
            'distance' => 165.2,
            'elevation' => 2400,
            'difficulty' => 2,
            'origin' => 'Valle Verde',
            'destination' => 'Puerto del Águila',
            'starts_in_days' => 2,
        ],
        [
            'number' => 3,
            'name' => 'Puerto del Águila → Cima Estelar',
            'type' => StageType::HighMountain,
            'distance' => 189.6,
            'elevation' => 4100,
            'difficulty' => 3,
            'origin' => 'Puerto del Águila',
            'destination' => 'Cima Estelar',
            'starts_in_days' => 3,
        ],
    ];

    public function run(): void
    {
        $this->call([CountrySeeder::class, ScoringSystemSeeder::class]);

        CountryModel::firstOrCreate(['id' => 'ES'], ['name' => 'España']);

        $teams = $this->seedTeams();
        $riders = $this->seedRiders($teams);
        $users = $this->seedUsers();

        [$competition, $edition] = $this->seedCompetition();
        $this->seedEditionParticipants($competition, $edition, $teams, $riders);

        $league = $this->seedLeague($edition, $users);
        $stages = $this->seedStages($edition, $teams, $riders);

        $this->command?->info('TestOpenPredictionsSeeder: competición abierta lista.');
        $this->command?->info('Login: carlos@test.com / password');
        $this->command?->info("Liga: {$league->name} (invite {$league->invite_code})");

        foreach ($stages as $stage) {
            $this->command?->info("Etapa {$stage->number} ({$stage->name}): /leagues/{$league->id}/stage/{$stage->id}");
        }

        $this->command?->info("Pre-race: /leagues/{$league->id}/predictions/pre-race");
    }

    /** @return array<int, TeamModel> */
    private function seedTeams(): array
    {
        $teams = [];

        foreach (self::TEAMS as $team) {
            $teams[] = TeamModel::firstOrCreate(
                ['name' => $team['name']],
                [
                    'id' => Str::uuid()->toString(),
                    'abbreviation' => $team['abbr'],
                    'country_id' => 'ES',
                ],
            );
        }

        return $teams;
    }

    /**
     * @param  array<int, TeamModel>  $teams
     * @return array<int, RiderModel>
     */
    private function seedRiders(array $teams): array
    {
        $riders = [];

        foreach (self::RIDERS as $rider) {
            $model = RiderModel::firstOrCreate(
                ['first_name' => $rider['first'], 'last_name' => $rider['last']],
                [
                    'id' => Str::uuid()->toString(),
                    'country_id' => 'ES',
                ],
            );

            TeamRosterModel::firstOrCreate(
                ['team_id' => $teams[$rider['team']]->id, 'rider_id' => $model->id, 'year' => now()->year],
                ['id' => Str::uuid()->toString()],
            );

            $riders[] = $model;
        }

        return $riders;
    }

    /** @return array<int, User> */
    private function seedUsers(): array
    {
        $users = [];

        foreach (self::USERS as $user) {
            $users[] = User::firstOrCreate(
                ['email' => $user['email']],
                ['name' => $user['name'], 'password' => bcrypt('password')],
            );
        }

        return $users;
    }

    /** @return array{0: CompetitionModel, 1: EditionModel} */
    private function seedCompetition(): array
    {
        $competition = CompetitionModel::firstOrCreate(
            ['name' => self::COMPETITION_NAME],
            [
                'id' => Str::uuid()->toString(),
                'type' => CompetitionType::GC,
                'country_id' => 'ES',
                'active' => true,
            ],
        );

        $edition = EditionModel::firstOrCreate(
            ['competition_id' => $competition->id, 'year' => now()->year],
            [
                'id' => Str::uuid()->toString(),
                'start_date' => now()->addDay()->toDateString(),
                'end_date' => now()->addDays(5)->toDateString(),
                'status' => EditionStatus::Upcoming,
            ],
        );

        return [$competition, $edition];
    }

    /**
     * @param  array<int, TeamModel>  $teams
     * @param  array<int, RiderModel>  $riders
     */
    private function seedEditionParticipants(CompetitionModel $competition, EditionModel $edition, array $teams, array $riders): void
    {
        foreach ($riders as $index => $rider) {
            $team = $teams[intdiv($index, 5)];

            CompetitionParticipantModel::firstOrCreate(
                [
                    'competition_id' => $competition->id,
                    'edition_id' => $edition->id,
                    'team_id' => $team->id,
                    'rider_id' => $rider->id,
                ],
                ['id' => Str::uuid()->toString()],
            );
        }
    }

    /**
     * @param  array<int, User>  $users
     */
    private function seedLeague(EditionModel $edition, array $users): LeagueModel
    {
        $scoringSystem = ScoringSystemModel::where('type', ScoringSystemType::Standard)->firstOrFail();

        $league = LeagueModel::firstOrCreate(
            ['name' => self::LEAGUE_NAME, 'edition_id' => $edition->id],
            [
                'id' => Str::uuid()->toString(),
                'scoring_system_id' => $scoringSystem->id,
                'owner_id' => $users[0]->id,
                'invite_code' => strtoupper(Str::random(8)),
                'max_players' => 20,
                'is_public' => true,
            ],
        );

        foreach ($users as $i => $user) {
            if (! $league->users()->where('user_id', $user->id)->exists()) {
                $league->users()->attach($user->id, [
                    'id' => Str::uuid()->toString(),
                    'role' => $i === 0 ? 'owner' : 'member',
                ]);
            }
        }

        return $league;
    }

    /**
     * @param  array<int, TeamModel>  $teams
     * @param  array<int, RiderModel>  $riders
     * @return array<int, StageModel>
     */
    private function seedStages(EditionModel $edition, array $teams, array $riders): array
    {
        $stages = [];

        foreach (self::STAGES as $spec) {
            $stage = StageModel::firstOrCreate(
                ['edition_id' => $edition->id, 'number' => $spec['number']],
                [
                    'id' => Str::uuid()->toString(),
                    'name' => $spec['name'],
                    'date' => now()->addDays($spec['starts_in_days'])->toDateString(),
                    'scheduled_start' => now()->addDays($spec['starts_in_days'])->setTime(13, 0),
                    'type' => $spec['type'],
                    'distance' => $spec['distance'],
                    'elevation_gain' => $spec['elevation'],
                    'difficulty' => $spec['difficulty'],
                    'origin' => $spec['origin'],
                    'destination' => $spec['destination'],
                    'status' => StageStatus::Upcoming,
                ],
            );

            foreach ($riders as $index => $rider) {
                StageParticipantModel::firstOrCreate(
                    ['stage_id' => $stage->id, 'rider_id' => $rider->id],
                    ['id' => Str::uuid()->toString(), 'team_id' => $teams[intdiv($index, 5)]->id],
                );
            }

            $stages[] = $stage;
        }

        return $stages;
    }
}
