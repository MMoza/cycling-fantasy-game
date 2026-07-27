<?php

declare(strict_types=1);

namespace App\Presentation\Console;

use App\Infrastructure\Persistence\Models\LeagueModel;
use App\Infrastructure\Persistence\Models\ScoreEventModel;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MockLeaderboardCommand extends Command
{
    protected $signature = 'mock:leaderboard {league_id?} {--count=25}';

    protected $description = 'Populate a league with mock users and score events for testing the leaderboard';

    public function handle(): int
    {
        $leagueId = $this->argument('league_id');
        $count = (int) $this->option('count');

        $query = LeagueModel::query();
        if ($leagueId) {
            $query->where('id', $leagueId);
        }
        $league = $query->firstOrFail();

        $admin = User::where('is_admin', true)->first();
        if (! $admin) {
            $this->error('No admin user found');

            return self::FAILURE;
        }

        // Ensure admin is a member
        if (! $league->users()->where('user_id', $admin->id)->exists()) {
            $league->users()->attach($admin->id, [
                'id' => Str::uuid()->toString(),
                'role' => 'member',
            ]);
        }

        // Fetch scoring rules for this league's scoring system
        $rules = DB::table('scoring_rules')
            ->where('scoring_system_id', $league->scoring_system_id)
            ->get()
            ->keyBy(fn ($r) => $r->type);

        // Create mock users
        $firstNames = ['Carlos', 'Ana', 'Luis', 'María', 'Pedro', 'Laura', 'Javier', 'Sofía', 'Miguel', 'Elena', 'Antonio', 'Carmen', 'Francisco', 'Isabel', 'Manuel', 'Teresa', 'Alejandro', 'Lucía', 'Rafael', 'Marta', 'Diego', 'Paula', 'Alberto', 'Nerea', 'Sergio', 'Andrea', 'Pablo', 'Cristina', 'Daniel', 'Raquel'];
        $lastNames = ['Pérez', 'García', 'Martínez', 'López', 'Rodríguez', 'Fernández', 'González', 'Sánchez', 'Ramírez', 'Torres', 'Flores', 'Rivera', 'Gómez', 'Díaz', 'Cruz', 'Morales', 'Reyes', 'Ortiz', 'Gutiérrez', 'Chávez'];

        $users = [$admin];
        $existingEmails = User::pluck('email')->toArray();

        for ($i = 0; $i < $count; $i++) {
            $first = $firstNames[array_rand($firstNames)];
            $last = $lastNames[array_rand($lastNames)];
            $email = strtolower($first.'.'.$last.'.'.Str::random(4).'@mock.test');

            if (in_array($email, $existingEmails, true)) {
                continue;
            }

            $user = User::create([
                'name' => "{$first} {$last}",
                'email' => $email,
                'password' => bcrypt('password'),
            ]);

            $existingEmails[] = $email;
            $users[] = $user;

            if (! $league->users()->where('user_id', $user->id)->exists()) {
                $league->users()->attach($user->id, [
                    'id' => Str::uuid()->toString(),
                    'role' => 'member',
                ]);
            }
        }

        $this->info('Created '.($count).' mock users, total members: '.count($users));

        // Clean existing score events for this league
        ScoreEventModel::where('league_id', $league->id)->delete();

        // Pre-race rules mapping context -> rule info
        $preRaceRules = [
            'gc_top_5' => ['type' => 'gc_top_5', 'description' => 'Top 5 general'],
            'points_winner' => ['type' => 'points_winner', 'description' => 'Maillot verde'],
            'mountains_winner' => ['type' => 'mountains_winner', 'description' => 'Maillot montaña'],
            'youth_winner' => ['type' => 'youth_winner', 'description' => 'Maillot blanco'],
            'teams_winner' => ['type' => 'teams_winner', 'description' => 'Mejor equipo'],
            'super_combativo' => ['type' => 'super_combativo', 'description' => 'Supercombativo'],
        ];

        $stages = $league->edition->stages()->get();
        $scoredStages = $stages->where('status', 'finished');

        foreach ($users as $idx => $user) {
            // Admin gets low points, others get power-law distribution
            if ($user->id === $admin->id) {
                $basePoints = rand(5, 30);
            } else {
                $rank = $idx;
                $basePoints = (int) max(10, 800 - ($rank * 25) + rand(-30, 30));
            }

            // Pre-race score events
            foreach ($preRaceRules as $ctx => $info) {
                $rule = $rules->get($info['type']);
                if (! $rule) {
                    continue;
                }
                $points = max(0, (int) ($basePoints * (rand(5, 25) / 100)));
                if ($points > 0) {
                    ScoreEventModel::create([
                        'id' => Str::uuid()->toString(),
                        'league_id' => $league->id,
                        'user_id' => $user->id,
                        'scoring_rule_id' => $rule->id,
                        'points' => $points,
                        'description' => $info['description'],
                        'context' => $ctx,
                        'stage_id' => null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            // Stage score events
            foreach ($scoredStages as $stage) {
                $stageRules = [
                    ['type' => 'stage_winner', 'ctx' => 'stage_winner', 'desc' => 'Ganador etapa'],
                    ['type' => 'stage_second', 'ctx' => 'stage_second', 'desc' => '2do etapa'],
                    ['type' => 'stage_third', 'ctx' => 'stage_third', 'desc' => '3ro etapa'],
                    ['type' => 'stage_leader', 'ctx' => 'stage_leader', 'desc' => 'Líder etapa'],
                    ['type' => 'stage_combativo', 'ctx' => 'stage_combativo', 'desc' => 'Combativo etapa'],
                ];

                foreach ($stageRules as $sr) {
                    $rule = $rules->get($sr['type']);
                    if (! $rule) {
                        continue;
                    }
                    $points = max(0, (int) ($basePoints * (rand(2, 15) / 100)));
                    if ($points > 0) {
                        ScoreEventModel::create([
                            'id' => Str::uuid()->toString(),
                            'league_id' => $league->id,
                            'user_id' => $user->id,
                            'scoring_rule_id' => $rule->id,
                            'points' => $points,
                            'description' => $sr['desc'],
                            'context' => $sr['ctx'],
                            'stage_id' => $stage->id,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }
        }

        $totalEvents = ScoreEventModel::where('league_id', $league->id)->count();
        $this->info("Created {$totalEvents} score events across ".count($users).' users');
        $this->info("League: {$league->name}");

        return self::SUCCESS;
    }
}
