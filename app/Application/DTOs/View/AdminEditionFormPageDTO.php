<?php

declare(strict_types=1);

namespace App\Application\DTOs\View;

use Illuminate\Contracts\Support\Arrayable;

/**
 * All props of the Admin/Editions/Form page.
 */
readonly class AdminEditionFormPageDTO implements Arrayable
{
    /**
     * @param  array{id: string, name: string}  $competition
     * @param  array{id: string, year: int, start_date: string, end_date: string, status: string}|null  $edition
     */
    public function __construct(
        public mixed $competition,
        public mixed $edition,
    ) {}

    public function toArray(): array
    {
        return [
            'competition' => $this->competition,
            'edition' => $this->edition,
        ];
    }
}
