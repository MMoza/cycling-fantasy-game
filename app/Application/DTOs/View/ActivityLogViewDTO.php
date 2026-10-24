<?php

declare(strict_types=1);

namespace App\Application\DTOs\View;

use App\Domain\Interfaces\MediaUrlResolverInterface;
use App\Infrastructure\Persistence\Models\ActivityLogModel;
use Illuminate\Contracts\Support\Arrayable;

/**
 * Activity feed entry: {id, type, title, description, data, created_at}.
 */
readonly class ActivityLogViewDTO implements Arrayable
{
    public function __construct(
        public string $id,
        public string $type,
        public string $title,
        public ?string $description,
        public ?array $data,
        public string $createdAt,
    ) {}

    public static function fromModel(ActivityLogModel $log, MediaUrlResolverInterface $mediaUrls): self
    {
        $data = $log->data;

        if ($log->type->value === 'league_winner' && isset($data['winner_avatar'])) {
            $data['winner_avatar'] = $mediaUrls->resolve($data['winner_avatar']);
        }

        return new self(
            id: $log->id,
            type: $log->type->value,
            title: $log->title,
            description: $log->description,
            data: $data,
            createdAt: $log->created_at->diffForHumans(),
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'title' => $this->title,
            'description' => $this->description,
            'data' => $this->data,
            'created_at' => $this->createdAt,
        ];
    }
}
