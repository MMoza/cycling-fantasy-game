<?php

declare(strict_types=1);

namespace App\Domain\Entities;

use App\Domain\ValueObjects\EditionStatus;
use Illuminate\Support\Str;

readonly class Edition
{
    public function __construct(
        public string $id,
        public string $competitionId,
        public int $year,
        public string $startDate,
        public string $endDate,
        public EditionStatus $status,
    ) {}

    public static function create(
        string $competitionId,
        int $year,
        string $startDate,
        string $endDate,
    ): self {
        return new self(
            id: Str::uuid()->toString(),
            competitionId: $competitionId,
            year: $year,
            startDate: $startDate,
            endDate: $endDate,
            status: EditionStatus::Upcoming,
        );
    }

    public function start(): self
    {
        return new self(
            id: $this->id,
            competitionId: $this->competitionId,
            year: $this->year,
            startDate: $this->startDate,
            endDate: $this->endDate,
            status: EditionStatus::Ongoing,
        );
    }

    public function finish(): self
    {
        return new self(
            id: $this->id,
            competitionId: $this->competitionId,
            year: $this->year,
            startDate: $this->startDate,
            endDate: $this->endDate,
            status: EditionStatus::Finished,
        );
    }
}
