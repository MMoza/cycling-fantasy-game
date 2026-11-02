<?php

declare(strict_types=1);

use App\Infrastructure\Persistence\Models\LeagueModel;
use App\Infrastructure\Persistence\Models\PredictionModel;
use App\Infrastructure\Persistence\Models\RiderModel;
use App\Infrastructure\Persistence\Models\StageModel;
use App\Infrastructure\Persistence\Models\StageParticipantModel;
use App\Models\User;
use Database\Seeders\TestOpenPredictionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(TestOpenPredictionsSeeder::class);

    $this->user = User::where('email', 'carlos@test.com')->firstOrFail();
    $this->league = LeagueModel::where('name', 'Liga Abierta de Prueba')->firstOrFail();
    $this->stage = StageModel::where('edition_id', $this->league->edition_id)
        ->where('number', 1)
        ->firstOrFail();
});

test('creates an open competition with test riders', function () {
    $stages = StageModel::where('edition_id', $this->league->edition_id)->get();

    expect($stages)->toHaveCount(3);

    foreach ($stages as $stage) {
        expect($stage->status->value)->toBe('upcoming');
        expect($stage->scheduled_start->isFuture())->toBeTrue();
        expect(StageParticipantModel::where('stage_id', $stage->id)->count())->toBe(15);
    }

    expect(RiderModel::count())->toBe(15);
});

test('stage page renders an open form with every rider available', function () {
    $response = $this->actingAs($this->user)
        ->get(route('stages.show', [$this->league->id, $this->stage->id]));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Stages/Show')
        ->where('is_locked', false)
        ->where('is_finished', false)
        ->has('availableRiders', 15)
        ->has('stage_rules', 5),
    );
});

test('predictions can be saved while the stage is open', function () {
    $riders = RiderModel::orderBy('last_name')->orderBy('first_name')->limit(3)->get();

    $response = $this->actingAs($this->user)->post(
        route('predictions.store', [$this->league->id, $this->stage->id]),
        [
            'predictions' => [
                ['category' => 'stage_winner', 'value' => $riders[0]->id],
                ['category' => 'stage_second', 'value' => $riders[1]->id],
                ['category' => 'stage_third', 'value' => $riders[2]->id],
            ],
        ],
    );

    $response->assertSessionHasNoErrors();
    $this->assertDatabaseCount('predictions', 3);
    expect(PredictionModel::count())->toBe(3);
});

test('ai proposal works with the mock provider', function () {
    config(['services.gemini.api_key' => 'mock']);

    $response = $this->actingAs($this->user)
        ->postJson(route('predictions.ai.generate', [$this->league->id, $this->stage->id]));

    $response->assertOk();

    $predictions = $response->json('predictions');
    expect(array_keys($predictions))->toBe([
        'stage_winner',
        'stage_second',
        'stage_third',
        'stage_leader',
        'stage_combativo',
    ]);

    foreach ($predictions as $riderId) {
        expect(RiderModel::where('id', $riderId)->exists())->toBeTrue();
    }

    // La propuesta nunca se guarda automáticamente
    $this->assertDatabaseCount('predictions', 0);
});

test('seeder is idempotent', function () {
    $this->seed(TestOpenPredictionsSeeder::class);

    expect(StageModel::where('edition_id', $this->league->edition_id)->count())->toBe(3);
    expect(RiderModel::count())->toBe(15);
    expect(LeagueModel::where('name', 'Liga Abierta de Prueba')->count())->toBe(1);
    expect(StageParticipantModel::count())->toBe(45);
});
