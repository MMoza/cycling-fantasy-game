<?php

declare(strict_types=1);

namespace App\Domain\Interfaces;

use App\Domain\Entities\Edition;

interface EditionRepositoryInterface
{
    public function find(string $id): ?Edition;

    /**
     * @return list<Edition>
     */
    public function findByCompetition(string $competitionId): array;

    /**
     * @return list<Edition>
     */
    public function findByYear(int $year): array;

    public function save(Edition $edition): void;

    public function delete(string $id): void;
}
