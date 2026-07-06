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
use App\Infrastructure\Persistence\Models\ScoringSystemModel;
use App\Infrastructure\Persistence\Models\StageModel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

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

    $this->stage1 = StageModel::create([
        'id' => Str::uuid()->toString(),
        'edition_id' => $this->edition->id,
        'number' => 1,
        'name' => 'Etapa 1',
        'date' => '2026-07-01',
        'scheduled_start' => '2026-07-01 11:00:00',
        'type' => StageType::Flat,
        'distance' => 180.5,
        'origin' => 'Lille',
        'destination' => 'Paris',
        'status' => StageStatus::Finished,
    ]);

    $this->stage2 = StageModel::create([
        'id' => Str::uuid()->toString(),
        'edition_id' => $this->edition->id,
        'number' => 2,
        'name' => 'Etapa 2',
        'date' => '2099-07-02',
        'scheduled_start' => '2099-07-02 12:00:00',
        'type' => StageType::Mountain,
        'distance' => 200.0,
        'origin' => 'Paris',
        'destination' => 'Lyon',
        'status' => StageStatus::Upcoming,
    ]);
});

test('guest cannot access stage page', function () {
    $response = $this->get(route('stages.index', $this->league->id));
    $response->assertRedirect(route('login'));
});

test('user not in league cannot access stage page', function () {
    $otherUser = User::factory()->create();
    $response = $this->actingAs($otherUser)->get(route('stages.index', $this->league->id));
    $response->assertNotFound();
});

test('stage index renders all stages', function () {
    $response = $this->actingAs($this->user)->get(route('stages.index', $this->league->id));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Stages/Index')
        ->where('league_id', $this->league->id)
        ->has('stages', 2)
        ->where('stages.0.id', $this->stage1->id)
        ->where('stages.0.status', 'finished')
        ->where('stages.1.id', $this->stage2->id)
        ->where('stages.1.status', 'upcoming')
        ->has('predictionsPerStage')
        ->has('pointsPerStage')
    );
});

test('stage index renders empty when no stages', function () {
    StageModel::truncate();

    $response = $this->actingAs($this->user)->get(route('stages.index', $this->league->id));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Stages/Index')
        ->where('stages', [])
    );
});

test('stage index shows points for finished stages', function () {
    $response = $this->actingAs($this->user)->get(route('stages.index', $this->league->id));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Stages/Index')
        ->where('stages.0.id', $this->stage1->id)
        ->where('stages.1.id', $this->stage2->id)
    );
});

test('stage show renders component with stage data', function () {
    $response = $this->actingAs($this->user)->get(route('stages.show', [$this->league->id, $this->stage2->id]));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Stages/Show')
        ->where('stage.number', 2)
        ->where('stage.origin', 'Paris')
        ->where('stage.destination', 'Lyon')
        ->where('is_locked', false)
        ->where('is_finished', false)
        ->where('stage_results', [])
        ->where('stage_classification', [])
        ->has('predictions')
        ->has('navigation')
        ->has('all_stages')
    );
});

test('stage show shows navigation between stages', function () {
    $response = $this->actingAs($this->user)->get(route('stages.show', [$this->league->id, $this->stage2->id]));

    $response->assertInertia(fn ($page) => $page
        ->where('navigation.prev.id', $this->stage1->id)
        ->where('navigation.prev.number', 1)
        ->where('navigation.next', null)
    );
});

test('stage show shows locked when scheduled_start has passed', function () {
    StageModel::where('id', $this->stage2->id)->update([
        'scheduled_start' => now()->subHour(),
    ]);

    $response = $this->actingAs($this->user)->get(route('stages.show', [$this->league->id, $this->stage2->id]));

    $response->assertInertia(fn ($page) => $page
        ->where('is_locked', true)
    );
});

test('stage show shows results and classification when finished', function () {
    StageModel::where('id', $this->stage2->id)->update(['status' => 'finished']);

    $riderId = Str::uuid()->toString();
    DB::table('riders')->insert([
        'id' => $riderId,
        'first_name' => 'Test',
        'last_name' => 'Rider',
        'country_id' => createCountry('ES', 'España'),
    ]);

    DB::table('stage_results')->insert([
        'id' => Str::uuid()->toString(),
        'stage_id' => $this->stage2->id,
        'rider_id' => $riderId,
        'position' => 1,
        'time' => '4:30:00',
        'gap' => null,
    ]);

    DB::table('score_events')->insert([
        'id' => Str::uuid()->toString(),
        'user_id' => $this->user->id,
        'league_id' => $this->league->id,
        'scoring_rule_id' => Str::uuid()->toString(),
        'points' => 30,
        'description' => 'Test score',
        'context' => 'stage_winner',
        'stage_id' => $this->stage2->id,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $response = $this->actingAs($this->user)->get(route('stages.show', [$this->league->id, $this->stage2->id]));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Stages/Show')
        ->where('is_finished', true)
        ->has('stage_results', 1)
        ->where('stage_results.0.rider_name', 'Rider Test')
        ->where('stage_results.0.time', '4:30:00')
        ->has('stage_classification', 1)
        ->where('stage_classification.0.user_id', $this->user->id)
        ->where('stage_classification.0.total_points', 30)
    );
});
