<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LandingController
{
    public function index(Request $request): Response
    {
        return Inertia::render('Landing');
    }
}
