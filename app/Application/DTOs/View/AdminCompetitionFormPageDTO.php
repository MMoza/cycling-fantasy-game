<?php

declare(strict_types=1);

namespace App\Application\DTOs\View;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Collection;

/**
 * All props of the Admin/Competitions/Form page.
 */
readonly class AdminCompetitionFormPageDTO implements Arrayable
{
    /**
     * @param  array{id: string, name: string, type: string, country_id: string|null, active: bool, cover_image: string|null, logo_image: string|null, pcs_slug: string|null, cover_image_url: string|null, logo_image_url: string|null}|null  $competition
     * @param  Collection<int, array{value: string, label: string}>  $countries
     */
    public function __construct(
        public mixed $competition,
        public mixed $countries,
    ) {}

    public function toArray(): array
    {
        return [
            'competition' => $this->competition,
            'countries' => $this->countries,
        ];
    }
}
