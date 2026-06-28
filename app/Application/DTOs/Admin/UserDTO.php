<?php

declare(strict_types=1);

namespace App\Application\DTOs\Admin;

readonly class UserDTO
{
    public function __construct(
        public string $id,
        public string $name,
        public string $email,
        public bool $isAdmin,
        public int $leaguesCount,
        public string $createdAt,
    ) {}
}
