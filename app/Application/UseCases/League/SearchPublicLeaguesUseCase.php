<?php

declare(strict_types=1);

namespace App\Application\UseCases\League;

use App\Application\DTOs\View\PublicLeagueCardDTO;
use App\Infrastructure\Persistence\Models\LeagueModel;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * Searches public leagues for the league index page.
 */
class SearchPublicLeaguesUseCase
{
    /**
     * @return LengthAwarePaginator<int, PublicLeagueCardDTO>
     */
    public function execute(User $user, ?string $search = null): LengthAwarePaginator
    {
        $joinedLeagueIds = $user->leagues()->pluck('leagues.id')->flip()->all();

        $query = LeagueModel::withCount('users')
            ->with(['edition.competition', 'scoringSystem', 'owner'])
            ->where('is_public', true);

        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }

        return $query->orderBy('name')
            ->paginate(20)
            ->through(fn (LeagueModel $league) => PublicLeagueCardDTO::fromModel($league, $joinedLeagueIds));
    }
}
