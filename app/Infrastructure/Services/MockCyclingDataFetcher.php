<?php

declare(strict_types=1);

namespace App\Infrastructure\Services;

use App\Domain\Interfaces\CyclingDataFetcherInterface;
use App\Domain\ValueObjects\StageType;

class MockCyclingDataFetcher implements CyclingDataFetcherInterface
{
    public function fetchStages(string $editionId): array
    {
        return [
            [
                'number' => 1,
                'name' => 'Etapa 1 - Lille',
                'date' => '2026-07-01',
                'type' => StageType::Flat,
                'distance' => 180.5,
                'origin' => 'Lille',
                'destination' => 'Paris',
            ],
            [
                'number' => 2,
                'name' => 'Etapa 2 - Montaña',
                'date' => '2026-07-02',
                'type' => StageType::Mountain,
                'distance' => 165.0,
                'origin' => 'Lyon',
                'destination' => 'Grenoble',
            ],
            [
                'number' => 3,
                'name' => 'Etapa 3 - Contrarreloj',
                'date' => '2026-07-03',
                'type' => StageType::TimeTrial,
                'distance' => 35.0,
                'origin' => 'Bordeaux',
                'destination' => 'Bordeaux',
            ],
            [
                'number' => 4,
                'name' => 'Etapa 4 - Alta Montaña',
                'date' => '2026-07-04',
                'type' => StageType::HighMountain,
                'distance' => 190.0,
                'origin' => 'Toulouse',
                'destination' => 'Luchon',
            ],
            [
                'number' => 5,
                'name' => 'Etapa 5 - Descanso',
                'date' => '2026-07-05',
                'type' => StageType::Rest,
                'distance' => null,
                'origin' => 'Marseille',
                'destination' => 'Marseille',
            ],
        ];
    }

    public function fetchStageResults(string $stageId): array
    {
        return [
            ['rider_id' => 'rider-1', 'position' => 1, 'time' => '4:30:15', 'gap' => null],
            ['rider_id' => 'rider-2', 'position' => 2, 'time' => '4:30:18', 'gap' => '+0:03'],
            ['rider_id' => 'rider-3', 'position' => 3, 'time' => '4:30:22', 'gap' => '+0:07'],
            ['rider_id' => 'rider-4', 'position' => 4, 'time' => '4:30:25', 'gap' => '+0:10'],
            ['rider_id' => 'rider-5', 'position' => 5, 'time' => '4:30:30', 'gap' => '+0:15'],
        ];
    }

    public function fetchClassifications(string $editionId): array
    {
        return [
            'gc' => [
                ['rider_id' => 'rider-1', 'position' => 1, 'time' => '45:30:15'],
                ['rider_id' => 'rider-2', 'position' => 2, 'time' => '45:30:25'],
                ['rider_id' => 'rider-3', 'position' => 3, 'time' => '45:30:40'],
            ],
            'points' => [
                ['rider_id' => 'rider-1', 'position' => 1, 'points' => 350],
                ['rider_id' => 'rider-2', 'position' => 2, 'points' => 280],
            ],
            'mountains' => [
                ['rider_id' => 'rider-3', 'position' => 1, 'points' => 120],
                ['rider_id' => 'rider-4', 'position' => 2, 'points' => 90],
            ],
        ];
    }
}
