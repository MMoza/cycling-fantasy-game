<?php

declare(strict_types=1);

namespace App\Application\DTOs\View;

use App\Infrastructure\Persistence\Models\EditionModel;
use Illuminate\Contracts\Support\Arrayable;

/**
 * Edition option: {id, name, year, competition: {name}}.
 */
readonly class EditionOptionViewDTO implements Arrayable
{
    public function __construct(
        public string $id,
        public string $name,
        public int $year,
        public CompetitionLabelViewDTO $competition,
    ) {}

    public static function fromModel(EditionModel $edition): self
    {
        return new self(
            id: $edition->id,
            name: $edition->competition->name,
            year: (int) $edition->year,
            competition: new CompetitionLabelViewDTO(name: $edition->competition->name),
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'year' => $this->year,
            'competition' => $this->competition,
        ];
    }
}
