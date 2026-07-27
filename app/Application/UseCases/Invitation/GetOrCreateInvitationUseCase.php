<?php

declare(strict_types=1);

namespace App\Application\UseCases\Invitation;

use App\Domain\ValueObjects\InvitationCode;
use App\Infrastructure\Persistence\Models\InvitationModel;
use Illuminate\Support\Str;

final class GetOrCreateInvitationUseCase
{
    public function execute(string $userId): InvitationModel
    {
        $invitation = InvitationModel::where('user_id', $userId)->first();

        if ($invitation !== null) {
            return $invitation;
        }

        return InvitationModel::create([
            'id' => Str::uuid()->toString(),
            'user_id' => $userId,
            'code' => InvitationCode::generate(),
            'accepted_count' => 0,
        ]);
    }
}
