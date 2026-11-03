<?php

declare(strict_types=1);

namespace App\Domain\Entities;

use App\Domain\Support\Random;
use App\Domain\ValueObjects\ActivityLogType;

readonly class ActivityLog
{
    public function __construct(
        public string $id,
        public string $leagueId,
        public ActivityLogType $type,
        public string $title,
        public ?string $description,
        public ?array $data,
        public string $createdAt,
    ) {}

    public static function create(
        string $leagueId,
        ActivityLogType $type,
        string $title,
        ?string $description = null,
        ?array $data = null,
    ): self {
        return new self(
            id: Random::uuid(),
            leagueId: $leagueId,
            type: $type,
            title: $title,
            description: $description,
            data: $data,
            createdAt: now()->toISOString(),
        );
    }
}
