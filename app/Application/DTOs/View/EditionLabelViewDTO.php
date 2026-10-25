<?php

declare(strict_types=1);

namespace App\Application\DTOs\View;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Edition label used inside league cards: {name, year}.
 */
readonly class EditionLabelViewDTO implements Arrayable
{
    public function __construct(
        public string $name,
        public int $year,
    ) {}

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'year' => $this->year,
        ];
    }
}
