<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domain\ValueObjects\ActivityLogType;
use App\Domain\ValueObjects\CompetitionType;
use App\Domain\ValueObjects\EditionStatus;
use App\Domain\ValueObjects\PredictionCategory;
use App\Domain\ValueObjects\PredictionType;
use App\Domain\ValueObjects\ScoringSystemType;
use App\Domain\ValueObjects\StageStatus;
use App\Domain\ValueObjects\StageType;
use App\Infrastructure\Persistence\Models\ActivityLogModel;
use App\Infrastructure\Persistence\Models\CompetitionModel;
use App\Infrastructure\Persistence\Models\CompetitionParticipantModel;
use App\Infrastructure\Persistence\Models\CountryModel;
use App\Infrastructure\Persistence\Models\EditionModel;
use App\Infrastructure\Persistence\Models\LeagueModel;
use App\Infrastructure\Persistence\Models\NotificationModel;
use App\Infrastructure\Persistence\Models\PredictionModel;
use App\Infrastructure\Persistence\Models\RiderModel;
use App\Infrastructure\Persistence\Models\ScoringSystemModel;
use App\Infrastructure\Persistence\Models\StageModel;
use App\Infrastructure\Persistence\Models\TeamModel;
use App\Infrastructure\Persistence\Models\TeamRosterModel;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Escenario de datos para probar la interfaz completa en local.
 *
 * Crea, sobre la base de TestCompetitionSeeder (3 etapas finalizadas con
 * resultados, pronósticos y puntuación real vía race:rebuild-scores):
 *  - Equipos, corredores, rosters y participantes de edición
 *  - Etapa próxima con cuenta atrás (verde si has pronosticado, ámbar si no)
 *  - Ligas de la temporada para el selector del dashboard (incluida una de
 *    otra temporada y una carrera de un día donde se oculta PREDICTOR)
 *  - Ligas públicas ajenas para el buscador
 *  - Activity logs, notificaciones y datos de perfil (online, invitaciones)
 *
 * Uso:
 *   php artisan db:seed --class=TestUIScenarioSeeder
 *
 * Login de prueba: carlos@test.com / password (también ana@, luis@, maria@…)
 */
class TestUIScenarioSeeder extends Seeder
{
    private const TEAMS = [
        ['name' => 'Equipo Trueno', 'abbr' => 'TRU'],
        ['name' => 'Equipo Ciclón', 'abbr' => 'CIC'],
        ['name' => 'Equipo Estela', 'abbr' => 'EST'],
    ];

    private const RIDERS = [
        ['first' => 'Adrián', 'last' => 'Velázquez', 'team' => 0],
        ['first' => 'Bruno', 'last' => 'Salgado', 'team' => 0],
        ['first' => 'Cristian', 'last' => 'Ferrer', 'team' => 0],
        ['first' => 'Diego', 'last' => 'Alonso', 'team' => 0],
        ['first' => 'Elena', 'last' => 'Rivas', 'team' => 1],
        ['first' => 'Fabián', 'last' => 'Ortega', 'team' => 1],
        ['first' => 'Gabriel', 'last' => 'Soto', 'team' => 1],
        ['first' => 'Hugo', 'last' => 'Cabrera', 'team' => 1],
        ['first' => 'Irene', 'last' => 'Vidal', 'team' => 2],
        ['first' => 'Javier', 'last' => 'Lozano', 'team' => 2],
        ['first' => 'Karim', 'last' => 'Nasser', 'team' => 2],
        ['first' => 'Lucas', 'last' => 'Ibarra', 'team' => 2],
    ];

    private const USERS = [
        ['name' => 'Carlos Pérez', 'email' => 'carlos@test.com'],
        ['name' => 'Ana García', 'email' => 'ana@test.com'],
        ['name' => 'Luis Martínez', 'email' => 'luis@test.com'],
        ['name' => 'María López', 'email' => 'maria@test.com'],
        ['name' => 'Marco Díaz', 'email' => 'marco@test.com'],
        ['name' => 'Sofía Torres', 'email' => 'sofia@test.com'],
    ];

