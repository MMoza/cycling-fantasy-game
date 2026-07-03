<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domain\ValueObjects\CompetitionType;
use App\Domain\ValueObjects\EditionStatus;
use App\Domain\ValueObjects\PredictionCategory;
use App\Domain\ValueObjects\PredictionType;
use App\Domain\ValueObjects\ScoringSystemType;
use App\Domain\ValueObjects\StageStatus;
use App\Domain\ValueObjects\StageType;
use App\Infrastructure\Persistence\Models\CompetitionModel;
use App\Infrastructure\Persistence\Models\EditionModel;
use App\Infrastructure\Persistence\Models\FinalClassificationModel;
use App\Infrastructure\Persistence\Models\LeagueModel;
use App\Infrastructure\Persistence\Models\RiderModel;
use App\Infrastructure\Persistence\Models\ScoreEventModel;
use App\Infrastructure\Persistence\Models\ScoringSystemModel;
use App\Infrastructure\Persistence\Models\StageResultModel;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TestCompetitionSeeder extends Seeder
{
    public function run(): void
    {
        $countryId = DB::table('countries')->first()->id;

        $competition = CompetitionModel::firstOrCreate(
            ['name' => 'Vuelta de Prueba 2026'],
            [
                'id' => Str::uuid()->toString(),
                'type' => CompetitionType::GrandTour,
                'country_id' => $countryId,
                'active' => true,
            ],
        );

        $today = now()->format('Y-m-d');

        $edition = EditionModel::firstOrCreate(
            ['competition_id' => $competition->id, 'year' => now()->year],
            [
                'id' => Str::uuid()->toString(),
                'start_date' => $today,
                'end_date' => $today,
                'status' => EditionStatus::Ongoing,
            ],
        );

        $riders = RiderModel::inRandomOrder()->limit(10)->get();
        $riderIds = $riders->pluck('id')->toArray();

        $teams = DB::table('teams')->inRandomOrder()->limit(3)->get();
        $teamIds = $teams->pluck('id')->toArray();

        $stageIds = [];
        $stageData = [
            ['number' => 1, 'name' => 'Prólogo urbano', 'type' => StageType::TimeTrial, 'distance' => 8.5, 'origin' => 'Plaza Mayor', 'destination' => 'Plaza Mayor', 'difficulty' => 1, 'elevation' => 45],
            ['number' => 2, 'name' => 'Etapa de media montaña', 'type' => StageType::Mountain, 'distance' => 168.3, 'origin' => 'Puerto de Montaña', 'destination' => 'Alto del Mirador', 'difficulty' => 2, 'elevation' => 2850],
            ['number' => 3, 'name' => 'Etapa reina de alta montaña', 'type' => StageType::HighMountain, 'distance' => 195.7, 'origin' => 'Valle Inferior', 'destination' => 'Pico Legendario', 'difficulty' => 3, 'elevation' => 4200],
        ];

        foreach ($stageData as $s) {
            $existing = DB::table('stages')
                ->where('edition_id', $edition->id)
                ->where('number', $s['number'])
                ->first();

            if ($existing) {
                $stageIds[] = $existing->id;
            } else {
                $id = Str::uuid()->toString();
                DB::table('stages')->insert([
                    'id' => $id,
                    'edition_id' => $edition->id,
                    'number' => $s['number'],
                    'name' => $s['name'],
                    'date' => $today,
                    'type' => $s['type']->value,
                    'distance' => $s['distance'],
                    'origin' => $s['origin'],
                    'destination' => $s['destination'],
                    'difficulty' => $s['difficulty'],
                    'elevation_gain' => $s['elevation'],
                    'status' => StageStatus::Finished->value,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $stageIds[] = $id;
            }
        }

        $scoringSystem = ScoringSystemModel::where('type', ScoringSystemType::Standard)->firstOrFail();

        $users = [];
        $userData = [
            ['name' => 'Carlos Pérez', 'email' => 'carlos@test.com'],
            ['name' => 'Ana García', 'email' => 'ana@test.com'],
            ['name' => 'Luis Martínez', 'email' => 'luis@test.com'],
            ['name' => 'María López', 'email' => 'maria@test.com'],
        ];

        foreach ($userData as $data) {
            $users[] = User::firstOrCreate(
                ['email' => $data['email']],
                ['name' => $data['name'], 'password' => bcrypt('password')],
            );
        }

        $league = LeagueModel::firstOrCreate(
            ['name' => 'Liga de Prueba', 'edition_id' => $edition->id],
            [
                'id' => Str::uuid()->toString(),
                'scoring_system_id' => $scoringSystem->id,
                'owner_id' => $users[0]->id,
                'invite_code' => 'TEST'.now()->timestamp,
                'max_players' => 10,
                'is_public' => false,
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

        $now = now();

        // ──────────────────────────────────────────────
        // Pre-race predictions
        // ──────────────────────────────────────────────

        $preRacePredictions = [
            [
                'user_idx' => 0,
                'gc_top_5' => [$riderIds[0], $riderIds[1], $riderIds[2], $riderIds[3], $riderIds[4]],
                'points_winner' => [$riderIds[0], $riderIds[1], $riderIds[2]],
                'mountains_winner' => [$riderIds[2], $riderIds[3], $riderIds[4]],
                'youth_winner' => [$riderIds[5], $riderIds[6], $riderIds[7]],
                'teams_winner' => $teamIds[0],
                'super_combativo' => $riderIds[8],
            ],
            [
                'user_idx' => 1,
                'gc_top_5' => [$riderIds[1], $riderIds[2], $riderIds[0], $riderIds[4], $riderIds[3]],
                'points_winner' => [$riderIds[1], $riderIds[0], $riderIds[2]],
                'mountains_winner' => [$riderIds[3], $riderIds[4], $riderIds[5]],
                'youth_winner' => [$riderIds[6], $riderIds[7], $riderIds[8]],
                'teams_winner' => $teamIds[1],
                'super_combativo' => $riderIds[9],
            ],
            [
                'user_idx' => 2,
                'gc_top_5' => [$riderIds[2], $riderIds[0], $riderIds[1], $riderIds[3], $riderIds[5]],
                'points_winner' => [$riderIds[2], $riderIds[1], $riderIds[0]],
                'mountains_winner' => [$riderIds[4], $riderIds[5], $riderIds[6]],
                'youth_winner' => [$riderIds[7], $riderIds[8], $riderIds[9]],
                'teams_winner' => $teamIds[2],
                'super_combativo' => $riderIds[0],
            ],
            [
                'user_idx' => 3,
                'gc_top_5' => [$riderIds[3], $riderIds[4], $riderIds[5], $riderIds[0], $riderIds[1]],
                'points_winner' => [$riderIds[3], $riderIds[4], $riderIds[5]],
                'mountains_winner' => [$riderIds[0], $riderIds[1], $riderIds[2]],
                'youth_winner' => [$riderIds[8], $riderIds[9], $riderIds[0]],
                'teams_winner' => $teamIds[0],
                'super_combativo' => $riderIds[1],
            ],
        ];

        foreach ($preRacePredictions as $pred) {
            $user = $users[$pred['user_idx']];

            $categories = [
                PredictionCategory::GcTop5->value => ['rider_ids' => $pred['gc_top_5']],
                PredictionCategory::PointsWinner->value => ['rider_ids' => $pred['points_winner']],
                PredictionCategory::MountainsWinner->value => ['rider_ids' => $pred['mountains_winner']],
                PredictionCategory::YouthWinner->value => ['rider_ids' => $pred['youth_winner']],
                PredictionCategory::TeamsWinner->value => ['team_id' => $pred['teams_winner']],
                PredictionCategory::SuperCombativo->value => ['rider_id' => $pred['super_combativo']],
            ];

            foreach ($categories as $category => $value) {
                DB::table('predictions')->updateOrInsert(
                    ['user_id' => $user->id, 'league_id' => $league->id, 'category' => $category, 'stage_id' => null],
                    [
                        'id' => Str::uuid()->toString(),
                        'type' => PredictionType::PreRace->value,
                        'prediction_value' => json_encode($value),
                        'locked_at' => $now,
                        'updated_at' => $now,
                        'created_at' => $now,
                    ],
                );
            }
        }

        // ──────────────────────────────────────────────
        // Stage results
        // Each stage: top 3 riders, one is_gc_leader, one is_combativo
        // ──────────────────────────────────────────────

        $stageResultsConfig = [
            // Stage 1 (TT, no combativo): winner=r0, second=r1, third=r2, leader=r0
            [
                'stage_idx' => 0,
                'winner' => $riderIds[0],
                'second' => $riderIds[1],
                'third' => $riderIds[2],
                'leader' => $riderIds[0],
                'combativo' => null,
            ],
            // Stage 2: winner=r3, second=r4, third=r5, leader=r3, combativo=r5 (3rd place)
            [
                'stage_idx' => 1,
                'winner' => $riderIds[3],
                'second' => $riderIds[4],
                'third' => $riderIds[5],
                'leader' => $riderIds[3],
                'combativo' => $riderIds[5],
            ],
            // Stage 3: winner=r6, second=r7, third=r8, leader=r6, combativo=r7 (2nd place)
            [
                'stage_idx' => 2,
                'winner' => $riderIds[6],
                'second' => $riderIds[7],
                'third' => $riderIds[8],
                'leader' => $riderIds[6],
                'combativo' => $riderIds[7],
            ],
        ];

        foreach ($stageResultsConfig as $cfg) {
            $stageId = $stageIds[$cfg['stage_idx']];

            StageResultModel::where('stage_id', $stageId)->delete();

            StageResultModel::create([
                'id' => Str::uuid()->toString(),
                'stage_id' => $stageId,
                'rider_id' => $cfg['winner'],
                'position' => 1,
                'is_gc_leader' => $cfg['leader'] === $cfg['winner'],
                'is_combativo' => $cfg['combativo'] === $cfg['winner'],
            ]);
            StageResultModel::create([
                'id' => Str::uuid()->toString(),
                'stage_id' => $stageId,
                'rider_id' => $cfg['second'],
                'position' => 2,
                'is_gc_leader' => $cfg['leader'] === $cfg['second'],
                'is_combativo' => $cfg['combativo'] === $cfg['second'],
            ]);
            StageResultModel::create([
                'id' => Str::uuid()->toString(),
                'stage_id' => $stageId,
                'rider_id' => $cfg['third'],
                'position' => 3,
                'is_gc_leader' => $cfg['leader'] === $cfg['third'],
                'is_combativo' => $cfg['combativo'] === $cfg['third'],
            ]);
        }

        // ──────────────────────────────────────────────
        // Stage predictions
        //   User 0: picks actual winner/leader → high scorer
        //   User 1: picks 2nd as winner, actual leader → medium scorer
        //   User 2: picks r[9] as winner, 2nd as leader → low scorer
        //   User 3: picks 3rd as winner, r9 as leader → low/zero scorer
        // ──────────────────────────────────────────────

        foreach ($stageIds as $sid) {
            DB::table('predictions')
                ->where('league_id', $league->id)
                ->where('stage_id', $sid)
                ->delete();
        }

        foreach ($stageResultsConfig as $cfg) {
            $stageId = $stageIds[$cfg['stage_idx']];

            $p = function (User $user, string $category, string $riderId) use ($league, $stageId, $now) {
                DB::table('predictions')->insert([
                    'id' => Str::uuid()->toString(),
                    'user_id' => $user->id,
                    'league_id' => $league->id,
                    'stage_id' => $stageId,
                    'category' => $category,
                    'type' => PredictionType::PreStage->value,
                    'prediction_value' => json_encode(['rider_id' => $riderId]),
                    'locked_at' => $now,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            };

            $p($users[0], PredictionCategory::StageWinner->value, $cfg['winner']);
            $p($users[0], PredictionCategory::StageLeader->value, $cfg['leader']);

            $p($users[1], PredictionCategory::StageWinner->value, $cfg['second']);
            $p($users[1], PredictionCategory::StageLeader->value, $cfg['leader']);

            $p($users[2], PredictionCategory::StageWinner->value, $riderIds[9]);
            $p($users[2], PredictionCategory::StageLeader->value, $cfg['second']);

            $p($users[3], PredictionCategory::StageWinner->value, $cfg['third']);
            $p($users[3], PredictionCategory::StageLeader->value, $riderIds[9]);

            // Combativo predictions only for stages that have it
            if ($cfg['combativo'] !== null) {
                // User 0 predicts actual combativo for stage 2 → hits
                //   Stage 2 combativo = r[5]; user 0 predicts r[6] → miss
                // User 0 predicts actual combativo for stage 3 → hits
                //   Stage 3 combativo = r[7]; user 0 predicts r[6] → miss
                $p($users[0], PredictionCategory::StageCombativo->value, $riderIds[6]);

                // User 1 predicts r[9] as combativo → misses both
                $p($users[1], PredictionCategory::StageCombativo->value, $riderIds[9]);
            }

            // Stage_second / Stage_third (only stage 3, diff 3, has rules)
            if ($cfg['stage_idx'] === 2) {
                $p($users[0], PredictionCategory::StageSecond->value, $cfg['second']);
                $p($users[0], PredictionCategory::StageThird->value, $cfg['third']);

                $p($users[1], PredictionCategory::StageSecond->value, $cfg['third']);
                $p($users[1], PredictionCategory::StageThird->value, $cfg['winner']);
            }
        }

        // ──────────────────────────────────────────────
        // Final classifications (for pre-race scoring)
        // ──────────────────────────────────────────────

        FinalClassificationModel::where('edition_id', $edition->id)->delete();

        $finalCategories = [
            [
                'category' => 'gc_top_5',
                'entries' => [
                    ['position' => 1, 'rider_id' => $riderIds[0]],
                    ['position' => 2, 'rider_id' => $riderIds[1]],
                    ['position' => 3, 'rider_id' => $riderIds[2]],
                    ['position' => 4, 'rider_id' => $riderIds[3]],
                    ['position' => 5, 'rider_id' => $riderIds[4]],
                ],
            ],
            [
                'category' => 'points_winner',
                'entries' => [
                    ['position' => 1, 'rider_id' => $riderIds[0]],
                    ['position' => 2, 'rider_id' => $riderIds[1]],
                    ['position' => 3, 'rider_id' => $riderIds[2]],
                ],
            ],
            [
                'category' => 'mountains_winner',
                'entries' => [
                    ['position' => 1, 'rider_id' => $riderIds[2]],
                    ['position' => 2, 'rider_id' => $riderIds[3]],
                    ['position' => 3, 'rider_id' => $riderIds[4]],
                ],
            ],
            [
                'category' => 'youth_winner',
                'entries' => [
                    ['position' => 1, 'rider_id' => $riderIds[5]],
                    ['position' => 2, 'rider_id' => $riderIds[6]],
                    ['position' => 3, 'rider_id' => $riderIds[7]],
                ],
            ],
            [
                'category' => 'teams_winner',
                'entries' => [
                    ['position' => 1, 'team_id' => $teamIds[0]],
                ],
            ],
            [
                'category' => 'super_combativo',
                'entries' => [
                    ['position' => 1, 'rider_id' => $riderIds[8]],
                ],
            ],
        ];

        foreach ($finalCategories as $fc) {
            foreach ($fc['entries'] as $entry) {
                FinalClassificationModel::create([
                    'id' => Str::uuid()->toString(),
                    'edition_id' => $edition->id,
                    'category' => $fc['category'],
                    'position' => $entry['position'],
                    'rider_id' => $entry['rider_id'] ?? null,
                    'team_id' => $entry['team_id'] ?? null,
                ]);
            }
        }

        // ──────────────────────────────────────────────
        // Generate real score events via the scoring engine
        // ──────────────────────────────────────────────

        ScoreEventModel::where('league_id', $league->id)->delete();

        $exitCode = Artisan::call('race:rebuild-scores', [
            'league_id' => $league->id,
        ]);

        $this->command?->info("race:rebuild-scores exit code: {$exitCode}");

        $this->command?->info('Test competition seeded!');
        $this->command?->info("Competition: {$competition->name}");
        $this->command?->info("Edition ID: {$edition->id}");
        $this->command?->info("League invite code: {$league->invite_code}");
        $this->command?->info('Users: '.collect($users)->pluck('email')->implode(', '));
    }
}
