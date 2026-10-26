<?php

declare(strict_types=1);

namespace App\Application\DTOs\View;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Leaderboard row of the classification page: rank/points plus, for stage
 * leaderboards, the rank movement fields (null for general rows).
 */
readonly class ClassificationLeaderboardEntryViewDTO implements Arrayable
{
    public function __construct(
        public string $userId,
        public string $userName,
        public int $points,
        public bool $isCurrentUser,
        public int $rank,
        public int $behindLeader,
        public ?int $previousRank = null,
        public ?int $rankChange = null,
        public array $badges = [],
    ) {}

    public function toArray(): array
    {
        return [
            'user_id' => $this->userId,
            'user_name' => $this->userName,
            'points' => $this->points,
            'is_current_user' => $this->isCurrentUser,
            'rank' => $this->rank,
            'behind_leader' => $this->behindLeader,
            'previous_rank' => $this->previousRank,
            'rank_change' => $this->rankChange,
            'badges' => $this->badges,
        ];
    }
}
