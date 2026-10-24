<?php

declare(strict_types=1);

namespace App\Application\DTOs\View;

use App\Domain\Interfaces\MediaUrlResolverInterface;
use App\Infrastructure\Persistence\Models\EditionModel;
use Illuminate\Contracts\Support\Arrayable;

/**
 * Race card of the landing carousel (camelCase wire keys).
 */
readonly class LandingRaceViewDTO implements Arrayable
{
    public function __construct(
        public string $editionId,
        public string $name,
        public int $year,
        public string $status,
        public string $startDate,
        public string $endDate,
        public ?string $countryId,
        public ?string $countryName,
        public ?string $logoImageUrl,
        public ?string $coverImageUrl,
        public ?string $officialLeagueId,
    ) {}

    public static function fromModel(EditionModel $edition, MediaUrlResolverInterface $mediaUrls): self
    {
        return new self(
            editionId: $edition->id,
            name: $edition->competition->name,
            year: $edition->year,
            status: $edition->status->value,
            startDate: $edition->start_date->toIso8601String(),
            endDate: $edition->end_date->toIso8601String(),
            countryId: $edition->competition->country_id,
            countryName: $edition->competition?->country?->name,
            logoImageUrl: $mediaUrls->resolve($edition->competition->logo_image),
            coverImageUrl: $mediaUrls->resolve($edition->competition->cover_image),
            officialLeagueId: $edition->leagues->first()?->id,
        );
    }

    public function toArray(): array
    {
        return [
            'editionId' => $this->editionId,
            'name' => $this->name,
            'year' => $this->year,
            'status' => $this->status,
            'startDate' => $this->startDate,
            'endDate' => $this->endDate,
            'countryId' => $this->countryId,
            'countryName' => $this->countryName,
            'logoImageUrl' => $this->logoImageUrl,
            'coverImageUrl' => $this->coverImageUrl,
            'officialLeagueId' => $this->officialLeagueId,
        ];
    }
}
