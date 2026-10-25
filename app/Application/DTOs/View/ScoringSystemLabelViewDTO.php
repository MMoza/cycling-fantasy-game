<?php

declare(strict_types=1);

namespace App\Application\DTOs\View;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Scoring system label used inside league cards: {name}.
 */
readonly class ScoringSystemLabelViewDTO implements Arrayable
{
    public function __construct(
        public string $name,
    ) {}

    public function toArray(): array
    {
        return [
            'name' => $this->name,
        ];
    }
}
