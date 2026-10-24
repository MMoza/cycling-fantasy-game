<?php

declare(strict_types=1);

namespace App\Application\DTOs\View;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Form data for the league creation page: {editions, scoringSystems}.
 */
readonly class LeagueFormDataDTO implements Arrayable
{
    /**
     * @param  EditionOptionViewDTO[]  $editions
     * @param  ScoringSystemOptionViewDTO[]  $scoringSystems
     */
    public function __construct(
        public array $editions,
        public array $scoringSystems,
    ) {}

    public function toArray(): array
    {
        return [
            'editions' => $this->editions,
            'scoringSystems' => $this->scoringSystems,
        ];
    }
}
