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

beforeEach(function () {
    $this->user = User::factory()->create();

    $competition = CompetitionModel::create([
        'id' => Str::uuid()->toString(),
        'name' => 'Tour de Francia',
        'type' => CompetitionType::GrandTour,
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

    $scoringSystem = ScoringSystemModel::create([
        'id' => Str::uuid()->toString(),
        'name' => 'Estándar',
        'type' => ScoringSystemType::Standard,
        'description' => 'Puntuación equilibrada',
    ]);

    $this->league = LeagueModel::create([
        'id' => Str::uuid()->toString(),
        'name' => 'Amigos del Tour',
        'edition_id' => $this->edition->id,
        'scoring_system_id' => $scoringSystem->id,
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
});

test('guest cannot save predictions', function () {
    $response = $this->post(route('predictions.store', [$this->league->id, $this->stage->id]), [
        'predictions' => [
            ['category' => 'stage_winner', 'value' => 'Rider A'],
        ],
    ]);

    $response->assertRedirect(route('login'));
});

test('user not in league cannot save predictions', function () {
    $otherUser = User::factory()->create();

    $response = $this->actingAs($otherUser)->post(route('predictions.store', [$this->league->id, $this->stage->id]), [
        'predictions' => [
            ['category' => 'stage_winner', 'value' => 'Rider A'],
        ],
    ]);

    $response->assertNotFound();
});

test('user can save predictions for unlocked stage', function () {
    $response = $this->actingAs($this->user)->post(route('predictions.store', [$this->league->id, $this->stage->id]), [
        'predictions' => [
            ['category' => 'stage_winner', 'value' => 'Rider A'],
            ['category' => 'stage_second', 'value' => 'Rider B'],
            ['category' => 'stage_third', 'value' => 'Rider C'],
            ['category' => 'stage_leader', 'value' => 'Rider A'],
            ['category' => 'stage_combativo', 'value' => 'Rider D'],
        ],
    ]);

    $response->assertRedirect();

    expect(PredictionModel::where('league_id', $this->league->id)
        ->where('stage_id', $this->stage->id)
        ->where('user_id', $this->user->id)
        ->count()
    )->toBe(5);

    $this->assertDatabaseHas('predictions', [
        'league_id' => $this->league->id,
        'stage_id' => $this->stage->id,
        'user_id' => $this->user->id,
        'category' => 'stage_winner',
        'type' => 'pre_stage',
    ]);
});

test('user can update existing predictions', function () {
    PredictionModel::create([
        'id' => Str::uuid()->toString(),
        'user_id' => $this->user->id,
        'league_id' => $this->league->id,
        'stage_id' => $this->stage->id,
        'type' => 'pre_stage',
        'category' => 'stage_winner',
        'prediction_value' => 'Old Rider',
    ]);

    $response = $this->actingAs($this->user)->post(route('predictions.store', [$this->league->id, $this->stage->id]), [
        'predictions' => [
            ['category' => 'stage_winner', 'value' => 'New Rider'],
        ],
    ]);

    $response->assertRedirect();

    $this->assertDatabaseHas('predictions', [
        'league_id' => $this->league->id,
        'stage_id' => $this->stage->id,
        'user_id' => $this->user->id,
        'category' => 'stage_winner',
        'prediction_value' => '{"rider_id":"New Rider"}',
    ]);

    expect(PredictionModel::where('league_id', $this->league->id)
        ->where('stage_id', $this->stage->id)
        ->where('user_id', $this->user->id)
        ->where('category', 'stage_winner')
        ->count()
    )->toBe(1);
});

test('user cannot save predictions for locked stage', function () {
    StageModel::where('id', $this->stage->id)->update([
        'scheduled_start' => now()->subHour(),
    ]);

    $response = $this->actingAs($this->user)->post(route('predictions.store', [$this->league->id, $this->stage->id]), [
        'predictions' => [
            ['category' => 'stage_winner', 'value' => 'Rider A'],
        ],
    ]);

    $response->assertRedirect();
    $response->assertSessionHasErrors();

    expect(PredictionModel::where('league_id', $this->league->id)
        ->where('stage_id', $this->stage->id)
        ->count()
    )->toBe(0);
});

test('validates required prediction categories', function () {
    $response = $this->actingAs($this->user)->post(route('predictions.store', [$this->league->id, $this->stage->id]), [
        'predictions' => [
            ['category' => 'invalid_category', 'value' => 'Rider A'],
        ],
    ]);

    $response->assertSessionHasErrors();
});

test('validates predictions is required', function () {
    $response = $this->actingAs($this->user)->post(route('predictions.store', [$this->league->id, $this->stage->id]), []);

    $response->assertSessionHasErrors('predictions');
});
