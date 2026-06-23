<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domain\ValueObjects\CompetitionType;
use App\Domain\ValueObjects\EditionStatus;
use App\Infrastructure\Persistence\Models\CompetitionModel;
use App\Infrastructure\Persistence\Models\EditionModel;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CompetitionSeeder extends Seeder
{
    public function run(): void
    {
        $tour = CompetitionModel::firstOrCreate([
            'name' => 'Tour de Francia',
        ], [
            'id' => Str::uuid()->toString(),
            'type' => CompetitionType::GrandTour,
            'country' => 'Francia',
            'active' => true,
        ]);

        EditionModel::firstOrCreate([
            'competition_id' => $tour->id,
            'year' => 2026,
        ], [
            'id' => Str::uuid()->toString(),
            'start_date' => '2026-07-01',
            'end_date' => '2026-07-23',
            'status' => EditionStatus::Upcoming,
        ]);
    }
}
