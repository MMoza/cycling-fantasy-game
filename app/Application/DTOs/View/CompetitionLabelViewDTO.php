<?php

declare(strict_types=1);

namespace App\Application\DTOs\View;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Competition label used inside edition options: {name}.
 */
readonly class CompetitionLabelViewDTO implements Arrayable
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
