<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domain\ValueObjects\EditionStatus;
use App\Infrastructure\Persistence\Models\CompetitionModel;
use App\Infrastructure\Persistence\Models\EditionModel;
use Illuminate\Database\Eloquent\Factories\Factory;

class EditionModelFactory extends Factory
{
    protected $model = EditionModel::class;

    public function definition(): array
    {
        $year = $this->faker->year();

        return [
            'id' => $this->faker->uuid(),
            'competition_id' => CompetitionModel::factory(),
            'year' => $year,
            'start_date' => "{$year}-07-01",
            'end_date' => "{$year}-07-23",
            'status' => EditionStatus::Upcoming,
        ];
    }
}
