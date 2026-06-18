<?php

declare(strict_types=1);

namespace App\Domain\Entities;

use App\Domain\ValueObjects\CompetitionType;
use Illuminate\Support\Str;

readonly class Competition
{
    public function __construct(
        public string $id,
        public string $name,
        public CompetitionType $type,
        public string $country,
        public bool $active,
    ) {}

    public static function create(
        string $name,
        CompetitionType $type,
        string $country,
    ): self {
        return new self(
            id: Str::uuid()->toString(),
            name: $name,
            type: $type,
            country: $country,
            active: true,
        );
    }

    public function deactivate(): self
    {
        return new self(
            id: $this->id,
            name: $this->name,
            type: $this->type,
            country: $this->country,
            active: false,
        );
    }

    public function activate(): self
    {
        return new self(
            id: $this->id,
            name: $this->name,
            type: $this->type,
            country: $this->country,
            active: true,
        );
    }
}
