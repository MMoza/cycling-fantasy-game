<?php

declare(strict_types=1);

namespace App\Application\DTOs\Admin;

readonly class RiderParticipantDTO
{
    public function __construct(
        public string $id,
        public string $fullName,
        public ?string $country,
        public bool $active,
    ) {}
}
