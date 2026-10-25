<?php

declare(strict_types=1);

namespace App\Application\DTOs\View;

use App\Infrastructure\Persistence\Models\ScoringSystemModel;
use Illuminate\Contracts\Support\Arrayable;

/**
 * Scoring system option: {id, name, description, type}.
 */
readonly class ScoringSystemOptionViewDTO implements Arrayable
{
    public function __construct(
        public string $id,
        public string $name,
        public string $description,
        public string $type,
    ) {}

    public static function fromModel(ScoringSystemModel $scoringSystem): self
    {
        return new self(
            id: $scoringSystem->id,
            name: $scoringSystem->name,
            description: $scoringSystem->description,
            type: $scoringSystem->type->value,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'type' => $this->type,
        ];
    }
}
