<?php

declare(strict_types=1);

use App\Domain\ValueObjects\CompetitionType;
use App\Domain\ValueObjects\EditionStatus;
use App\Domain\ValueObjects\ScoringSystemType;
use App\Domain\ValueObjects\StageStatus;
use App\Domain\ValueObjects\StageType;
use App\Infrastructure\Persistence\Models\CompetitionModel;
use App\Infrastructure\Persistence\Models\EditionModel;
use App\Infrastructure\Persistence\Models\LeagueModel;
use App\Infrastructure\Persistence\Models\PredictionModel;
use App\Infrastructure\Persistence\Models\ScoringSystemModel;
use App\Infrastructure\Persistence\Models\StageModel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

function predictionFlashError(string $key): string
{
    $errors = session('errors');

    if (is_object($errors)) {
        return (string) $errors->first($key);
    }

    return (string) data_get($errors, "default.messages.{$key}.0", '');
}

beforeEach(function () {
    $this->user = User::factory()->create();

    $competition = CompetitionModel::create([
        'id' => Str::uuid()->toString(),
        'name' => 'Tour de Francia',
        'type' => CompetitionType::GC,
        'country_id' => createCountry(),
        'active' => true,
    ]);

    $this->edition = EditionModel::create([
        'id' => Str::uuid()->toString(),
        'competition_id' => $competition->id,
        'year' => 2026,
        'start_date' => '2026-07-01',
        'end_date' => '2026-07-23',
        'status' => EditionStatus::Upcoming,
    ]);

    $this->scoringSystem = ScoringSystemModel::create([
        'id' => Str::uuid()->toString(),
        'name' => 'Estándar',
        'type' => ScoringSystemType::Standard,
        'description' => 'Puntuación equilibrada',
    ]);

    $this->league = LeagueModel::create([
        'id' => Str::uuid()->toString(),
        'name' => 'Amigos del Tour',
        'edition_id' => $this->edition->id,
        'scoring_system_id' => $this->scoringSystem->id,
        'owner_id' => $this->user->id,
        'invite_code' => Str::random(8),
        'is_public' => false,
    ]);

    $this->league->users()->attach($this->user->id, [
        'id' => Str::uuid()->toString(),
        'role' => 'owner',
    ]);

    $this->stage = StageModel::create([
        'id' => Str::uuid()->toString(),
        'edition_id' => $this->edition->id,
        'number' => 1,
        'name' => 'Etapa 1',
        'date' => '2026-07-01',
        'scheduled_start' => now()->addDay(),
        'type' => StageType::Flat,
        'distance' => 180.5,
        'origin' => 'Lille',
        'destination' => 'Paris',
        'status' => StageStatus::Upcoming,
    ]);

    $this->team = createTestTeam('Team Test', 'TST');
    $this->riders = [
        createTestRider('Rider', 'A'),
        createTestRider('Rider', 'B'),
        createTestRider('Rider', 'C'),
    ];

    foreach ($this->riders as $rider) {
        createTestParticipant($this->edition->competition_id, $this->edition->id, $this->team->id, $rider->id);
    }
});

test('allows the same rider in stage winner and stage leader', function () {
    $response = $this->actingAs($this->user)->post(route('predictions.store', [$this->league->id, $this->stage->id]), [
        'predictions' => [
            ['category' => 'stage_winner', 'value' => $this->riders[0]->id],
            ['category' => 'stage_leader', 'value' => $this->riders[0]->id],
            ['category' => 'stage_combativo', 'value' => $this->riders[0]->id],
        ],
    ]);

    $response->assertRedirect();
    $response->assertSessionHasNoErrors();
    $this->assertDatabaseCount('predictions', 3);
});

test('rejects the same rider in two podium positions', function () {
    $response = $this->actingAs($this->user)->post(route('predictions.store', [$this->league->id, $this->stage->id]), [
        'predictions' => [
            ['category' => 'stage_winner', 'value' => $this->riders[0]->id],
            ['category' => 'stage_second', 'value' => $this->riders[0]->id],
            ['category' => 'stage_third', 'value' => $this->riders[1]->id],
        ],
    ]);

    $response->assertRedirect();
    $response->assertSessionHasErrors('stage');
    $this->assertDatabaseCount('predictions', 0);
});

