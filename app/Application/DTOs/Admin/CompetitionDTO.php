<?php

declare(strict_types=1);

namespace App\Application\DTOs\Admin;

readonly class CompetitionDTO
{
    public function __construct(
        public string $id,
        public string $name,
        public string $type,
        public ?string $countryId,
        public bool $active,
        public int $editionsCount,
        public ?string $coverImage = null,
        public ?string $logoImage = null,
        public ?string $coverImageUrl = null,
        public ?string $logoImageUrl = null,
    ) {}
}

readonly class CreateCompetitionDTO
{
    public function __construct(
        public string $name,
        public string $type,
        public string $countryId,
    ) {}
}

readonly class UpdateCompetitionDTO
{
    public function __construct(
        public string $name,
        public string $type,
        public string $countryId,
        public bool $active,
    ) {}
}
