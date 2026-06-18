<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domain\ValueObjects\StageStatus;
use App\Domain\ValueObjects\StageType;
use App\Infrastructure\Persistence\Models\EditionModel;
use App\Infrastructure\Persistence\Models\StageModel;
use Illuminate\Database\Eloquent\Factories\Factory;

class StageModelFactory extends Factory
{
    protected $model = StageModel::class;

    public function definition(): array
    {
        $number = $this->faker->numberBetween(1, 21);

        return [
            'id' => $this->faker->uuid(),
            'edition_id' => EditionModel::factory(),
            'number' => $number,
            'name' => "Etapa {$number}",
            'date' => $this->faker->dateTimeBetween('2026-07-01', '2026-07-23'),
            'type' => $this->faker->randomElement(StageType::cases()),
            'distance' => $this->faker->randomFloat(1, 100, 250),
            'origin' => $this->faker->city(),
            'destination' => $this->faker->city(),
            'status' => StageStatus::Upcoming,
        ];
    }
}
