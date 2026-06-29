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
        $this->createCompetition(
            name: 'Tour de Francia',
            countryId: 'FR',
            editions: [
                ['year' => 2026, 'start' => '2026-07-04', 'end' => '2026-07-26'],
            ],
        );

        $this->createCompetition(
            name: 'Giro de Italia',
            countryId: 'IT',
            editions: [
                ['year' => 2026, 'start' => '2026-05-09', 'end' => '2026-05-31'],
            ],
        );

        $this->createCompetition(
            name: 'La Vuelta',
            countryId: 'ES',
            editions: [
                ['year' => 2026, 'start' => '2026-08-15', 'end' => '2026-09-06'],
            ],
        );
    }

    private function createCompetition(string $name, string $countryId, array $editions): void
    {
        $competition = CompetitionModel::firstOrCreate([
            'name' => $name,
        ], [
            'id' => Str::uuid()->toString(),
            'type' => CompetitionType::GrandTour,
            'country_id' => $countryId,
            'active' => true,
        ]);

        foreach ($editions as $edition) {
            EditionModel::firstOrCreate([
                'competition_id' => $competition->id,
                'year' => $edition['year'],
            ], [
                'id' => Str::uuid()->toString(),
                'start_date' => $edition['start'],
                'end_date' => $edition['end'],
                'status' => EditionStatus::Upcoming,
            ]);
        }
    }
}
