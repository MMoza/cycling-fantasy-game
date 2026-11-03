<?php

declare(strict_types=1);

namespace App\Domain\Interfaces;

use App\Domain\Entities\User;

interface UserRepositoryInterface
{
    public function find(string $id): ?User;

    public function findByEmail(string $email): ?User;

    /**
     * @return list<User>
     */
    public function all(): array;

    public function save(User $user): void;
}
