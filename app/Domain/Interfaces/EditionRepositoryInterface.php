<?php

declare(strict_types=1);

namespace App\Domain\Interfaces;

use App\Domain\Entities\Edition;
use Illuminate\Support\Collection;

interface EditionRepositoryInterface
{
    public function find(string $id): ?Edition;

    public function findByCompetition(string $competitionId): Collection;

    public function findByYear(int $year): Collection;

    public function save(Edition $edition): void;

    public function delete(string $id): void;
}
