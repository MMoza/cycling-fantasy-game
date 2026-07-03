<?php

declare(strict_types=1);

namespace App\Application\UseCases\League;

use App\Application\Exceptions\ApplicationException;
use App\Infrastructure\Persistence\Models\LeagueModel;
use App\Models\User;

class UpdateLeagueUseCase
{
    public function execute(User $user, string $leagueId, array $data): LeagueModel
    {
        $league = LeagueModel::findOrFail($leagueId);

        if ($league->owner_id !== $user->id) {
            throw new ApplicationException('Solo el creador de la liga puede modificarla');
        }

        $league->update([
            'name' => $data['name'] ?? $league->name,
            'is_public' => $data['is_public'] ?? $league->is_public,
        ]);

        return $league->fresh();
    }
}
