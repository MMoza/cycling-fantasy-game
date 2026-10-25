<?php

declare(strict_types=1);

namespace App\Application\DTOs\View;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Current user standing: {rank, points, behind_leader} as display strings.
 */
readonly class UserPositionViewDTO implements Arrayable
{
    public function __construct(
        public string $rank,
        public string $points,
        public string $behindLeader,
    ) {}

    public static function fromStanding(int $rank, int $points, int $behindLeader): self
    {
        return new self(
            rank: (string) $rank,
            points: (string) $points,
            behindLeader: "{$behindLeader} pts",
        );
    }

    public static function empty(): self
    {
        return new self(rank: '-', points: '0', behindLeader: '-');
    }

    public function toArray(): array
    {
        return [
            'rank' => $this->rank,
            'points' => $this->points,
            'behind_leader' => $this->behindLeader,
        ];
    }
}
