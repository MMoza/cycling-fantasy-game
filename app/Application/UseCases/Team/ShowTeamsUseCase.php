<?php

declare(strict_types=1);

namespace App\Application\UseCases\Team;

use App\Domain\Interfaces\MediaUrlResolverInterface;
use App\Infrastructure\Persistence\Models\CompetitionParticipantModel;
use App\Infrastructure\Persistence\Models\LeagueModel;

readonly class TeamRider
{
    public function __construct(
        public string $id,
        public string $firstName,
        public string $lastName,
        public string $fullName,
        public ?string $countryId,
        public ?string $countryName,
        public ?string $profileImageUrl,
        public ?int $age,
    ) {}
}

readonly class TeamData
{
    /** @param TeamRider[] $riders */
    public function __construct(
        public string $id,
        public string $name,
        public ?string $abbreviation,
        public ?string $countryId,
        public ?string $countryName,
        public ?string $logoUrl,
        public array $riders,
    ) {}
}

class ShowTeamsUseCase
{
    public function __construct(private readonly MediaUrlResolverInterface $mediaUrls) {}

    public function execute(string $leagueId): array
    {
        $league = LeagueModel::with('edition.competition')->findOrFail($leagueId);

        $participants = CompetitionParticipantModel::where('edition_id', $league->edition_id)
            ->with(['team.country', 'rider.country'])
            ->get();

        $grouped = $participants->groupBy('team_id');

        $teams = [];
        foreach ($grouped as $teamId => $teamParticipants) {
            $firstParticipant = $teamParticipants->first();
            $team = $firstParticipant->team;

            if ($team === null) {
                continue;
            }

            $riders = [];
            foreach ($teamParticipants as $participant) {
                $rider = $participant->rider;

                if ($rider === null) {
                    continue;
                }

                $riders[] = new TeamRider(
                    id: $rider->id,
                    firstName: $rider->first_name,
                    lastName: $rider->last_name,
                    fullName: "{$rider->first_name} {$rider->last_name}",
                    countryId: $rider->country_id,
                    countryName: $rider->country?->name,
                    profileImageUrl: $this->mediaUrls->resolve($rider->profile_image),
                    age: $rider->age,
                );
            }

            usort($riders, fn (TeamRider $a, TeamRider $b) => strcmp($a->lastName, $b->lastName));

            $teams[] = new TeamData(
                id: $team->id,
                name: $team->name,
                abbreviation: $team->abbreviation,
                countryId: $team->country_id,
                countryName: $team->country?->name,
                logoUrl: $this->mediaUrls->resolve($team->logo_url),
                riders: $riders,
            );
        }

        usort($teams, fn (TeamData $a, TeamData $b) => strcmp($a->name, $b->name));

        return [
            'league_id' => $leagueId,
            'league_name' => $league->name,
            'competition_name' => $league->edition->competition->name,
            'year' => $league->edition->year,
            'teams' => $teams,
            'total_teams' => count($teams),
            'total_riders' => $participants->count(),
        ];
    }
}
