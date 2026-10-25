<?php

declare(strict_types=1);

namespace App\Application\DTOs\View;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Collection;

/**
 * All props of the Admin/Stages/Show page: the complete payload built by
 * ShowAdminStageUseCase. `availableTeams` and `is_ttt` are consumed by the
 * page (team selector for TTT results), so they stay on the wire.
 */
readonly class AdminStageShowPageDTO implements Arrayable
{
    /**
     * @param  array{id: string, year: int, competition: string}  $edition
     * @param  array{id: string, number: int, name: string, type: string, type_value: string, date: string, distance: float|null, elevation_gain: int|null, difficulty: int|null, origin: string|null, destination: string|null, status: string, status_label: string}  $stage
     * @param  Collection<int, array{id: string, name: string, country_id: string|null}>  $availableRiders
     * @param  Collection<int, array{id: string, name: string}>  $availableTeams
     * @param  array<int, array{id: string, rider_id: string, position: int, time: string|null, gap: string|null, is_gc_leader: bool, is_combativo: bool}>  $stageParticipants
     * @param  Collection<int, array{id: string, rider_id: string, position: int, time: string|null, gap: string|null, is_gc_leader: bool, is_combativo: bool}>  $results
     */
    public function __construct(
        public mixed $edition,
        public mixed $stage,
        public mixed $availableRiders,
        public mixed $availableTeams,
        public bool $isTtt,
        public mixed $stageParticipants,
        public mixed $results,
    ) {}

    public function toArray(): array
    {
        return [
            'edition' => $this->edition,
            'stage' => $this->stage,
            'availableRiders' => $this->availableRiders,
            'availableTeams' => $this->availableTeams,
            'is_ttt' => $this->isTtt,
            'stageParticipants' => $this->stageParticipants,
            'results' => $this->results,
        ];
    }
}
