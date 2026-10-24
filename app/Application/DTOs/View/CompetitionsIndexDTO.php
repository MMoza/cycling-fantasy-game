<?php

declare(strict_types=1);

namespace App\Application\DTOs\View;

use Illuminate\Contracts\Support\Arrayable;

/**
 * All props of the Competitions/Index page (camelCase wire keys):
 * {yearGroups, years, currentYear}.
 */
readonly class CompetitionsIndexDTO implements Arrayable
{
    /**
     * @param  array<int, mixed>  $yearGroups
     * @param  array<int, int>  $years
     */
    public function __construct(
        public array $yearGroups,
        public array $years,
        public int $currentYear,
    ) {}

    public function toArray(): array
    {
        return [
            'yearGroups' => $this->yearGroups,
            'years' => $this->years,
            'currentYear' => $this->currentYear,
        ];
    }
}
