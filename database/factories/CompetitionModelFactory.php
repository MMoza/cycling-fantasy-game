<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domain\ValueObjects\CompetitionType;
use App\Infrastructure\Persistence\Models\CompetitionModel;
use Illuminate\Database\Eloquent\Factories\Factory;

class CompetitionModelFactory extends Factory
{
    protected $model = CompetitionModel::class;

    public function definition(): array
    {
        return [
            'id' => $this->faker->uuid(),
            'name' => $this->faker->randomElement([
                'Tour de Francia',
                'Giro de Italia',
                'Vuelta a España',
                'París-Niza',
                'Milán-San Remo',
            ]),
            'type' => $this->faker->randomElement(CompetitionType::cases()),
            'country' => $this->faker->country(),
            'active' => true,
        ];
    }
}
