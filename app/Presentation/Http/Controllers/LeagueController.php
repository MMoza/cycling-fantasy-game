<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controllers;

use App\Application\DTOs\CreateLeagueDTO;
use App\Application\DTOs\View\LeagueIndexDTO;
use App\Application\Exceptions\ApplicationException;
use App\Application\UseCases\League\CreateLeagueUseCase;
use App\Application\UseCases\League\GetCreateLeagueFormDataUseCase;
use App\Application\UseCases\League\GetLeagueDashboardUseCase;
use App\Application\UseCases\League\JoinLeagueUseCase;
use App\Application\UseCases\League\ListLeaguesUseCase;
use App\Application\UseCases\League\SearchPublicLeaguesUseCase;
use App\Application\UseCases\League\UpdateLeagueUseCase;
use Illuminate\Http\Request;
use Inertia\Inertia;

class LeagueController extends Controller
{
    public function __construct(
        private readonly ListLeaguesUseCase $listLeaguesUseCase,
        private readonly GetCreateLeagueFormDataUseCase $getCreateLeagueFormDataUseCase,
        private readonly CreateLeagueUseCase $createLeagueUseCase,
        private readonly GetLeagueDashboardUseCase $getLeagueDashboardUseCase,
        private readonly SearchPublicLeaguesUseCase $searchPublicLeaguesUseCase,
        private readonly JoinLeagueUseCase $joinLeagueUseCase,
        private readonly UpdateLeagueUseCase $updateLeagueUseCase,
    ) {}

    public function index(Request $request)
    {
        $search = $request->query('q');
        $showPublicLeagues = $search || $request->has('page');

        return Inertia::render('Leagues/Index', (new LeagueIndexDTO(
            myLeagues: $this->listLeaguesUseCase->execute($request->user()),
            publicLeagues: $showPublicLeagues
                ? $this->searchPublicLeaguesUseCase->execute($request->user(), $search)
                : [],
            searchQuery: $search ?? '',
        ))->toArray());
    }

    public function create()
    {
        return Inertia::render('Leagues/Create', $this->getCreateLeagueFormDataUseCase->execute()->toArray());
    }

    public function show(Request $request, string $league)
    {
        return Inertia::render('Leagues/Show', $this->getLeagueDashboardUseCase->execute($request->user(), $league)->toArray());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'edition_id' => ['required', 'uuid', 'exists:editions,id'],
            'scoring_system_id' => ['required', 'uuid', 'exists:scoring_systems,id'],
            'is_public' => ['required', 'boolean'],
            'is_official' => ['sometimes', 'boolean'],
        ]);

        $dto = new CreateLeagueDTO(
            name: $validated['name'],
            editionId: $validated['edition_id'],
            scoringSystemId: $validated['scoring_system_id'],
            isPublic: $validated['is_public'],
            isOfficial: $validated['is_official'] ?? false,
        );

        try {
            $league = $this->createLeagueUseCase->execute($request->user(), $dto);
        } catch (ApplicationException $e) {
            return redirect()->back()->withErrors(['plan' => $e->getMessage()])->withInput();
        }

        return redirect()->route('leagues.show', $league->id);
    }

    public function join(Request $request)
    {
        $validated = $request->validate([
            'invite_code' => ['required', 'string', 'exists:leagues,invite_code'],
        ]);

        try {
            $league = $this->joinLeagueUseCase->execute($request->user(), $validated['invite_code']);
        } catch (ApplicationException $e) {
            return redirect()->back()->withErrors(['invite_code' => $e->getMessage()]);
        }

        return redirect()->route('leagues.show', $league->id);
    }

    public function joinOfficial(Request $request)
    {
        $validated = $request->validate([
            'league_id' => ['required', 'uuid', 'exists:leagues,id'],
        ]);

        try {
            $league = $this->joinLeagueUseCase->executeById($request->user(), $validated['league_id']);
        } catch (ApplicationException $e) {
            return redirect()->back()->withErrors(['league' => $e->getMessage()]);
        }

        return redirect()->route('leagues.show', $league->id);
    }

    public function update(Request $request, string $league)
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'is_public' => ['sometimes', 'boolean'],
        ]);

        try {
            $this->updateLeagueUseCase->execute($request->user(), $league, $validated);
        } catch (ApplicationException $e) {
            return redirect()->back()->withErrors(['settings' => $e->getMessage()]);
        }

        return redirect()->back();
    }
}