test('duplicate error names the rider and the positions', function () {
    $response = $this->actingAs($this->user)->post(route('predictions.store', [$this->league->id, $this->stage->id]), [
        'predictions' => [
            ['category' => 'stage_winner', 'value' => $this->riders[0]->id],
            ['category' => 'stage_second', 'value' => $this->riders[0]->id],
        ],
    ]);

    $message = predictionFlashError('stage');

    expect($message)->toContain('A Rider');
    expect($message)->toContain('está seleccionado en varias posiciones');
    expect($message)->toContain('Ganador de etapa');
    expect($message)->toContain('2º clasificado etapa');
    expect($message)->toContain('Corrige las posiciones antes de guardar');
});

test('rejects the same rider in exact position categories', function () {
    $response = $this->actingAs($this->user)->post(route('predictions.store', [$this->league->id, $this->stage->id]), [
        'predictions' => [
            ['category' => 'stage_position_1', 'value' => $this->riders[1]->id],
            ['category' => 'stage_position_2', 'value' => $this->riders[1]->id],
        ],
    ]);

    $response->assertRedirect();
    $response->assertSessionHasErrors('stage');
    $this->assertDatabaseCount('predictions', 0);
});

test('reports every duplicated rider', function () {
    $response = $this->actingAs($this->user)->post(route('predictions.store', [$this->league->id, $this->stage->id]), [
        'predictions' => [
            ['category' => 'stage_winner', 'value' => $this->riders[0]->id],
            ['category' => 'stage_second', 'value' => $this->riders[0]->id],
            ['category' => 'stage_third', 'value' => $this->riders[1]->id],
            ['category' => 'stage_position_4', 'value' => $this->riders[1]->id],
        ],
    ]);

    $message = predictionFlashError('stage');

    expect($message)->toContain('A Rider');
    expect($message)->toContain('B Rider');
    $this->assertDatabaseCount('predictions', 0);
});

test('does not overwrite previous predictions when duplicates are rejected', function () {
    $this->actingAs($this->user)->post(route('predictions.store', [$this->league->id, $this->stage->id]), [
        'predictions' => [
            ['category' => 'stage_winner', 'value' => $this->riders[0]->id],
        ],
    ])->assertSessionHasNoErrors();

    $response = $this->actingAs($this->user)->post(route('predictions.store', [$this->league->id, $this->stage->id]), [
        'predictions' => [
            ['category' => 'stage_winner', 'value' => $this->riders[1]->id],
            ['category' => 'stage_second', 'value' => $this->riders[1]->id],
        ],
    ]);

    $response->assertSessionHasErrors('stage');
    $this->assertDatabaseCount('predictions', 1);

    $prediction = PredictionModel::where('category', 'stage_winner')->first();
    expect($prediction->prediction_value)->toBe(['rider_id' => $this->riders[0]->id]);
});

test('rejects duplicated teams in team time trial', function () {
    $tttStage = StageModel::create([
        'id' => Str::uuid()->toString(),
        'edition_id' => $this->edition->id,
        'number' => 2,
        'name' => 'CRE por equipos',
        'date' => '2026-07-02',
        'scheduled_start' => now()->addDay(),
        'type' => StageType::TeamTimeTrial,
        'distance' => 25.0,
        'origin' => 'Rouen',
        'destination' => 'Rouen',
        'status' => StageStatus::Upcoming,
    ]);

    $response = $this->actingAs($this->user)->post(route('predictions.store', [$this->league->id, $tttStage->id]), [
        'predictions' => [
            ['category' => 'stage_winner', 'value' => $this->team->id],
            ['category' => 'stage_second', 'value' => $this->team->id],
        ],
    ]);

    $response->assertRedirect();
    $response->assertSessionHasErrors('stage');

    $message = predictionFlashError('stage');
    expect($message)->toContain('Team Test');

    $this->assertDatabaseCount('predictions', 0);
});

test('accepts different riders in every position', function () {
    $response = $this->actingAs($this->user)->post(route('predictions.store', [$this->league->id, $this->stage->id]), [
        'predictions' => [
            ['category' => 'stage_winner', 'value' => $this->riders[0]->id],
            ['category' => 'stage_second', 'value' => $this->riders[1]->id],
            ['category' => 'stage_third', 'value' => $this->riders[2]->id],
            ['category' => 'stage_leader', 'value' => $this->riders[0]->id],
        ],
    ]);

    $response->assertRedirect();
    $response->assertSessionHasNoErrors();
    $this->assertDatabaseCount('predictions', 4);
    expect(PredictionModel::count())->toBe(4);
});
