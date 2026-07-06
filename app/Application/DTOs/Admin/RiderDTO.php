<?php

declare(strict_types=1);

namespace App\Application\DTOs\Admin;

readonly class RiderDTO
{
    public function __construct(
        public string $id,
        public string $firstName,
        public string $lastName,
        public string $fullName,
        public ?string $countryId,
        public ?string $profileImage,
        public ?int $age,
        public ?string $teamName = null,
    ) {}
}

readonly class CreateRiderDTO
{
    public function __construct(
        public string $firstName,
        public string $lastName,
        public ?string $countryId,
        public ?string $birthDate,
        public ?string $profileImage,
    ) {}
}

readonly class UpdateRiderDTO
{
    public function __construct(
        public string $firstName,
        public string $lastName,
        public ?string $countryId,
        public ?string $birthDate,
        public ?string $profileImage,
    ) {}
}
