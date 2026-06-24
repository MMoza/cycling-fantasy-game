<?php

declare(strict_types=1);

namespace App\Domain\Entities;

use App\Domain\ValueObjects\StageStatus;
use App\Domain\ValueObjects\StageType;

readonly class Stage
{
    public function __construct(
        public string $id,
        public string $editionId,
        public int $number,
        public string $name,
        public string $date,
        public StageType $type,
        public ?float $distance,
        public string $origin,
        public string $destination,
        public StageStatus $status,
        public ?int $elevationGain = null,
        public ?string $profileImage = null,
        public ?int $difficulty = null,
    ) {
    }

    public static function create(
        string $editionId,
        int $number,
        string $name,
        string $date,
        StageType $type,
        ?float $distance,
        string $origin,
        string $destination,
        ?int $elevationGain = null,
        ?string $profileImage = null,
        ?int $difficulty = null,
    ): self {
        return new self(
            id: \Illuminate\Support\Str::uuid()->toString(),
            editionId: $editionId,
            number: $number,
            name: $name,
            date: $date,
            type: $type,
            distance: $distance,
            origin: $origin,
            destination: $destination,
            elevationGain: $elevationGain,
            profileImage: $profileImage,
            difficulty: $difficulty,
            status: StageStatus::Upcoming,
        );
    }

    public function start(): self
    {
        return new self(
            id: $this->id,
            editionId: $this->editionId,
            number: $this->number,
            name: $this->name,
            date: $this->date,
            type: $this->type,
            distance: $this->distance,
            origin: $this->origin,
            destination: $this->destination,
            elevationGain: $this->elevationGain,
            profileImage: $this->profileImage,
            difficulty: $this->difficulty,
            status: StageStatus::Ongoing,
        );
    }

    public function finish(): self
    {
        return new self(
            id: $this->id,
            editionId: $this->editionId,
            number: $this->number,
            name: $this->name,
            date: $this->date,
            type: $this->type,
            distance: $this->distance,
            origin: $this->origin,
            destination: $this->destination,
            elevationGain: $this->elevationGain,
            profileImage: $this->profileImage,
            difficulty: $this->difficulty,
            status: StageStatus::Finished,
        );
    }
}
