<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Infrastructure\Persistence\Models\ScoringSystemModel;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SeasonClassificationSeeder extends Seeder
{
    private array $userIds = [];

    public function run(): void
    {
        $this->userIds = User::pluck('id')->toArray();

        if (count($this->userIds) < 2) {
            $this->command?->error('Need at least 2 users. Run AdminUserSeeder first.');

            return;
        }

        $standardSystem = ScoringSystemModel::where('name', 'Estandar Tour')->first();
        if (! $standardSystem) {
            $this->command?->error('Standard scoring system not found.');

            return;
        }

        $this->createNewCompetitions();
        $this->createEditionsForExistingCompetitions();
        $this->createOfficialLeagues($standardSystem->id);
        $this->createScoreEvents();
    }

    private function createNewCompetitions(): void
    {
        $newComps = [
            ['name' => 'Milán-San Remo', 'type' => 'monument', 'country_id' => 'IT'],
            ['name' => 'Tour de Flandes', 'type' => 'monument', 'country_id' => 'BE'],
            ['name' => 'París-Roubaix', 'type' => 'monument', 'country_id' => 'FR'],
            ['name' => 'Giro de Lombardía', 'type' => 'monument', 'country_id' => 'IT'],
            ['name' => 'Liège-Bastogne-Liège', 'type' => 'monument', 'country_id' => 'BE'],
            ['name' => 'Strade Bianche', 'type' => 'classic', 'country_id' => 'IT'],
            ['name' => 'Gent-Wevelgem', 'type' => 'classic', 'country_id' => 'BE'],
            ['name' => 'E3 Saxo Classic', 'type' => 'classic', 'country_id' => 'BE'],
            ['name' => 'Amstel Gold Race', 'type' => 'classic', 'country_id' => 'NL'],
            ['name' => 'La Flèche Wallonne', 'type' => 'classic', 'country_id' => 'BE'],
            ['name' => 'GP de Québec', 'type' => 'major', 'country_id' => 'CA'],
            ['name' => 'GP de Montréal', 'type' => 'major', 'country_id' => 'CA'],
            ['name' => 'San Sebastián Klasikoa', 'type' => 'major', 'country_id' => 'ES'],
        ];

        foreach ($newComps as $comp) {
            $exists = DB::table('competitions')->where('name', $comp['name'])->exists();
            if (! $exists) {
                DB::table('competitions')->insert([
                    'id' => Str::uuid()->toString(),
                    'name' => $comp['name'],
                    'type' => $comp['type'],
                    'country_id' => $comp['country_id'],
                    'active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    private function createEditionsForExistingCompetitions(): void
    {
        $editionConfigs = [
            // GC
            'Tour de Francia' => ['status' => 'finished', 'start' => '2026-07-04', 'end' => '2026-07-26'],
            'Giro de Italia' => ['status' => 'finished', 'start' => '2026-05-09', 'end' => '2026-05-31'],
            'La Vuelta' => ['status' => 'finished', 'start' => '2026-08-15', 'end' => '2026-09-06'],
            // Championship
            'World Championship' => ['status' => 'upcoming', 'start' => '2026-09-20', 'end' => '2026-09-27'],
            // Monuments
            'Milán-San Remo' => ['status' => 'finished', 'start' => '2026-03-21', 'end' => '2026-03-21'],
            'Tour de Flandes' => ['status' => 'finished', 'start' => '2026-04-05', 'end' => '2026-04-05'],
            'París-Roubaix' => ['status' => 'finished', 'start' => '2026-04-12', 'end' => '2026-04-12'],
            'Giro de Lombardía' => ['status' => 'upcoming', 'start' => '2026-10-10', 'end' => '2026-10-10'],
            'Liège-Bastogne-Liège' => ['status' => 'finished', 'start' => '2026-04-26', 'end' => '2026-04-26'],
            // Classics
            'Strade Bianche' => ['status' => 'finished', 'start' => '2026-03-07', 'end' => '2026-03-07'],
            'Gent-Wevelgem' => ['status' => 'finished', 'start' => '2026-03-29', 'end' => '2026-03-29'],
            'E3 Saxo Classic' => ['status' => 'finished', 'start' => '2026-03-27', 'end' => '2026-03-27'],
            'Amstel Gold Race' => ['status' => 'finished', 'start' => '2026-04-19', 'end' => '2026-04-19'],
            'La Flèche Wallonne' => ['status' => 'finished', 'start' => '2026-04-22', 'end' => '2026-04-22'],
            // Majors
            'GP de Québec' => ['status' => 'upcoming', 'start' => '2026-09-11', 'end' => '2026-09-11'],
            'GP de Montréal' => ['status' => 'upcoming', 'start' => '2026-09-13', 'end' => '2026-09-13'],
            'San Sebastián Klasikoa' => ['status' => 'finished', 'start' => '2026-07-25', 'end' => '2026-07-25'],
        ];

        foreach ($editionConfigs as $compName => $config) {
            $comp = DB::table('competitions')->where('name', $compName)->first();
            if (! $comp) {
                continue;
            }

            $exists = DB::table('editions')
                ->where('competition_id', $comp->id)
                ->where('year', 2026)
                ->exists();

            if (! $exists) {
                DB::table('editions')->insert([
                    'id' => Str::uuid()->toString(),
                    'competition_id' => $comp->id,
                    'year' => 2026,
                    'start_date' => $config['start'],
                    'end_date' => $config['end'],
                    'status' => $config['status'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    private function createOfficialLeagues(string $scoringSystemId): void
    {
        $editions = DB::table('editions')
            ->where('year', 2026)
            ->get();

        foreach ($editions as $edition) {
            $hasOfficialLeague = DB::table('leagues')
                ->where('edition_id', $edition->id)
                ->where('is_official', true)
                ->exists();

            if ($hasOfficialLeague) {
                continue;
            }

            $comp = DB::table('competitions')->where('id', $edition->competition_id)->first();
            if (! $comp) {
                continue;
            }

            $leagueId = Str::uuid()->toString();

            DB::table('leagues')->insert([
                'id' => $leagueId,
                'name' => "Liga Oficial {$comp->name} {$edition->year}",
                'edition_id' => $edition->id,
                'scoring_system_id' => $scoringSystemId,
                'owner_id' => $this->userIds[0],
                'is_official' => true,
                'is_public' => true,
                'invite_code' => strtoupper(Str::random(8)),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            foreach ($this->userIds as $index => $userId) {
                $role = $index === 0 ? 'owner' : 'member';
                $exists = DB::table('league_user')
                    ->where('user_id', $userId)
                    ->where('league_id', $leagueId)
                    ->exists();

                if (! $exists) {
                    DB::table('league_user')->insert([
                        'id' => Str::uuid()->toString(),
                        'user_id' => $userId,
                        'league_id' => $leagueId,
                        'role' => $role,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }

    private function createScoreEvents(): void
    {
        $officialLeagues = DB::table('leagues')
            ->where('is_official', true)
            ->get();

        $rules = DB::table('scoring_rules')
            ->where('context', 'pre_race')
            ->get();

        if ($rules->isEmpty()) {
            $this->command?->warning('No pre_race scoring rules found.');

            return;
        }

        DB::table('score_events')->whereNull('stage_id')->delete();

        foreach ($officialLeagues as $league) {
            $leagueRules = $rules->where('scoring_system_id', $league->scoring_system_id);

            foreach ($this->userIds as $userIndex => $userId) {
                $baseMultiplier = match ($userIndex) {
                    0 => 1.2,
                    1 => 1.0,
                    2 => 0.8,
                    3 => 0.6,
                    4 => 0.4,
                    5 => 0.3,
                    6 => 0.2,
                    default => 0.5,
                };

                foreach ($leagueRules as $rule) {
                    $points = max(1, (int) round($rule->points * $baseMultiplier * (0.7 + mt_rand(0, 60) / 100)));

                    DB::table('score_events')->insert([
                        'id' => Str::uuid()->toString(),
                        'user_id' => $userId,
                        'league_id' => $league->id,
                        'scoring_rule_id' => $rule->id,
                        'points' => $points,
                        'description' => $rule->context,
                        'context' => $rule->context,
                        'stage_id' => null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }

        $total = DB::table('score_events')->whereNull('stage_id')->count();
        $this->command?->info("Created {$total} score events across ".$officialLeagues->count().' leagues.');
    }
}
