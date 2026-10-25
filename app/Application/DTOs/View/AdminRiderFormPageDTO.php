<?php

declare(strict_types=1);

namespace App\Application\DTOs\View;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Collection;

/**
 * All props of the Admin/Riders/Form page.
 */
readonly class AdminRiderFormPageDTO implements Arrayable
{
    /**
     * @param  array{id: string, first_name: string, last_name: string, country_id: string|null, birth_date: string|null, profile_image: string|null}|null  $rider
     * @param  Collection<int, array{value: string, label: string}>  $countries
     */
    public function __construct(
        public mixed $rider,
        public mixed $countries,
    ) {}

    public function toArray(): array
    {
        return [
            'rider' => $this->rider,
            'countries' => $this->countries,
        ];
    }
}
