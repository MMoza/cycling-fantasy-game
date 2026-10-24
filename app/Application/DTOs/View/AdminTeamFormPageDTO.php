<?php

declare(strict_types=1);

namespace App\Application\DTOs\View;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Collection;

/**
 * All props of the Admin/Teams/Form page.
 */
readonly class AdminTeamFormPageDTO implements Arrayable
{
    /**
     * @param  array{id: string, name: string, abbreviation: string|null, country_id: string|null, logo_url: string|null}|null  $team
     * @param  Collection<int, array{value: string, label: string}>  $countries
     */
    public function __construct(
        public mixed $team,
        public mixed $countries,
    ) {}

    public function toArray(): array
    {
        return [
            'team' => $this->team,
            'countries' => $this->countries,
        ];
    }
}
