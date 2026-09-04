<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SeasonClassificationController extends Controller
{
    public function index(Request $request): Response
    {
        $year = (int) date('Y');

        return Inertia::render('SeasonClassification', [
            'year' => $year,
        ]);
    }
}
