<?php

declare(strict_types=1);

namespace App\Application\DTOs;

readonly class JoinAllSeasonResultDTO
{
    public function __construct(
        public int $joined,
        public int $alreadyMember,
        public int $notJoinable,
        public ?string $error,
    ) {}

    public function total(): int
    {
        return $this->joined + $this->alreadyMember + $this->notJoinable;
    }
}