    public function run(): void
    {
        $this->call([CountrySeeder::class, ScoringSystemSeeder::class]);

        CountryModel::firstOrCreate(['id' => 'ES'], ['name' => 'España']);

        $teams = $this->seedTeams();
        $riders = $this->seedRiders($teams);

        // Escenario principal: 'Vuelta de Prueba 2026' con etapas finalizadas,
        // resultados, pronósticos y puntuación real.
        $this->call(TestCompetitionSeeder::class);

        $users = $this->seedUsers();
        $mainLeague = LeagueModel::where('name', 'Liga de Prueba')->firstOrFail();
        $mainEdition = $mainLeague->edition;

        $this->seedEditionParticipants($mainEdition, $riders, $teams);
        $this->seedUpcomingStages($mainLeague, $mainEdition, $riders);
        $this->seedSeasonLeagues($users, $riders);
        $this->seedPublicLeagues($users);
        $this->seedUserProfiles($users);
        $this->seedActivityLogs($mainLeague);
        $this->seedNotifications($users, $mainLeague);

        $this->command?->info('TestUIScenarioSeeder: listo. Login carlos@test.com / password');
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
     * @return array<string, RiderModel>
     */
    private function seedRiders(array $teams): array
    {
        $riders = [];

        foreach (self::RIDERS as $rider) {
            $key = $rider['first'].' '.$rider['last'];

            $riders[$key] = RiderModel::firstOrCreate(
                ['first_name' => $rider['first'], 'last_name' => $rider['last']],
                ['id' => Str::uuid()->toString(), 'country_id' => 'ES'],
            );

            TeamRosterModel::firstOrCreate(
                ['team_id' => $teams[$rider['team']]->id, 'rider_id' => $riders[$key]->id, 'year' => (int) date('Y')],
                ['id' => Str::uuid()->toString()],
            );
        }

        return $riders;
    }

    /** @return array<string, User> */
    private function seedUsers(): array
    {
        $users = [];

        foreach (self::USERS as $userData) {
            $users[$userData['email']] = User::firstOrCreate(
                ['email' => $userData['email']],
                ['name' => $userData['name'], 'password' => bcrypt('password')],
            );
        }

        return $users;
    }

    /**
     * @param  array<string, RiderModel>  $riders
     * @param  array<int, TeamModel>  $teams
     */
    private function seedEditionParticipants(EditionModel $edition, array $riders, array $teams): void
    {
        foreach (self::RIDERS as $rider) {
            CompetitionParticipantModel::firstOrCreate(
                [
                    'edition_id' => $edition->id,
                    'team_id' => $teams[$rider['team']]->id,
                    'rider_id' => $riders[$rider['first'].' '.$rider['last']]->id,
                ],
                ['id' => Str::uuid()->toString(), 'competition_id' => $edition->competition_id],
            );
        }
    }

    /**
     * Etapa 4 próxima con cuenta atrás: verde para quien ha pronosticado,
     * ámbar para quien no.
     *
     * @param  array<string, RiderModel>  $riders
     */
    private function seedUpcomingStages(LeagueModel $league, EditionModel $edition, array $riders): void
    {
        $stage = StageModel::firstOrCreate(
            ['edition_id' => $edition->id, 'number' => 4],
            [
                'id' => Str::uuid()->toString(),
                'name' => 'Etapa con cuenta atrás',
                'date' => now()->addDay()->format('Y-m-d'),
                'scheduled_start' => now()->addHours(6),
                'type' => StageType::Hill,
                'distance' => 172.5,
                'origin' => 'Villa Norte',
                'destination' => 'Alto del Reloj',
                'difficulty' => 3,
                'elevation_gain' => 2200,
                'status' => StageStatus::Upcoming,
            ],
        );

        $carlos = User::where('email', 'carlos@test.com')->firstOrFail();
        $riderIds = collect($riders)->pluck('id')->values();

        PredictionModel::firstOrCreate(
            [
                'user_id' => $carlos->id,
                'league_id' => $league->id,
                'category' => PredictionCategory::StageWinner->value,
                'stage_id' => $stage->id,
            ],
            [
                'id' => Str::uuid()->toString(),
                'type' => PredictionType::PreStage->value,
                'prediction_value' => ['rider_id' => $riderIds[0]],
                'created_at' => now(),
                'updated_at' => now(),
            ],
        );
    }

    /**
     * Ligas de la temporada para el selector del dashboard: dos del año en
     * curso (una sin etapas), una de otra temporada (fallback) y una carrera
     * de un día donde se oculta PREDICTOR.
     *
     * @param  array<string, User>  $users
     * @param  array<string, RiderModel>  $riders
     */
    private function seedSeasonLeagues(array $users, array $riders): void
    {
        $scoringSystem = ScoringSystemModel::where('type', ScoringSystemType::Standard)->firstOrFail();
        $year = (int) date('Y');

        $specs = [
            [
                'competition' => 'Gran Fondo Mock '.$year,
                'type' => CompetitionType::GC,
                'league' => 'Liga Montaña Mock',
                'year' => $year,
                'start' => now()->addDays(10),
                'status' => EditionStatus::Upcoming,
            ],
            [
                'competition' => 'Vuelta Costa Mock '.$year,
                'type' => CompetitionType::GC,
                'league' => 'Liga Costa Mock',
                'year' => $year,
                'start' => now()->addMonth(),
                'status' => EditionStatus::Upcoming,
            ],
            [
                'competition' => 'Monumento Mock '.$year,
                'type' => CompetitionType::Monument,
                'league' => 'Clásicas Mock',
                'year' => $year,
                'start' => now()->addWeeks(2),
                'status' => EditionStatus::Upcoming,
                'one_day' => true,
            ],
            [
                'competition' => 'Tour Histórico Mock',
                'type' => CompetitionType::GC,
                'league' => 'Liga Histórica Mock',
                'year' => $year - 1,
                'start' => now()->subYear(),
                'status' => EditionStatus::Finished,
            ],
        ];

        foreach ($specs as $spec) {
            $competition = CompetitionModel::firstOrCreate(
                ['name' => $spec['competition']],
                [
                    'id' => Str::uuid()->toString(),
                    'type' => $spec['type'],
                    'country_id' => 'ES',
                    'active' => true,
                ],
            );

            $edition = EditionModel::firstOrCreate(
                ['competition_id' => $competition->id, 'year' => $spec['year']],
                [
                    'id' => Str::uuid()->toString(),
                    'start_date' => $spec['start']->format('Y-m-d'),
                    'end_date' => $spec['start']->addDays(10)->format('Y-m-d'),
                    'status' => $spec['status'],
                ],
            );

            $league = LeagueModel::firstOrCreate(
                ['name' => $spec['league'], 'edition_id' => $edition->id],
                [
                    'id' => Str::uuid()->toString(),
                    'scoring_system_id' => $scoringSystem->id,
                    'owner_id' => $users['carlos@test.com']->id,
                    'invite_code' => strtoupper(Str::random(8)),
                    'max_players' => 20,
                    'is_public' => true,
                ],
            );

            foreach ($users as $user) {
                if (! $league->users()->where('user_id', $user->id)->exists()) {
                    $league->users()->attach($user->id, [
                        'id' => Str::uuid()->toString(),
                        'role' => $user->id === $league->owner_id ? 'owner' : 'member',
                    ]);
                }
            }

            if ($edition->stages()->count() === 0) {
                StageModel::create([
                    'id' => Str::uuid()->toString(),
                    'edition_id' => $edition->id,
                    'number' => 1,
                    'name' => $spec['one_day'] ?? false ? 'Gran Fondo Mock' : 'Primera etapa Mock',
                    'date' => $spec['start']->format('Y-m-d'),
                    'scheduled_start' => $spec['start']->addHours(10),
                    'type' => StageType::Hill,
                    'distance' => $spec['one_day'] ?? false ? 241.0 : 165.0,
                    'origin' => 'Ciudad Mock',
                    'destination' => 'Puerto Mock',
                    'difficulty' => 3,
                    'elevation_gain' => 2500,
                    'status' => StageStatus::Upcoming,
                ]);
            }
        }
    }

    /**
     * Ligas públicas ajenas para el buscador (is_joined = false).
     *
     * @param  array<string, User>  $users
     */
    private function seedPublicLeagues(array $users): void
    {
        $scoringSystem = ScoringSystemModel::where('type', ScoringSystemType::Conservative)->first()
            ?? ScoringSystemModel::where('type', ScoringSystemType::Standard)->firstOrFail();

        $owners = ['marco@test.com', 'sofia@test.com'];

        foreach ($owners as $index => $email) {
            $owner = $users[$email];

            $competition = CompetitionModel::firstOrCreate(
                ['name' => 'Competición Pública '.($index + 1)],
                ['id' => Str::uuid()->toString(), 'type' => CompetitionType::GC, 'country_id' => 'ES', 'active' => true],
            );

            $edition = EditionModel::firstOrCreate(
                ['competition_id' => $competition->id, 'year' => (int) date('Y')],
                [
                    'id' => Str::uuid()->toString(),
                    'start_date' => now()->addDays(30 + $index * 10)->format('Y-m-d'),
                    'end_date' => now()->addDays(45 + $index * 10)->format('Y-m-d'),
                    'status' => EditionStatus::Upcoming,
                ],
            );

            $league = LeagueModel::firstOrCreate(
                ['name' => 'Liga Pública '.($index + 1).' Mock', 'edition_id' => $edition->id],
                [
                    'id' => Str::uuid()->toString(),
                    'scoring_system_id' => $scoringSystem->id,
                    'owner_id' => $owner->id,
                    'invite_code' => strtoupper(Str::random(8)),
                    'max_players' => 20,
                    'is_public' => true,
                ],
            );

            if (! $league->users()->where('user_id', $owner->id)->exists()) {
                $league->users()->attach($owner->id, [
                    'id' => Str::uuid()->toString(),
                    'role' => 'owner',
                ]);
            }
        }
    }

    /**
     * @param  array<string, User>  $users
     */
    private function seedUserProfiles(array $users): void
    {
        $carlos = $users['carlos@test.com'];

        DB::table('users')->where('id', $carlos->id)->update([
            'last_active_at' => now(),
            'avatar' => 'https://i.pravatar.cc/150?img=12',
        ]);

        DB::table('users')->where('id', $users['ana@test.com']->id)->update([
            'last_active_at' => now()->subMinutes(5),
            'invited_by' => $carlos->id,
        ]);

        DB::table('users')->where('id', $users['luis@test.com']->id)->update([
            'last_active_at' => null,
            'invited_by' => $carlos->id,
        ]);

        DB::table('users')->where('id', $users['maria@test.com']->id)->update([
            'last_active_at' => now()->subHours(3),
        ]);

        DB::table('users')->whereIn('id', [$users['marco@test.com']->id, $users['sofia@test.com']->id])->update([
            'invited_by' => $carlos->id,
        ]);
    }

    private function seedActivityLogs(LeagueModel $league): void
    {
        $logs = [
            [ActivityLogType::CompetitionStart, 'Competición iniciada', '¡Arranca la Vuelta de Prueba 2026!', 72],
            [ActivityLogType::StageStart, 'Etapa 1 en directo', 'Prólogo urbano — 8,5 km', 48],
            [ActivityLogType::StageEnd, 'Etapa 1 finalizada', 'Resultado oficial publicado', 47],
            [ActivityLogType::PredictionsLocked, 'Pronósticos cerrados', 'Los pronósticos de la etapa 3 ya están bloqueados', 24],
            [ActivityLogType::StageEnd, 'Etapa 3 finalizada', 'Etapa reina — Pico Legendario', 2],
        ];

        foreach ($logs as [$type, $title, $description, $hoursAgo]) {
            ActivityLogModel::firstOrCreate(
                ['league_id' => $league->id, 'title' => $title],
                [
                    'id' => Str::uuid()->toString(),
                    'type' => $type,
                    'description' => $description,
                    'data' => ['source' => 'TestUIScenarioSeeder'],
                    'created_at' => now()->subHours($hoursAgo),
                    'updated_at' => now()->subHours($hoursAgo),
                ],
            );
        }
    }

    /**
     * @param  array<string, User>  $users
     */
    private function seedNotifications(array $users, LeagueModel $league): void
    {
        $position = 0;

        foreach ($users as $user) {
            $position++;

            NotificationModel::firstOrCreate(
                ['user_id' => $user->id, 'league_id' => $league->id, 'title' => 'Vuelta de Prueba 2026 ha terminado'],
                [
                    'id' => Str::uuid()->toString(),
                    'type' => 'competition_ended',
                    'description' => 'Has finalizado en posición #'.$position.' con '.(100 - ($position - 1) * 15).' puntos.',
                    'data' => [
                        'user_name' => $user->name,
                        'user_avatar' => $user->avatar,
                        'position' => $position,
                        'total_points' => 100 - ($position - 1) * 15,
                        'stages_won' => $position === 1 ? 2 : 0,
                        'competition_name' => 'Vuelta de Prueba 2026',
                        'competition_year' => (int) date('Y'),
                        'best_stage' => $position === 1 ? ['number' => 3, 'name' => 'Etapa reina de alta montaña', 'points' => 45] : null,
                        'is_official' => false,
                        'league_id' => $league->id,
                    ],
                    'read_at' => $position === 1 ? now()->subDay() : null,
                    'created_at' => now()->subHours(30 - $position),
                    'updated_at' => now()->subHours(30 - $position),
                ],
            );
        }
    }
}
