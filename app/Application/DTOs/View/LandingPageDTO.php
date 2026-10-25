<?php

declare(strict_types=1);

namespace App\Application\DTOs\View;

use Illuminate\Contracts\Support\Arrayable;

/**
 * All props of the public Landing page:
 * {auth: {user}, seasonRaces, activeRaceId, currentStage}.
 */
readonly class LandingPageDTO implements Arrayable
{
    /**
     * @param  array{id: string, name: string}|null  $user
     * @param  array<int, LandingRaceViewDTO>  $seasonRaces
     */
    public function __construct(
        public ?array $user,
        public array $seasonRaces,
        public ?string $activeRaceId,
        public ?LandingStageViewDTO $currentStage,
    ) {}

    public function toArray(): array
    {
        return [
            'auth' => [
                'user' => $this->user,
            ],
            'seasonRaces' => $this->seasonRaces,
            'activeRaceId' => $this->activeRaceId,
            'currentStage' => $this->currentStage,
        ];
    }
}
