<?php

declare(strict_types=1);

namespace App\Application\DTOs\View;

use App\Application\DTOs\Admin\RiderDTO;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Collection;

/**
 * All props of the Admin/Riders/Index page.
 */
readonly class AdminRidersListPageDTO implements Arrayable
{
    /**
     * @param  Collection<int, RiderDTO>  $riders
     * @param  Collection<int, array{value: string, label: string}>  $countries
     */
    public function __construct(
        public mixed $riders,
        public mixed $countries,
    ) {}

    public function toArray(): array
    {
        return [
            'riders' => $this->riders,
            'countries' => $this->countries,
        ];
    }
}
