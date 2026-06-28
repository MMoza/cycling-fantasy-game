<?php

declare(strict_types=1);

namespace App\Application\DTOs\Admin;

readonly class TeamDTO
{
    public function __construct(
        public string $id,
        public string $name,
        public ?string $abbreviation,
        public ?string $countryId,
        public ?string $logoUrl,
        public int $ridersCount,
    ) {}
}

readonly class CreateTeamDTO
{
    public function __construct(
        public string $name,
        public ?string $abbreviation,
        public ?string $countryId,
        public ?string $logoUrl,
    ) {}
}

readonly class UpdateTeamDTO
{
    public function __construct(
        public string $name,
        public ?string $abbreviation,
        public ?string $countryId,
        public ?string $logoUrl,
    ) {}
}
