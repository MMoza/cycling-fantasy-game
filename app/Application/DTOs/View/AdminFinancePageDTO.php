<?php

declare(strict_types=1);

namespace App\Application\DTOs\View;

use Illuminate\Contracts\Support\Arrayable;

/**
 * All props of the Admin/Finance page.
 */
readonly class AdminFinancePageDTO implements Arrayable
{
    /**
     * @param  array<string, mixed>  $summary
     * @param  array<string, mixed>  $movements
     * @param  array{year: int, type: string|null, category: string|null, from: string|null, to: string|null, page: int}  $filters
     * @param  list<array{value: string, label: string, type: string}>  $categories
     */
    public function __construct(
        public mixed $summary,
        public mixed $movements,
        public mixed $filters,
        public mixed $categories,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'summary' => $this->summary,
            'movements' => $this->movements,
            'filters' => $this->filters,
            'categories' => $this->categories,
        ];
    }
}
