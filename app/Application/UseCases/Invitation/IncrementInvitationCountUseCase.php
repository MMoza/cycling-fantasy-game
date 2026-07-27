<?php

declare(strict_types=1);

namespace App\Application\UseCases\Invitation;

use App\Application\Exceptions\ApplicationException;
use App\Infrastructure\Persistence\Models\InvitationModel;

final class IncrementInvitationCountUseCase
{
    public function execute(string $code): void
    {
        $invitation = InvitationModel::where('code', $code)->first();

        if ($invitation === null) {
            throw new ApplicationException('Código de invitación inválido');
        }

        $invitation->increment('accepted_count');
    }
}
