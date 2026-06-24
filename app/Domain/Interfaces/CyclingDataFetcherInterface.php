<?php

declare(strict_types=1);

namespace App\Domain\Interfaces;

interface CyclingDataFetcherInterface
{
    public function fetchStages(string $editionId): array;

    public function fetchStageResults(string $stageId): array;

    public function fetchClassifications(string $editionId): array;
}
