<?php

declare(strict_types=1);

namespace App\Domain\Interfaces;

use App\Domain\Entities\Competition;

interface CompetitionRepositoryInterface
{
    public function find(string $id): ?Competition;

    /**
     * @return list<Competition>
     */
    public function findAll(): array;

    /**
     * @return list<Competition>
     */
    public function findByType(string $type): array;

    public function save(Competition $competition): void;

    public function delete(string $id): void;
}
