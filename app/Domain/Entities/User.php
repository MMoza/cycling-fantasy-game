<?php

declare(strict_types=1);

namespace App\Domain\Entities;

use App\Domain\ValueObjects\UserPlan;

/**
 * Usuario del sistema. La persistencia y la autenticación (Laravel Auth) no
 * forman parte del dominio: se accede a través de UserRepositoryInterface.
 */
readonly class User
{
    public function __construct(
        public string $id,
        public string $name,
        public string $email,
        public bool $isAdmin,
        public UserPlan $plan,
        public ?string $avatar,
        public ?string $lastVisitedLeagueId,
    ) {}
}
