<?php

declare(strict_types=1);

namespace App\Application\DTOs\View;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Collection;

/**
 * All props of the Admin/Stages/Form page.
 */
readonly class AdminStageFormPageDTO implements Arrayable
{
    /**
     * @param  array{id: string, year: int, competition: string, competition_id: string, competition_type: string}  $edition
     * @param  array{id: string, number: int, name: string, date: string, type: string, distance: float|null, elevation_gain: int|null, difficulty: int|null, origin: string|null, destination: string|null, profile_image: string|null, scheduled_start: string|null, live_stream_url: string|null, status: string}|null  $stage
     * @param  Collection<int, array{value: string, label: string}>  $stageTypes
     * @param  array<int, array{id: string, name: string, country_id: string|null, team_name: string|null}>  $availableParticipants
     * @param  array<int, string>  $stageParticipantIds
     */
    public function __construct(
        public mixed $edition,
        public mixed $stage,
        public mixed $stageTypes,
        public mixed $availableParticipants,
        public mixed $stageParticipantIds,
    ) {}

    public function toArray(): array
    {
        return [
            'edition' => $this->edition,
            'stage' => $this->stage,
            'stageTypes' => $this->stageTypes,
            'availableParticipants' => $this->availableParticipants,
            'stageParticipantIds' => $this->stageParticipantIds,
        ];
    }
}
