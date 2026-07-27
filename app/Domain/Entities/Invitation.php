<?php

declare(strict_types=1);

namespace App\Domain\Entities;

use App\Domain\ValueObjects\InvitationCode;
use Carbon\Carbon;

final class Invitation
{
    private function __construct(
        public readonly string $id,
        public readonly string $userId,
        public readonly InvitationCode $code,
        public readonly int $acceptedCount,
        public readonly Carbon $createdAt,
        public readonly Carbon $updatedAt,
    ) {}

    public static function create(
        string $id,
        string $userId,
        InvitationCode $code,
    ): self {
        return new self(
            id: $id,
            userId: $userId,
            code: $code,
            acceptedCount: 0,
            createdAt: Carbon::now(),
            updatedAt: Carbon::now(),
        );
    }

    public function incrementAcceptedCount(): self
    {
        return new self(
            id: $this->id,
            userId: $this->userId,
            code: $this->code,
            acceptedCount: $this->acceptedCount + 1,
            createdAt: $this->createdAt,
            updatedAt: Carbon::now(),
        );
    }
}
