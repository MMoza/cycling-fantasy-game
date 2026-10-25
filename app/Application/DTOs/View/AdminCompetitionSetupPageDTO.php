<?php

declare(strict_types=1);

namespace App\Application\DTOs\View;

use App\Infrastructure\Persistence\Models\TeamModel;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Collection;

/**
 * All props of the Admin/Competitions/Setup page.
 */
readonly class AdminCompetitionSetupPageDTO implements Arrayable
{
    /**
     * @param  array{id: string, name: string}  $competition
     * @param  array{id: string, year: int, stages_count: int}  $edition
     * @param  Collection<int, array{team_id: string, team_name: string, riders: Collection<int, array{id: string, full_name: string, country: string|null, active: bool}>}>  $participants
     * @param  Collection<int, TeamModel>  $teams
     */
    public function __construct(
        public mixed $competition,
        public mixed $edition,
        public mixed $participants,
        public mixed $teams,
    ) {}

    public function toArray(): array
    {
        return [
            'competition' => $this->competition,
            'edition' => $this->edition,
            'participants' => $this->participants,
            'teams' => $this->teams,
        ];
    }
}
