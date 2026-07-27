<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controllers;

use App\Application\UseCases\Invitation\GetOrCreateInvitationUseCase;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InvitationController
{
    public function __construct(
        private readonly GetOrCreateInvitationUseCase $getOrCreateInvitationUseCase,
    ) {}

    public function show(Request $request): JsonResponse
    {
        $invitation = $this->getOrCreateInvitationUseCase->execute($request->user()->id);

        return response()->json([
            'code' => $invitation->code->value,
            'accepted_count' => $invitation->accepted_count,
            'invite_url' => route('register', ['ref' => $invitation->code->value]),
        ]);
    }
}
