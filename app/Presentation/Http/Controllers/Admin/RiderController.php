<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controllers\Admin;

use App\Infrastructure\Persistence\Models\CountryModel;
use App\Infrastructure\Persistence\Models\RiderModel;
use App\Presentation\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class RiderController extends Controller
{
    public function index(): Response
    {
        $riders = RiderModel::with('country')
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get()
            ->map(fn ($r) => [
                'id' => $r->id,
                'first_name' => $r->first_name,
                'last_name' => $r->last_name,
                'full_name' => $r->full_name,
                'country_id' => $r->country_id,
                'profile_image' => $r->profile_image,
                'age' => $r->age,
            ]);

        $countries = CountryModel::orderBy('name')
            ->get(['id', 'name'])
            ->map(fn ($c) => ['value' => $c->id, 'label' => $c->name]);

        return Inertia::render('Admin/Riders/Index', [
            'riders' => $riders,
            'countries' => $countries,
        ]);
    }

    public function create(): Response
    {
        $countries = CountryModel::orderBy('name')
            ->get(['id', 'name'])
            ->map(fn ($c) => ['value' => $c->id, 'label' => $c->name]);

        return Inertia::render('Admin/Riders/Form', [
            'rider' => null,
            'countries' => $countries,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'country_id' => 'nullable|string|size:2|exists:countries,id',
            'birth_date' => 'nullable|date',
            'profile_image' => 'nullable|string|max:255',
        ]);

        RiderModel::create($validated);

        return redirect()->route('admin.riders.index');
    }

    public function edit(string $id): Response
    {
        $rider = RiderModel::findOrFail($id);

        $countries = CountryModel::orderBy('name')
            ->get(['id', 'name'])
            ->map(fn ($c) => ['value' => $c->id, 'label' => $c->name]);

        return Inertia::render('Admin/Riders/Form', [
            'rider' => [
                'id' => $rider->id,
                'first_name' => $rider->first_name,
                'last_name' => $rider->last_name,
                'country_id' => $rider->country_id,
                'birth_date' => $rider->birth_date?->format('Y-m-d'),
                'profile_image' => $rider->profile_image,
            ],
            'countries' => $countries,
        ]);
    }

    public function update(Request $request, string $id): RedirectResponse
    {
        $rider = RiderModel::findOrFail($id);

        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'country_id' => 'nullable|string|size:2|exists:countries,id',
            'birth_date' => 'nullable|date',
            'profile_image' => 'nullable|string|max:255',
        ]);

        $rider->update($validated);

        return redirect()->route('admin.riders.index');
    }
}
