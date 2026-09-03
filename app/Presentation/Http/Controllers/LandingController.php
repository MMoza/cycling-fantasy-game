<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controllers;

use App\Infrastructure\Persistence\Models\EditionModel;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LandingController
{
    public function index(Request $request): Response
    {
        $now = now();

        $ongoing = EditionModel::where('status', 'ongoing')
            ->with('competition')
            ->first();

        $upcoming = ! $ongoing ? EditionModel::where('status', 'upcoming')
            ->where('start_date', '>', $now)
            ->orderBy('start_date')
            ->with('competition')
            ->first() : null;

        return Inertia::render('Landing', [
            'auth' => [
                'user' => $request->user() ? [
                    'id' => $request->user()->id,
                    'name' => $request->user()->name,
                ] : null,
            ],
            'activeEdition' => $ongoing ? [
                'name' => $ongoing->competition->name.' '.$ongoing->year,
                'status' => 'ongoing',
            ] : null,
            'nextEdition' => $upcoming ? [
                'name' => $upcoming->competition->name.' '.$upcoming->year,
                'startDate' => $upcoming->start_date->toIso8601String(),
            ] : null,
        ]);
    }
}
