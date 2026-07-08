<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controllers;

use App\Application\UseCases\Rider\ShowRiderUseCase;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RiderController extends Controller
{
    public function __construct(
        private readonly ShowRiderUseCase $showRiderUseCase,
    ) {}

    public function show(Request $request, string $league, string $rider)
    {
        $data = $this->showRiderUseCase->execute($league, $rider);

        return Inertia::render('Riders/Show', $data);
    }
}
