<?php

declare(strict_types=1);

namespace App\Application\DTOs\View;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Leaderboard row: rank/points plus ranking movement and streak info.
 */
readonly class LeaderboardEntryViewDTO implements Arrayable
{
    public function __construct(
        public string $userId,
        public string $userName,
        public ?string $avatar,
        public int $points,
        public bool $isCurrentUser,
        public bool $isOnline,
        public int $rank,
        public ?int $previousRank,
        public ?int $rankChange,
        public int $behindLeader,
        public int $winnerStreak,
    ) {}

    public function toArray(): array
    {
        return [
            'user_id' => $this->userId,
            'user_name' => $this->userName,
            'avatar' => $this->avatar,
            'points' => $this->points,
            'is_current_user' => $this->isCurrentUser,
            'is_online' => $this->isOnline,
            'rank' => $this->rank,
            'previous_rank' => $this->previousRank,
            'rank_change' => $this->rankChange,
            'behind_leader' => $this->behindLeader,
            'winner_streak' => $this->winnerStreak,
        ];
    }
}
