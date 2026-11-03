<?php

declare(strict_types=1);

namespace App\Domain\Entities;

use App\Domain\Support\Random;
use App\Domain\ValueObjects\CompetitionType;

readonly class Competition
{
    public function __construct(
        public string $id,
        public string $name,
        public CompetitionType $type,
        public string $country,
        public bool $active,
        public ?string $coverImage = null,
        public ?string $logoImage = null,
    ) {}

    public static function create(
        string $name,
        CompetitionType $type,
        string $country,
        ?string $coverImage = null,
        ?string $logoImage = null,
    ): self {
        return new self(
            id: Random::uuid(),
            name: $name,
            type: $type,
            country: $country,
            active: true,
            coverImage: $coverImage,
            logoImage: $logoImage,
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
            coverImage: $this->coverImage,
            logoImage: $this->logoImage,
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
            coverImage: $this->coverImage,
            logoImage: $this->logoImage,
        );
    }

    public function withCoverImage(string $path): self
    {
        return new self(
            id: $this->id,
            name: $this->name,
            type: $this->type,
            country: $this->country,
            active: $this->active,
            coverImage: $path,
            logoImage: $this->logoImage,
        );
    }

    public function withLogoImage(string $path): self
    {
        return new self(
            id: $this->id,
            name: $this->name,
            type: $this->type,
            country: $this->country,
            active: $this->active,
            coverImage: $this->coverImage,
            logoImage: $path,
        );
    }
}
