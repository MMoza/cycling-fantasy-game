<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controllers;

use App\Infrastructure\Persistence\Models\EditionModel;
use App\Infrastructure\Persistence\Models\StageModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class LandingController
{
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
            'logoImageUrl' => $this->resolveS3Url($edition->competition->logo_image),
            'coverImageUrl' => $this->resolveS3Url($edition->competition->cover_image),
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

    private function resolveS3Url(?string $path): ?string
    {
        if ($path === null) {
            return null;
        }

        $disk = Storage::disk('s3');

        try {
            return $disk->temporaryUrl($path, now()->addHours(24));
        } catch (\Exception) {
            // fall through
        }

        try {
            return $disk->url($path);
        } catch (\Exception) {
            // fall through
        }

        $endpoint = rtrim(config('filesystems.disks.s3.endpoint', ''), '/');
        $bucket = config('filesystems.disks.s3.bucket', '');

        if ($endpoint && $bucket) {
            return "{$endpoint}/{$bucket}/".ltrim($path, '/');
        }

        return null;
    }
}
