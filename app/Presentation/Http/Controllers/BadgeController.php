<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controllers;

use App\Application\Services\BadgeService;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BadgeController extends Controller
{
    public function __construct(private readonly BadgeService $badgeService) {}

    /**
     * Vista de detalle de un badge de un usuario (niveles, fechas e histórico).
     */
    public function show(Request $request, string $user, string $badge): Response
    {
        $target = User::findOrFail($user);

        try {
            $detail = $this->badgeService->badgeDetail($target, $badge);
        } catch (ModelNotFoundException) {
            abort(404);
        }

        return Inertia::render('Badges/Show', $detail->toArray());
    }
}
