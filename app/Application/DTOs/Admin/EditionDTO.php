<?php

declare(strict_types=1);

namespace App\Application\DTOs\Admin;

readonly class EditionDTO
{
    public function __construct(
        public string $id,
        public int $year,
        public string $startDate,
        public string $endDate,
        public string $status,
    ) {}
}

readonly class CreateEditionDTO
{
    public function __construct(
        public int $year,
        public string $startDate,
        public string $endDate,
    ) {}
}

readonly class UpdateEditionDTO
{
    public function __construct(
        public int $year,
        public string $startDate,
        public string $endDate,
        public string $status,
    ) {}
}
