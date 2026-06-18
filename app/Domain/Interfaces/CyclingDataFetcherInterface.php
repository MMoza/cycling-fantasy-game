<?php

declare(strict_types=1);

namespace App\Domain\Interfaces;

use App\Domain\ValueObjects\StageType;

interface CyclingDataFetcherInterface
{
    public function fetchStages(string $editionId): array;

    public function fetchStageResults(string $stageId): array;

    public function fetchClassifications(string $editionId): array;
}
