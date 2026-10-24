<?php

declare(strict_types=1);

namespace App\Application\DTOs\View;

use App\Application\DTOs\Admin\TeamDTO;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Collection;

/**
 * All props of the Admin/Teams/Index page.
 */
readonly class AdminTeamsListPageDTO implements Arrayable
{
    /**
     * @param  Collection<int, TeamDTO>  $teams
     * @param  Collection<int, array{value: string, label: string}>  $countries
     */
    public function __construct(
        public mixed $teams,
        public mixed $countries,
    ) {}

    public function toArray(): array
    {
        return [
            'teams' => $this->teams,
            'countries' => $this->countries,
        ];
    }
}
