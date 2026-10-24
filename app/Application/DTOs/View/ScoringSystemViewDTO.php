<?php

declare(strict_types=1);

namespace App\Application\DTOs\View;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Scoring system with its rules: {name, type, description, rules}.
 */
readonly class ScoringSystemViewDTO implements Arrayable
{
    /**
     * @param  ScoringRuleViewDTO[]  $rules
     */
    public function __construct(
        public string $name,
        public string $type,
        public string $description,
        public array $rules,
    ) {}

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'type' => $this->type,
            'description' => $this->description,
            'rules' => $this->rules,
        ];
    }
}
