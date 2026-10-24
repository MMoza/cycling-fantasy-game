<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controllers;

use App\Domain\Interfaces\MediaUrlResolverInterface;
use App\Infrastructure\Persistence\Models\EditionModel;
use App\Infrastructure\Persistence\Models\StageModel;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LandingController
{
    public function __construct(private readonly MediaUrlResolverInterface $mediaUrls) {}

    public function index(Request $request): Response
    {
        $now = now();
        $currentYear = (int) $now->format('Y');

        // All editions for current year, with competition + country + official league
        $editions = EditionModel::with([
            'competition.country',
            'leagues' => fn ($q) => $q->where('is_official', true),
        ])
            ->where('year', $currentYear)
            ->whereHas('competition', fn ($q) => $q->where('active', true))
            ->orderBy('start_date')
            ->get();

        // Find the current stage for ongoing editions
        $ongoingEdition = $editions->firstWhere('status', 'ongoing');

        $currentStage = null;
        if ($ongoingEdition) {
            $stage = StageModel::where('edition_id', $ongoingEdition->id)
                ->where('status', 'ongoing')
                ->orWhere(function ($q) use ($ongoingEdition, $now) {
                    $q->where('edition_id', $ongoingEdition->id)
                        ->where('date', '<=', $now->toDateString())
                        ->orderByDesc('date')
                        ->limit(1);
                })
                ->orderBy('number', 'desc')
                ->first();

            if ($stage) {
                $currentStage = [
                    'number' => $stage->number,
                    'name' => $stage->name,
                    'distance' => $stage->distance,
                    'date' => $stage->date->toDateString(),
                ];
            }
        }

        $races = $editions->map(fn (EditionModel $edition) => [
            'editionId' => $edition->id,
            'name' => $edition->competition->name,
            'year' => $edition->year,
            'status' => $edition->status->value,
            'startDate' => $edition->start_date->toIso8601String(),
            'endDate' => $edition->end_date->toIso8601String(),
            'countryId' => $edition->competition->country_id,
            'countryName' => $edition->competition?->country?->name,
            'logoImageUrl' => $this->mediaUrls->resolve($edition->competition->logo_image),
            'coverImageUrl' => $this->mediaUrls->resolve($edition->competition->cover_image),
            'officialLeagueId' => $edition->leagues->first()?->id,
        ])->values()->all();

        $activeRace = $editions->firstWhere('status', 'ongoing')
            ?? $editions->firstWhere('status', 'upcoming');

        return Inertia::render('Landing', [
            'auth' => [
                'user' => $request->user() ? [
                    'id' => $request->user()->id,
                    'name' => $request->user()->name,
                ] : null,
            ],
            'seasonRaces' => $races,
            'activeRaceId' => $activeRace?->id,
            'currentStage' => $currentStage,
        ]);
    }
}
