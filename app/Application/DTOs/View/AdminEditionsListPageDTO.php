<?php

declare(strict_types=1);

namespace App\Application\DTOs\View;

use App\Application\DTOs\Admin\EditionDTO;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Collection;

/**
 * All props of the Admin/Editions/Index page.
 */
readonly class AdminEditionsListPageDTO implements Arrayable
{
    /**
     * @param  array{id: string, name: string}  $competition
     * @param  Collection<int, EditionDTO>  $editions
     */
    public function __construct(
        public mixed $competition,
        public mixed $editions,
    ) {}

    public function toArray(): array
    {
        return [
            'competition' => $this->competition,
            'editions' => $this->editions,
        ];
    }
}
