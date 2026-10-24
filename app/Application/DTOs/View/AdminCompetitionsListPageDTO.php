<?php

declare(strict_types=1);

namespace App\Application\DTOs\View;

use App\Application\DTOs\Admin\CompetitionDTO;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Collection;

/**
 * All props of the Admin/Competitions/Index page.
 */
readonly class AdminCompetitionsListPageDTO implements Arrayable
{
    /**
     * @param  Collection<int, CompetitionDTO>  $competitions
     * @param  Collection<int, array{value: string, label: string}>  $countries
     */
    public function __construct(
        public mixed $competitions,
        public mixed $countries,
    ) {}

    public function toArray(): array
    {
        return [
            'competitions' => $this->competitions,
            'countries' => $this->countries,
        ];
    }
}
