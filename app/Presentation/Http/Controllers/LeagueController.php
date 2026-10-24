<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controllers;

use App\Application\DTOs\View\LeagueIndexDTO;
use App\Application\Exceptions\ApplicationException;
use App\Application\UseCases\League\CreateLeagueUseCase;
use App\Application\UseCases\League\GetCreateLeagueFormDataUseCase;
use App\Application\UseCases\League\GetLeagueDashboardUseCase;
use App\Application\UseCases\League\JoinLeagueUseCase;
use App\Application\UseCases\League\ListLeaguesUseCase;
use App\Application\UseCases\League\SearchPublicLeaguesUseCase;
use App\Application\UseCases\League\UpdateLeagueUseCase;
use App\Presentation\Http\Requests\League\JoinLeagueRequest;
use App\Presentation\Http\Requests\League\JoinOfficialLeagueRequest;
use App\Presentation\Http\Requests\League\StoreLeagueRequest;
use App\Presentation\Http\Requests\League\UpdateLeagueRequest;
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

    public function store(StoreLeagueRequest $request)
    {
        try {
            $league = $this->createLeagueUseCase->execute($request->user(), $request->toDTO());
        } catch (ApplicationException $e) {
            return redirect()->back()->withErrors(['plan' => $e->getMessage()])->withInput();
        }

        return redirect()->route('leagues.show', $league->id);
    }

    public function join(JoinLeagueRequest $request)
    {
        try {
            $league = $this->joinLeagueUseCase->execute($request->user(), $request->inviteCode());
        } catch (ApplicationException $e) {
            return redirect()->back()->withErrors(['invite_code' => $e->getMessage()]);
        }

        return redirect()->route('leagues.show', $league->id);
    }

    public function joinOfficial(JoinOfficialLeagueRequest $request)
    {
        try {
            $league = $this->joinLeagueUseCase->executeById($request->user(), $request->leagueId());
        } catch (ApplicationException $e) {
            return redirect()->back()->withErrors(['league' => $e->getMessage()]);
        }

        return redirect()->route('leagues.show', $league->id);
    }

    public function update(UpdateLeagueRequest $request, string $league)
    {
        try {
            $this->updateLeagueUseCase->execute($request->user(), $league, $request->validated());
        } catch (ApplicationException $e) {
            return redirect()->back()->withErrors(['settings' => $e->getMessage()]);
        }

        return redirect()->back();
    }
}
