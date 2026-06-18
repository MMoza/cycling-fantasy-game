<?php

declare(strict_types=1);

namespace App\Domain\Entities;

readonly class League
{
    public function __construct(
        public string $id,
        public string $name,
        public string $editionId,
        public string $scoringSystemId,
        public string $ownerId,
        public string $inviteCode,
    ) {
    }

    public static function create(
        string $name,
        string $editionId,
        string $scoringSystemId,
        string $ownerId,
    ): self {
        return new self(
            id: \Illuminate\Support\Str::uuid()->toString(),
            name: $name,
            editionId: $editionId,
            scoringSystemId: $scoringSystemId,
            ownerId: $ownerId,
            inviteCode: \Illuminate\Support\Str::random(8),
        );
    }

    public function regenerateInviteCode(): self
    {
        return new self(
            id: $this->id,
            name: $this->name,
            editionId: $this->editionId,
            scoringSystemId: $this->scoringSystemId,
            ownerId: $this->ownerId,
            inviteCode: \Illuminate\Support\Str::random(8),
        );
    }
}
