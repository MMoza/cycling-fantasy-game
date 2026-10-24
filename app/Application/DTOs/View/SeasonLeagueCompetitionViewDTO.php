<?php

declare(strict_types=1);

namespace App\Application\DTOs\View;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Competition block of a season league: {name, type, coverImageUrl, logoImageUrl}.
 */
readonly class SeasonLeagueCompetitionViewDTO implements Arrayable
{
    public function __construct(
        public string $name,
        public string $type,
        public ?string $coverImageUrl,
        public ?string $logoImageUrl,
    ) {}

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'type' => $this->type,
            'coverImageUrl' => $this->coverImageUrl,
            'logoImageUrl' => $this->logoImageUrl,
        ];
    }
}
