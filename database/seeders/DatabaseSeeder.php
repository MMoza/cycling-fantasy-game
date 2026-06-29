<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            CountrySeeder::class,
            AdminUserSeeder::class,
            CompetitionSeeder::class,
            ScoringSystemSeeder::class,
            TourDeFrance2026Seeder::class,
        ]);

        if (! User::where('email', 'dev@cyclingfantasy.com')->exists()) {
            User::factory()->create([
                'name' => 'Desarrollo',
                'email' => 'dev@cyclingfantasy.com',
                'password' => bcrypt('password'),
            ]);
        }

        if (app()->environment('local', 'testing')) {
            $this->call(TestCompetitionSeeder::class);
        }
    }
}
