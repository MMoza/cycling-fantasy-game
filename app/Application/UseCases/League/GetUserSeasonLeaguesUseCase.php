<?php

declare(strict_types=1);

namespace App\Application\UseCases\League;

use App\Domain\Interfaces\MediaUrlResolverInterface;
use App\Infrastructure\Persistence\Models\LeagueModel;
use App\Models\User;

class GetUserSeasonLeaguesUseCase
{
    public function __construct(private readonly MediaUrlResolverInterface $mediaUrls) {}

    /**
     * Returns the leagues the user belongs to whose edition belongs to the
     * current season (year), ordered by edition start date.
     *
     * When the active league belongs to another season it is appended at the
     * end, so the dashboard navigation never loses the current context.
     *
     * @return array<int, array<string, mixed>>
     */
    public function execute(User $user, ?string $activeLeagueId = null): array
    {
        $year = (int) date('Y');

        $seasonLeagues = $user->leagues()
            ->with(['edition.competition'])
            ->withCount('users')
            ->whereHas('edition', fn ($query) => $query->where('year', $year))
            ->get()
            ->sort(fn (LeagueModel $a, LeagueModel $b): int => $this->compare($a, $b))
            ->values();

        if ($activeLeagueId !== null && ! $seasonLeagues->contains('id', $activeLeagueId)) {
            $activeLeague = $user->leagues()
                ->with(['edition.competition'])
                ->withCount('users')
                ->where('leagues.id', $activeLeagueId)
                ->first();

            if ($activeLeague !== null) {
                $seasonLeagues->push($activeLeague);
            }
        }

        return $seasonLeagues
            ->map(fn (LeagueModel $league): array => [
                'id' => $league->id,
                'name' => $league->name,
                'year' => (int) $league->edition->year,
                'is_official' => (bool) $league->is_official,
                'member_count' => (int) $league->users_count,
                'edition_status' => $league->edition->status->value,
                'competition' => [
                    'name' => $league->edition->competition->name,
                    'type' => $league->edition->competition->type->value,
                    'coverImageUrl' => $this->mediaUrls->resolve($league->edition->competition->cover_image),
                    'logoImageUrl' => $this->mediaUrls->resolve($league->edition->competition->logo_image),
                ],
            ])
            ->values()
            ->all();
    }

    private function compare(LeagueModel $a, LeagueModel $b): int
    {
        $aStart = $a->edition->start_date;
        $bStart = $b->edition->start_date;

        if ($aStart === null && $bStart === null) {
            return strcmp($a->name, $b->name);
        }

        if ($aStart === null) {
            return 1;
        }

        if ($bStart === null) {
            return -1;
        }

        $byStart = $aStart->equalTo($bStart) ? 0 : ($aStart->lessThan($bStart) ? -1 : 1);

        return $byStart !== 0 ? $byStart : strcmp($a->name, $b->name);
    }
}
