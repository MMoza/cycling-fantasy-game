<?php

declare(strict_types=1);

namespace App\Application\DTOs\View;

use App\Domain\Interfaces\MediaUrlResolverInterface;
use App\Infrastructure\Persistence\Models\CompetitionModel;
use Illuminate\Contracts\Support\Arrayable;

/**
 * Competition block of the league dashboard: {name, year, type, coverImageUrl, logoImageUrl}.
 */
readonly class CompetitionViewDTO implements Arrayable
{
    public function __construct(
        public string $name,
        public int $year,
        public string $type,
        public ?string $coverImageUrl,
        public ?string $logoImageUrl,
    ) {}

    public static function fromModel(CompetitionModel $competition, int $year, MediaUrlResolverInterface $mediaUrls): self
    {
        return new self(
            name: $competition->name,
            year: $year,
            type: $competition->type->value,
            coverImageUrl: $mediaUrls->resolve($competition->cover_image),
            logoImageUrl: $mediaUrls->resolve($competition->logo_image),
        );
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'year' => $this->year,
            'type' => $this->type,
            'coverImageUrl' => $this->coverImageUrl,
            'logoImageUrl' => $this->logoImageUrl,
        ];
    }
}
