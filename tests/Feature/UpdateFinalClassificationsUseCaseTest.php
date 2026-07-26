<?php

declare(strict_types=1);

use App\Application\Exceptions\ApplicationException;
use App\Application\Services\ActivityLogService;
use App\Application\Services\PreRaceScoringService;
use App\Application\UseCases\Admin\FinalClassification\UpdateFinalClassificationsUseCase;
use App\Domain\ValueObjects\CompetitionType;
use App\Domain\ValueObjects\EditionStatus;
use App\Domain\ValueObjects\ScoringSystemType;
use App\Domain\ValueObjects\StageStatus;
use App\Domain\ValueObjects\StageType;
use App\Infrastructure\Persistence\Models\CompetitionModel;
use App\Infrastructure\Persistence\Models\EditionModel;
use App\Infrastructure\Persistence\Models\FinalClassificationModel;
use App\Infrastructure\Persistence\Models\LeagueModel;
use App\Infrastructure\Persistence\Models\PredictionModel;
use App\Infrastructure\Persistence\Models\ScoreEventModel;
use App\Infrastructure\Persistence\Models\ScoringRuleModel;
use App\Infrastructure\Persistence\Models\ScoringSystemModel;
use App\Infrastructure\Persistence\Models\StageModel;
use App\Infrastructure\Persistence\Models\TeamModel;
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
        'status' => EditionStatus::Ongoing,
    ]);

    $this->scoringSystem = ScoringSystemModel::create([
        'id' => Str::uuid()->toString(),
        'name' => 'Estándar',
        'type' => ScoringSystemType::Standard,
        'description' => 'Puntuación equilibrada',
    ]);

    ScoringRuleModel::create([
        'id' => Str::uuid()->toString(),
        'scoring_system_id' => $this->scoringSystem->id,
        'type' => 'super_combativo',
        'context' => 'pre_race',
        'points' => 30,
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

    $this->riderId = Str::uuid()->toString();

    DB::table('riders')->insert([
        'id' => $this->riderId,
        'first_name' => 'Test',
        'last_name' => 'Rider',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $this->teamId = Str::uuid()->toString();

    TeamModel::create([
        'id' => $this->teamId,
        'name' => 'Test Team',
        'uci_code' => 'TST',
    ]);

    $this->useCase = new UpdateFinalClassificationsUseCase(
        new PreRaceScoringService(new ActivityLogService),
    );
});

test('throws exception when stages are not finished', function () {
    StageModel::create([
        'id' => Str::uuid()->toString(),
        'edition_id' => $this->edition->id,
        'number' => 1,
        'name' => 'Etapa 1',
        'date' => '2026-07-01',
        'type' => StageType::Flat,
        'status' => StageStatus::Upcoming,
        'origin' => 'City A',
        'destination' => 'City B',
    ]);

    expect(fn () => $this->useCase->execute($this->edition->id, [
        'super_combativo' => $this->riderId,
    ]))->toThrow(ApplicationException::class, 'quedan 1 etapas sin finalizar');
});

test('saves classifications and triggers pre-race scoring when all stages finished', function () {
    StageModel::create([
        'id' => Str::uuid()->toString(),
        'edition_id' => $this->edition->id,
        'number' => 1,
        'name' => 'Etapa 1',
        'date' => '2026-07-01',
        'type' => StageType::Flat,
        'status' => StageStatus::Finished,
        'origin' => 'City A',
        'destination' => 'City B',
    ]);

    PredictionModel::create([
        'id' => Str::uuid()->toString(),
        'user_id' => $this->user->id,
        'league_id' => $this->league->id,
        'stage_id' => null,
        'type' => 'pre_race',
        'category' => 'super_combativo',
        'prediction_value' => ['rider_id' => $this->riderId],
        'locked_at' => now()->subDay(),
    ]);

    $result = $this->useCase->execute($this->edition->id, [
        'super_combativo' => $this->riderId,
    ]);

    expect($result['scored'])->toBe(1);
    expect($result['leagues'])->toBe(1);

    $this->assertDatabaseHas('final_classifications', [
        'edition_id' => $this->edition->id,
        'category' => 'super_combativo',
        'rider_id' => $this->riderId,
    ]);

    $this->assertDatabaseHas('score_events', [
        'user_id' => $this->user->id,
        'league_id' => $this->league->id,
        'points' => 30,
    ]);
});

test('marks edition as finished after saving classifications', function () {
    $this->useCase->execute($this->edition->id, [
        'super_combativo' => $this->riderId,
    ]);

    $this->edition->refresh();
    expect($this->edition->status)->toBe(EditionStatus::Finished);
});

test('allows saving when no stages exist', function () {
    $result = $this->useCase->execute($this->edition->id, [
        'super_combativo' => $this->riderId,
    ]);

    expect($result)->toBeArray();
    $this->assertDatabaseHas('final_classifications', [
        'edition_id' => $this->edition->id,
        'category' => 'super_combativo',
    ]);
});

test('ignores rest stages when checking finished status', function () {
    StageModel::create([
        'id' => Str::uuid()->toString(),
        'edition_id' => $this->edition->id,
        'number' => 1,
        'name' => 'Etapa 1',
        'date' => '2026-07-01',
        'type' => StageType::Flat,
        'status' => StageStatus::Finished,
        'origin' => 'City A',
        'destination' => 'City B',
    ]);

    StageModel::create([
        'id' => Str::uuid()->toString(),
        'edition_id' => $this->edition->id,
        'number' => 2,
        'name' => 'Descanso',
        'date' => '2026-07-02',
        'type' => StageType::Rest,
        'status' => StageStatus::Upcoming,
        'origin' => 'Rest',
        'destination' => 'Rest',
    ]);

    $result = $this->useCase->execute($this->edition->id, [
        'super_combativo' => $this->riderId,
    ]);

    expect($result)->toBeArray();
});

test('re-scores when classifications already existed', function () {
    FinalClassificationModel::create([
        'id' => Str::uuid()->toString(),
        'edition_id' => $this->edition->id,
        'category' => 'super_combativo',
        'rider_id' => $this->riderId,
    ]);

    PredictionModel::create([
        'id' => Str::uuid()->toString(),
        'user_id' => $this->user->id,
        'league_id' => $this->league->id,
        'stage_id' => null,
        'type' => 'pre_race',
        'category' => 'super_combativo',
        'prediction_value' => ['rider_id' => $this->riderId],
        'locked_at' => now()->subDay(),
    ]);

    $result = $this->useCase->execute($this->edition->id, [
        'super_combativo' => $this->riderId,
    ]);

    expect($result['scored'])->toBe(1);
    expect(ScoreEventModel::count())->toBe(1);
});
