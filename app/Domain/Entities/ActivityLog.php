<?php

declare(strict_types=1);

namespace App\Domain\Entities;

use App\Domain\ValueObjects\ActivityLogType;
use App\Infrastructure\Persistence\Models\ActivityLogModel;
use Illuminate\Support\Str;

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
            id: Str::uuid()->toString(),
            leagueId: $leagueId,
            type: $type,
            title: $title,
            description: $description,
            data: $data,
            createdAt: now()->toISOString(),
        );
    }

    public static function fromModel(ActivityLogModel $model): self
    {
        return new self(
            id: $model->id,
            leagueId: $model->league_id,
            type: $model->type,
            title: $model->title,
            description: $model->description,
            data: $model->data,
            createdAt: $model->created_at->toISOString(),
        );
    }
}
