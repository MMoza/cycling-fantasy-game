<?php

declare(strict_types=1);

namespace App\Application\DTOs\View;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Collection;

/**
 * All props of the Admin/Teams/Show page.
 */
readonly class AdminTeamShowPageDTO implements Arrayable
{
    /**
     * @param  array{id: string, name: string, abbreviation: string|null, country_id: string|null, logo_url: string|null}  $team
     * @param  Collection<int, array{year: int, riders: Collection<int, array{id: string, full_name: string, country_id: string|null}>}>  $rosters
     * @param  Collection<int, array{id: string, full_name: string}>  $allRiders
     * @param  Collection<int, array{value: string, label: string}>  $countries
     */
    public function __construct(
        public mixed $team,
        public mixed $rosters,
        public mixed $allRiders,
        public mixed $countries,
    ) {}

    public function toArray(): array
    {
        return [
            'team' => $this->team,
            'rosters' => $this->rosters,
            'allRiders' => $this->allRiders,
            'countries' => $this->countries,
        ];
    }
}
