<?php

declare(strict_types=1);

use App\Domain\ValueObjects\CompetitionType;
use App\Domain\ValueObjects\EditionStatus;
use App\Domain\ValueObjects\ScoringSystemType;
use App\Infrastructure\Persistence\Models\CompetitionModel;
use App\Infrastructure\Persistence\Models\EditionModel;
use App\Infrastructure\Persistence\Models\LeagueModel;
use App\Infrastructure\Persistence\Models\ScoreEventModel;
use App\Infrastructure\Persistence\Models\ScoringSystemModel;
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
        'max_players' => 20,
        'is_public' => false,
    ]);

    $this->league->users()->attach($this->user->id, [
        'id' => Str::uuid()->toString(),
        'role' => 'owner',
    ]);
});

test('guest cannot access classification page', function () {
    $response = $this->get(route('classification.index', $this->league->id));
    $response->assertRedirect(route('login'));
});

test('user not in league cannot access classification page', function () {
    $otherUser = User::factory()->create();
    $response = $this->actingAs($otherUser)->get(route('classification.index', $this->league->id));
    $response->assertNotFound();
});

test('classification renders all members even when no scores', function () {
    $response = $this->actingAs($this->user)->get(route('classification.index', $this->league->id));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Classification/Index')
        ->where('league_name', 'Amigos del Tour')
        ->has('general_leaderboard', 1)
        ->where('user_position.rank', 1)
        ->where('user_position.points', 0)
    );
});

test('classification shows scores when they exist', function () {
    $otherUser = User::factory()->create();
    $this->league->users()->attach($otherUser->id, [
        'id' => Str::uuid()->toString(),
        'role' => 'member',
    ]);

    ScoreEventModel::create([
        'id' => Str::uuid()->toString(),
        'user_id' => $this->user->id,
        'league_id' => $this->league->id,
        'scoring_rule_id' => Str::uuid()->toString(),
        'points' => 50,
        'description' => 'Ganador etapa 1',
        'context' => 'stage_1',
    ]);

    ScoreEventModel::create([
        'id' => Str::uuid()->toString(),
        'user_id' => $otherUser->id,
        'league_id' => $this->league->id,
        'scoring_rule_id' => Str::uuid()->toString(),
        'points' => 100,
        'description' => 'Amarillo etapa 1',
        'context' => 'stage_1',
    ]);

    $response = $this->actingAs($this->user)->get(route('classification.index', $this->league->id));

    $response->assertInertia(fn ($page) => $page
        ->has('general_leaderboard', 2)
        ->where('general_leaderboard.0.user_name', $otherUser->name)
        ->where('general_leaderboard.0.points', 100)
        ->where('general_leaderboard.0.rank', 1)
        ->where('general_leaderboard.0.is_current_user', false)
        ->where('general_leaderboard.1.user_name', $this->user->name)
        ->where('general_leaderboard.1.points', 50)
        ->where('general_leaderboard.1.rank', 2)
        ->where('general_leaderboard.1.is_current_user', true)
        ->where('general_leaderboard.1.behind_leader', 50)
        ->where('user_position.rank', 2)
        ->where('user_position.points', 50)
    );
});

test('classification highlights current user', function () {
    ScoreEventModel::create([
        'id' => Str::uuid()->toString(),
        'user_id' => $this->user->id,
        'league_id' => $this->league->id,
        'scoring_rule_id' => Str::uuid()->toString(),
        'points' => 50,
        'description' => 'Ganador etapa 1',
        'context' => 'stage_1',
    ]);

    $response = $this->actingAs($this->user)->get(route('classification.index', $this->league->id));

    $response->assertInertia(fn ($page) => $page
        ->has('general_leaderboard', 1)
        ->where('general_leaderboard.0.is_current_user', true)
        ->where('general_leaderboard.0.rank', 1)
        ->where('general_leaderboard.0.behind_leader', 0)
    );
});
