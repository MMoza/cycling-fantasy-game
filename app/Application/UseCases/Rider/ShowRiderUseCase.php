<?php

declare(strict_types=1);

namespace App\Application\UseCases\Rider;

use App\Infrastructure\Persistence\Models\CompetitionParticipantModel;
use App\Infrastructure\Persistence\Models\RiderModel;

class ShowRiderUseCase
{
    public function execute(string $leagueId, string $riderId): array
    {
        $rider = RiderModel::with('country')->findOrFail($riderId);

        $participant = CompetitionParticipantModel::where('rider_id', $riderId)
            ->where('edition_id', function ($query) use ($leagueId) {
                $query->select('edition_id')->from('leagues')->where('id', $leagueId);
            })
            ->with('team')
            ->first();

        return [
            'id' => $rider->id,
            'first_name' => $rider->first_name,
            'last_name' => $rider->last_name,
            'full_name' => $rider->full_name,
            'country_id' => $rider->country_id,
            'country_name' => $rider->country?->name,
            'age' => $rider->age,
            'birth_date' => $rider->birth_date?->format('Y-m-d'),
            'profile_image' => $rider->profile_image,
            'team' => $participant?->team ? [
                'id' => $participant->team->id,
                'name' => $participant->team->name,
            ] : null,
            'league_id' => $leagueId,
        ];
    }
}
