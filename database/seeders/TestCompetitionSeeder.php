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
use App\Infrastructure\Persistence\Models\LeagueModel;
use App\Infrastructure\Persistence\Models\RiderModel;
use App\Infrastructure\Persistence\Models\ScoringSystemModel;
use App\Models\User;
use Illuminate\Database\Seeder;
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
                'status' => EditionStatus::Upcoming,
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
                    'status' => StageStatus::Upcoming->value,
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

        $now = now();

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

        foreach ($users as $i => $user) {
            $winnerId = $riderIds[$i % count($riderIds)];
            $usedForWinner = [$winnerId];

            $stageCategories = [
                PredictionCategory::StageWinner->value => ['rider_id' => $winnerId],
                PredictionCategory::StageSecond->value => ['rider_id' => $riderIds[($i + 1) % count($riderIds)]],
                PredictionCategory::StageThird->value => ['rider_id' => $riderIds[($i + 2) % count($riderIds)]],
                PredictionCategory::StageLeader->value => ['rider_id' => $riderIds[$i % count($riderIds)]],
                PredictionCategory::StageCombativo->value => ['rider_id' => $riderIds[($i + 3) % count($riderIds)]],
            ];

            foreach ($stageCategories as $category => $value) {
                DB::table('predictions')->updateOrInsert(
                    ['user_id' => $user->id, 'league_id' => $league->id, 'category' => $category, 'stage_id' => $stageIds[0]],
                    [
                        'id' => Str::uuid()->toString(),
                        'type' => PredictionType::PreStage->value,
                        'prediction_value' => json_encode($value),
                        'locked_at' => null,
                        'updated_at' => $now,
                        'created_at' => $now,
                    ],
                );
            }
        }

        $this->command?->info('Test competition seeded!');
        $this->command?->info("Competition: {$competition->name}");
        $this->command?->info("Edition ID: {$edition->id}");
        $this->command?->info("League invite code: {$league->invite_code}");
        $this->command?->info('Users: '.collect($users)->pluck('email')->implode(', '));
        $this->command?->info('Stages: '.implode(', ', $stageIds));
    }
}
