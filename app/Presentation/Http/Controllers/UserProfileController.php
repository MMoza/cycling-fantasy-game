<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controllers;

use App\Application\UseCases\UserProfile\ShowUserProfileUseCase;
use Illuminate\Http\Request;
use Inertia\Inertia;

class UserProfileController extends Controller
{
    public function __construct(
        private readonly ShowUserProfileUseCase $showUserProfileUseCase,
    ) {}

    public function show(Request $request, string $league, string $member)
    {
        return Inertia::render('Users/Show', $this->showUserProfileUseCase->execute($request->user(), $league, $member)->toArray());
    }
}
