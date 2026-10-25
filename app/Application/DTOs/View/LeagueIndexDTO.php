<?php

declare(strict_types=1);

namespace App\Application\DTOs\View;

use App\Application\DTOs\LeagueDTO;
use Illuminate\Contracts\Support\Arrayable;

/**
 * All props of the Leagues/Index page: {my_leagues, public_leagues, search_query}.
 */
readonly class LeagueIndexDTO implements Arrayable
{
    /**
     * @param  iterable<LeagueDTO>  $myLeagues
     * @param  iterable<PublicLeagueCardDTO>|array<array-key, never>  $publicLeagues
     */
    public function __construct(
        public mixed $myLeagues,
        public mixed $publicLeagues,
        public string $searchQuery,
    ) {}

    public function toArray(): array
    {
        return [
            'my_leagues' => $this->myLeagues,
            'public_leagues' => $this->publicLeagues,
            'search_query' => $this->searchQuery,
        ];
    }
}
