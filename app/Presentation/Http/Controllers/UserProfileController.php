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
        $data = $this->showUserProfileUseCase->execute($request->user(), $league, $member);

        return Inertia::render('Users/Show', [
            'league_id' => $data['league_id'],
            'league_name' => $data['league_name'],
            'competition_started' => $data['competition_started'],
            'has_stage_predictions' => $data['has_stage_predictions'],
            'user' => $data['user'],
            'global_stats' => $data['global_stats'],
            'points_history' => $data['points_history'],
            'pre_race_predictions' => $data['pre_race_predictions'],
            'stage_details' => $data['stage_details'],
        ]);
    }
}
