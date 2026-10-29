<?php

declare(strict_types=1);

namespace App\Application\UseCases\Season;

use App\Application\DTOs\JoinAllSeasonResultDTO;
use App\Application\Exceptions\ApplicationException;
use App\Application\UseCases\League\JoinLeagueUseCase;
use App\Infrastructure\Persistence\Models\EditionModel;
use App\Models\User;

class JoinAllMissingCompetitionsUseCase
{
    public function __construct(private readonly JoinLeagueUseCase $joinLeagueUseCase) {}

    public function execute(User $user, int $year): JoinAllSeasonResultDTO
    {
        $userLeagueIds = $user->leagues()->pluck('leagues.id')->all();

        $editions = EditionModel::with(['leagues' => fn ($q) => $q->where('is_official', true)])
            ->where('year', $year)
            ->whereHas('competition', fn ($q) => $q->where('active', true))
            ->orderBy('start_date')
            ->get();

        $joined = 0;
        $alreadyMember = 0;
        $notJoinable = 0;
        $error = null;

        foreach ($editions as $edition) {
            $officialLeague = $edition->leagues->first();

            if ($officialLeague === null) {
                continue;
            }

            if (in_array($officialLeague->id, $userLeagueIds, true)) {
                $alreadyMember++;

                continue;
            }

            if ($edition->status->value === 'finished') {
                $notJoinable++;

                continue;
            }

            try {
                $this->joinLeagueUseCase->executeById($user, $officialLeague->id);
                $joined++;
            } catch (ApplicationException $e) {
                // Se conservan las uniones ya hechas y se reporta el primer error
                // (típicamente el límite de ligas del plan).
                $error ??= $e->getMessage();
            }
        }

        return new JoinAllSeasonResultDTO(
            joined: $joined,
            alreadyMember: $alreadyMember,
            notJoinable: $notJoinable,
            error: $error,
        );
    }
}
