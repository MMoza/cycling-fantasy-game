<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controllers\Admin;

use App\Infrastructure\Persistence\Models\RiderModel;
use App\Presentation\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Str;

class RiderController extends Controller
{
    public function index(): Response
    {
        $riders = RiderModel::orderBy('name')
            ->get()
            ->map(fn ($r) => [
                'id' => $r->id,
                'name' => $r->name,
                'nationality' => $r->nationality,
                'birth_date' => $r->birth_date?->format('Y-m-d'),
            ]);

        return Inertia::render('Admin/Riders/Index', [
            'riders' => $riders,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Riders/Form', [
            'rider' => null,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'nationality' => 'nullable|string|max:255',
            'birth_date' => 'nullable|date',
        ]);

        RiderModel::create([
            'id' => Str::uuid()->toString(),
            ...$validated,
        ]);

        return redirect()->route('admin.riders.index');
    }

    public function edit(string $id): Response
    {
        $rider = RiderModel::findOrFail($id);

        return Inertia::render('Admin/Riders/Form', [
            'rider' => [
                'id' => $rider->id,
                'name' => $rider->name,
                'nationality' => $rider->nationality,
                'birth_date' => $rider->birth_date?->format('Y-m-d'),
            ],
        ]);
    }

    public function update(Request $request, string $id): RedirectResponse
    {
        $rider = RiderModel::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'nationality' => 'nullable|string|max:255',
            'birth_date' => 'nullable|date',
        ]);

        $rider->update($validated);

        return redirect()->route('admin.riders.index');
    }
}
