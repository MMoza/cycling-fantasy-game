<?php

declare(strict_types=1);

namespace App\Domain\Interfaces;

use App\Domain\Entities\User;

interface UserRepositoryInterface
{
    public function find(string $id): ?User;

    /**
     * @param  list<string>  $ids
     * @return list<User>
     */
    public function findMany(array $ids): array;

    public function findByEmail(string $email): ?User;

    /**
     * @return list<User>
     */
    public function all(): array;

    public function save(User $user): void;
}
