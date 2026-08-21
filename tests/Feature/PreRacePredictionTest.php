<?php

declare(strict_types=1);

use App\Domain\ValueObjects\CompetitionType;
use App\Domain\ValueObjects\EditionStatus;
use App\Domain\ValueObjects\ScoringSystemType;
use App\Infrastructure\Persistence\Models\CompetitionModel;
use App\Infrastructure\Persistence\Models\EditionModel;
use App\Infrastructure\Persistence\Models\LeagueModel;
use App\Infrastructure\Persistence\Models\PredictionModel;
use App\Infrastructure\Persistence\Models\ScoringSystemModel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();

    $this->competition = CompetitionModel::create([
        'id' => Str::uuid()->toString(),
        'name' => 'Tour de Francia',
        'type' => CompetitionType::GC,
        'country_id' => createCountry(),
        'active' => true,
    ]);

    $this->edition = EditionModel::create([
        'id' => Str::uuid()->toString(),
        'competition_id' => $this->competition->id,
        'year' => 2026,
        'start_date' => now()->addMonth(),
        'end_date' => now()->addMonth()->addDays(21),
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

    $this->team = createTestTeam('Team Test', 'TST');
    $this->riders = [
        createTestRider('Rider', 'A'),
        createTestRider('Rider', 'B'),
        createTestRider('Rider', 'C'),
        createTestRider('Rider', 'D'),
        createTestRider('Rider', 'E'),
    ];

    foreach ($this->riders as $rider) {
        createTestParticipant($this->competition->id, $this->edition->id, $this->team->id, $rider->id);
    }
});

test('guest cannot access pre-race page', function () {
    $response = $this->get(route('predictions.pre-race', $this->league->id));
    $response->assertRedirect(route('login'));
});

test('user not in league cannot access pre-race page', function () {
    $otherUser = User::factory()->create();
    $response = $this->actingAs($otherUser)->get(route('predictions.pre-race', $this->league->id));
    $response->assertNotFound();
});

test('pre-race page renders component', function () {
    $response = $this->actingAs($this->user)->get(route('predictions.pre-race', $this->league->id));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Predictions/PreRace')
        ->where('league_name', 'Amigos del Tour')
        ->where('is_locked', false)
        ->has('predictions')
    );
});

test('user can save pre-race predictions', function () {
    $riderIds = array_map(fn ($r) => $r->id, $this->riders);

    $response = $this->actingAs($this->user)->post(route('predictions.pre-race.store', $this->league->id), [
        'predictions' => [
            ['category' => 'gc_top_5', 'value' => implode(',', $riderIds)],
            ['category' => 'points_winner', 'value' => $riderIds[0]],
            ['category' => 'mountains_winner', 'value' => $riderIds[1]],
            ['category' => 'youth_winner', 'value' => $riderIds[2]],
            ['category' => 'teams_winner', 'value' => $this->team->id],
            ['category' => 'super_combativo', 'value' => $riderIds[3]],
        ],
    ]);

    $response->assertRedirect();

    expect(PredictionModel::where('league_id', $this->league->id)
        ->whereNull('stage_id')
        ->where('user_id', $this->user->id)
        ->count()
    )->toBe(6);
});

test('pre-race page shows existing predictions', function () {
    PredictionModel::create([
        'id' => Str::uuid()->toString(),
        'user_id' => $this->user->id,
        'league_id' => $this->league->id,
        'stage_id' => null,
        'type' => 'pre_race',
        'category' => 'gc_top_5',
        'prediction_value' => 'Rider A, Rider B, Rider C, Rider D, Rider E',
    ]);

    $response = $this->actingAs($this->user)->get(route('predictions.pre-race', $this->league->id));

    $response->assertInertia(fn ($page) => $page
        ->has('predictions', 1)
    );
});

test('user cannot save pre-race predictions after edition started', function () {
    EditionModel::where('id', $this->edition->id)->update([
        'status' => EditionStatus::Ongoing,
    ]);

    $response = $this->actingAs($this->user)->post(route('predictions.pre-race.store', $this->league->id), [
        'predictions' => [
            ['category' => 'gc_top_5', 'value' => $this->riders[0]->id],
        ],
    ]);

    $response->assertSessionHasErrors('race');

    expect(PredictionModel::where('league_id', $this->league->id)->count())->toBe(0);
});

test('pre-race page shows locked when edition has started', function () {
    EditionModel::where('id', $this->edition->id)->update([
        'start_date' => now()->subDay(),
    ]);

    $response = $this->actingAs($this->user)->get(route('predictions.pre-race', $this->league->id));

    $response->assertInertia(fn ($page) => $page
        ->where('is_locked', true)
    );
});

test('validates pre-race prediction categories', function () {
    $response = $this->actingAs($this->user)->post(route('predictions.pre-race.store', $this->league->id), [
        'predictions' => [
            ['category' => 'invalid_category', 'value' => $this->riders[0]->id],
        ],
    ]);

    $response->assertSessionHasErrors();
});

test('rejects rider not in edition participants for pre-race', function () {
    $otherRider = createTestRider('Other', 'Rider');

    $response = $this->actingAs($this->user)->post(route('predictions.pre-race.store', $this->league->id), [
        'predictions' => [
            ['category' => 'super_combativo', 'value' => $otherRider->id],
        ],
    ]);

    $response->assertRedirect();
    $response->assertSessionHasErrors();

    expect(PredictionModel::where('league_id', $this->league->id)
        ->whereNull('stage_id')
        ->count()
    )->toBe(0);
});
