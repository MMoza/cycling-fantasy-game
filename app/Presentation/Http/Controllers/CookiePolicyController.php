<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;

class CookiePolicyController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('CookiePolicy');
    }
}
