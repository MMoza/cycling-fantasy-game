<?php

declare(strict_types=1);

namespace App\Application\DTOs\View;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Vista de detalle de un badge de un usuario: {badge, user, level,
 * level_index, progress, target, awarded_at, levels_detail, history}.
 */
readonly class BadgeDetailViewDTO implements Arrayable
{
    /**
     * @param  array<string, mixed>  $badge
     * @param  array<string, mixed>  $user
     * @param  array<int, array<string, mixed>>  $levelsDetail
     * @param  array<int, array<string, mixed>>  $history
     */
    public function __construct(
        public array $badge,
        public array $user,
        public int $level,
        public int $levelIndex,
        public int $progress,
        public int $target,
        public ?string $awardedAt,
        public array $levelsDetail,
        public array $history,
    ) {}

    public function toArray(): array
    {
        return [
            'badge' => $this->badge,
            'user' => $this->user,
            'level' => $this->level,
            'level_index' => $this->levelIndex,
            'progress' => $this->progress,
            'target' => $this->target,
            'awarded_at' => $this->awardedAt,
            'levels_detail' => $this->levelsDetail,
            'history' => $this->history,
        ];
    }
}
