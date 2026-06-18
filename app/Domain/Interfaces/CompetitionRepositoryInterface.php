<?php

declare(strict_types=1);

namespace App\Domain\Interfaces;

use App\Domain\Entities\Competition;
use Illuminate\Support\Collection;

interface CompetitionRepositoryInterface
{
    public function find(string $id): ?Competition;

    public function findAll(): Collection;

    public function findByType(string $type): Collection;

    public function save(Competition $competition): void;

    public function delete(string $id): void;
}
