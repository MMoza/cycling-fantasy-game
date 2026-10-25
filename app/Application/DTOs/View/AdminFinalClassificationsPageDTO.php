<?php

declare(strict_types=1);

namespace App\Application\DTOs\View;

use App\Infrastructure\Persistence\Models\FinalClassificationModel;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Collection;

/**
 * All props of the Admin/Editions/FinalClassifications page.
 */
readonly class AdminFinalClassificationsPageDTO implements Arrayable
{
    /**
     * @param  array{id: string, year: int, competition: string}  $edition
     * @param  Collection<int, array{value: string, label: string}>  $riders
     * @param  Collection<int, array{value: string, label: string}>  $teams
     * @param  Collection<string, Collection<int, FinalClassificationModel>>  $classifications
     */
    public function __construct(
        public mixed $edition,
        public mixed $riders,
        public mixed $teams,
        public mixed $classifications,
    ) {}

    public function toArray(): array
    {
        return [
            'edition' => $this->edition,
            'riders' => $this->riders,
            'teams' => $this->teams,
            'classifications' => $this->classifications,
        ];
    }
}
