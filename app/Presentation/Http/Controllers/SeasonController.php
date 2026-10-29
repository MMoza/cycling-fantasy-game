<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controllers;

use App\Application\DTOs\JoinAllSeasonResultDTO;
use App\Application\UseCases\Season\JoinAllMissingCompetitionsUseCase;
use App\Application\UseCases\Season\ShowSeasonClassificationUseCase;
use App\Application\UseCases\Season\ShowSeasonUseCase;
use App\Presentation\Http\Requests\Season\JoinAllSeasonRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SeasonController extends Controller
{
    public function __construct(
        private readonly ShowSeasonUseCase $showSeasonUseCase,
        private readonly ShowSeasonClassificationUseCase $showSeasonClassificationUseCase,
        private readonly JoinAllMissingCompetitionsUseCase $joinAllMissingCompetitionsUseCase,
    ) {}

    public function index(Request $request, ?string $year = null): Response
    {
        return Inertia::render('Season/Index', $this->showSeasonUseCase->execute(
            $request->user(),
            $year !== null ? (int) $year : null,
        )->toArray());
    }

    public function joinAll(JoinAllSeasonRequest $request): RedirectResponse
    {
        $result = $this->joinAllMissingCompetitionsUseCase->execute($request->user(), $request->year());

        if ($result->error !== null) {
            Inertia::flash('error', $this->partialJoinMessage($result));
        } elseif ($result->joined > 0) {
            Inertia::flash('success', $this->joinMessage($result->joined));
        } else {
            Inertia::flash('success', 'Ya estás inscrito en todas las competiciones disponibles.');
        }

        return redirect()->back();
    }

    private function partialJoinMessage(JoinAllSeasonResultDTO $result): string
    {
        $message = $result->error;

        if ($result->joined > 0) {
            $message = "{$this->joinMessage($result->joined)} {$message}";
        }

        return $message;
    }

    private function joinMessage(int $joined): string
    {
        return $joined === 1
            ? 'Te has unido a 1 competición.'
            : "Te has unido a {$joined} competiciones.";
    }

    public function classification(Request $request): Response
    {
        return Inertia::render('Season/Classification', $this->showSeasonClassificationUseCase->execute($request->user())->toArray());
    }
}
