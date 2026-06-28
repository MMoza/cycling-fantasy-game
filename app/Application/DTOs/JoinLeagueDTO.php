<?php

declare(strict_types=1);

namespace App\Application\DTOs;

readonly class JoinLeagueDTO
{
    public function __construct(
        public string $inviteCode,
    ) {}
}
