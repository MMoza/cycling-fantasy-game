<?php

declare(strict_types=1);

use App\Application\Exceptions\ApplicationException;
use App\Application\UseCases\Admin\Stage\GetStageFormDataUseCase;
use App\Application\UseCases\Admin\Stage\ShowAdminStageUseCase;
use App\Application\UseCases\Admin\Stage\StoreStageUseCase;
use App\Application\UseCases\Admin\Stage\UpdateStageUseCase;
use App\Application\UseCases\Prediction\StoreStagePredictionUseCase;
use App\Application\UseCases\Stage\ShowStageUseCase;
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
use App\Infrastructure\Persistence\Models\StageParticipantModel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();

    $this->competition = CompetitionModel::create([
        'id' => Str::uuid()->toString(),
        'name' => 'Campeonato Mundial',
        'type' => CompetitionType::Championship,
        'country_id' => createCountry(),
        'active' => true,
    ]);

    $this->edition = EditionModel::create([
        'id' => Str::uuid()->toString(),
        'competition_id' => $this->competition->id,
        'year' => 2026,
        'start_date' => '2026-09-20',
        'end_date' => '2026-09-21',
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
        'name' => 'Liga Mundial',
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
        createTestRider('Rider', 'Alpha'),
        createTestRider('Rider', 'Bravo'),
        createTestRider('Rider', 'Charlie'),
        createTestRider('Rider', 'Delta'),
        createTestRider('Rider', 'Echo'),
    ];

    foreach ($this->riders as $rider) {
        createTestParticipant($this->competition->id, $this->edition->id, $this->team->id, $rider->id);
    }

    $this->ttStage = StageModel::create([
        'id' => Str::uuid()->toString(),
        'edition_id' => $this->edition->id,
        'number' => 1,
        'name' => 'Contrareloj Individual',
        'date' => '2026-09-20',
        'scheduled_start' => '2026-09-20 10:00:00',
        'type' => StageType::TimeTrial,
        'distance' => 32.5,
        'origin' => 'Monaco',
        'destination' => 'Monaco',
        'status' => StageStatus::Upcoming,
    ]);

    $this->roadStage = StageModel::create([
        'id' => Str::uuid()->toString(),
        'edition_id' => $this->edition->id,
        'number' => 2,
        'name' => 'Prueba en Línea',
        'date' => '2026-09-21',
        'scheduled_start' => '2026-09-21 10:00:00',
        'type' => StageType::Flat,
        'distance' => 280.0,
        'origin' => 'Niza',
        'destination' => 'Niza',
        'status' => StageStatus::Upcoming,
    ]);
});

describe('StoreStageUseCase with stage_participants', function () {

    test('creates stage_participants when rider_ids are provided', function () {
        $useCase = new StoreStageUseCase;

        $riderIds = [$this->riders[0]->id, $this->riders[1]->id];

        $stage = $useCase->execute($this->edition->id, [
            'number' => 3,
            'name' => 'Etapa Test',
            'date' => '2026-09-22',
            'type' => 'flat',
            'origin' => 'A',
            'destination' => 'B',
            'rider_ids' => $riderIds,
        ]);

        $participants = StageParticipantModel::where('stage_id', $stage->id)->get();

        expect($participants->count())->toBe(2);
        expect($participants->pluck('rider_id')->sort()->values()->toArray())
            ->toEqual(collect($riderIds)->sort()->values()->toArray());
        expect($participants->first()->team_id)->toBe($this->team->id);
    });

    test('does not create stage_participants when rider_ids is empty', function () {
        $useCase = new StoreStageUseCase;

        $stage = $useCase->execute($this->edition->id, [
            'number' => 3,
            'name' => 'Etapa Test',
            'date' => '2026-09-22',
            'type' => 'flat',
            'origin' => 'A',
            'destination' => 'B',
        ]);

        $participants = StageParticipantModel::where('stage_id', $stage->id)->get();
        expect($participants->count())->toBe(0);
    });

    test('does not create stage_participants when rider_ids is empty array', function () {
        $useCase = new StoreStageUseCase;

        $stage = $useCase->execute($this->edition->id, [
            'number' => 3,
            'name' => 'Etapa Test',
            'date' => '2026-09-22',
            'type' => 'flat',
            'origin' => 'A',
            'destination' => 'B',
            'rider_ids' => [],
        ]);

        $participants = StageParticipantModel::where('stage_id', $stage->id)->get();
        expect($participants->count())->toBe(0);
    });

    test('only creates participants for riders in competition_participants', function () {
        $useCase = new StoreStageUseCase;
        $externalRider = createTestRider('External', 'Rider');

        $stage = $useCase->execute($this->edition->id, [
            'number' => 3,
            'name' => 'Etapa Test',
            'date' => '2026-09-22',
            'type' => 'flat',
            'origin' => 'A',
            'destination' => 'B',
            'rider_ids' => [$this->riders[0]->id, $externalRider->id],
        ]);

        $participants = StageParticipantModel::where('stage_id', $stage->id)->get();
        expect($participants->count())->toBe(1);
        expect($participants->first()->rider_id)->toBe($this->riders[0]->id);
    });
});

describe('UpdateStageUseCase with stage_participants', function () {

    test('syncs stage_participants on update', function () {
        $useCase = new UpdateStageUseCase;

        createTestStageParticipant($this->ttStage->id, $this->riders[0]->id, $this->team->id);
        createTestStageParticipant($this->ttStage->id, $this->riders[1]->id, $this->team->id);

        expect(StageParticipantModel::where('stage_id', $this->ttStage->id)->count())->toBe(2);

        $useCase->execute($this->edition->id, $this->ttStage->id, [
            'name' => 'CR Actualizada',
            'rider_ids' => [$this->riders[2]->id, $this->riders[3]->id, $this->riders[4]->id],
        ]);

        $participants = StageParticipantModel::where('stage_id', $this->ttStage->id)->get();
        expect($participants->count())->toBe(3);
        expect($participants->pluck('rider_id')->sort()->values()->toArray())
            ->toEqual(collect([$this->riders[2]->id, $this->riders[3]->id, $this->riders[4]->id])->sort()->values()->toArray());
    });

    test('removes all stage_participants when rider_ids is empty', function () {
        $useCase = new UpdateStageUseCase;

        createTestStageParticipant($this->ttStage->id, $this->riders[0]->id, $this->team->id);

        $useCase->execute($this->edition->id, $this->ttStage->id, [
            'rider_ids' => [],
        ]);

        expect(StageParticipantModel::where('stage_id', $this->ttStage->id)->count())->toBe(0);
    });

    test('does not touch participants when rider_ids not provided', function () {
        $useCase = new UpdateStageUseCase;

        createTestStageParticipant($this->ttStage->id, $this->riders[0]->id, $this->team->id);

        $useCase->execute($this->edition->id, $this->ttStage->id, [
            'name' => 'Solo nombre',
        ]);

        expect(StageParticipantModel::where('stage_id', $this->ttStage->id)->count())->toBe(1);
    });
});

describe('GetStageFormDataUseCase with championship type', function () {

    test('returns availableParticipants for championship type', function () {
        $useCase = new GetStageFormDataUseCase;

        $result = $useCase->execute($this->edition->id);

        expect($result['availableParticipants'])->toHaveCount(5);
        expect($result['stageParticipantIds'])->toBe([]);
        expect($result['edition']['competition_type'])->toBe('championship');
    });

    test('returns stageParticipantIds when stage exists', function () {
        $useCase = new GetStageFormDataUseCase;

        createTestStageParticipant($this->ttStage->id, $this->riders[0]->id, $this->team->id);
        createTestStageParticipant($this->ttStage->id, $this->riders[1]->id, $this->team->id);

        $result = $useCase->execute($this->edition->id, $this->ttStage->id);

        expect($result['stageParticipantIds'])->toHaveCount(2);
        expect($result['stageParticipantIds'])->toContain($this->riders[0]->id);
        expect($result['stageParticipantIds'])->toContain($this->riders[1]->id);
    });

    test('returns empty availableParticipants for non-championship type', function () {
        $gcCompetition = CompetitionModel::create([
            'id' => Str::uuid()->toString(),
            'name' => 'Tour de Francia',
            'type' => CompetitionType::GC,
            'country_id' => createCountry('ES', 'España'),
            'active' => true,
        ]);

        $gcEdition = EditionModel::create([
            'id' => Str::uuid()->toString(),
            'competition_id' => $gcCompetition->id,
            'year' => 2026,
            'start_date' => '2026-07-01',
            'end_date' => '2026-07-23',
            'status' => EditionStatus::Upcoming,
        ]);

        $useCase = new GetStageFormDataUseCase;
        $result = $useCase->execute($gcEdition->id);

        expect($result['availableParticipants'])->toBe([]);
    });
});

describe('ShowStageUseCase with stage_participants', function () {

    test('filters riders by stage_participants when present', function () {
        createTestStageParticipant($this->roadStage->id, $this->riders[0]->id, $this->team->id);
        createTestStageParticipant($this->roadStage->id, $this->riders[1]->id, $this->team->id);

        $useCase = new ShowStageUseCase;

        $result = $useCase->execute($this->user, $this->league->id, $this->roadStage->id);

        $riderIds = array_column($result['availableRiders']->toArray(), 'value');
        expect($riderIds)->toHaveCount(2);
        expect($riderIds)->toContain($this->riders[0]->id);
        expect($riderIds)->toContain($this->riders[1]->id);
        expect($riderIds)->not->toContain($this->riders[2]->id);
    });

    test('falls back to competition_participants when no stage_participants', function () {
        $useCase = new ShowStageUseCase;

        $result = $useCase->execute($this->user, $this->league->id, $this->roadStage->id);

        $riderIds = array_column($result['availableRiders']->toArray(), 'value');
        expect($riderIds)->toHaveCount(5);
    });

    test('each stage can have different riders', function () {
        createTestStageParticipant($this->ttStage->id, $this->riders[0]->id, $this->team->id);
        createTestStageParticipant($this->ttStage->id, $this->riders[1]->id, $this->team->id);

        createTestStageParticipant($this->roadStage->id, $this->riders[2]->id, $this->team->id);
        createTestStageParticipant($this->roadStage->id, $this->riders[3]->id, $this->team->id);
        createTestStageParticipant($this->roadStage->id, $this->riders[4]->id, $this->team->id);

        $useCase = new ShowStageUseCase;

        $ttResult = $useCase->execute($this->user, $this->league->id, $this->ttStage->id);
        $ttRiderIds = array_column($ttResult['availableRiders']->toArray(), 'value');
        expect($ttRiderIds)->toHaveCount(2);

        $roadResult = $useCase->execute($this->user, $this->league->id, $this->roadStage->id);
        $roadRiderIds = array_column($roadResult['availableRiders']->toArray(), 'value');
        expect($roadRiderIds)->toHaveCount(3);
    });
});

describe('StoreStagePredictionUseCase with stage_participants', function () {

    test('accepts rider in stage_participants', function () {
        createTestStageParticipant($this->roadStage->id, $this->riders[0]->id, $this->team->id);

        $useCase = new StoreStagePredictionUseCase;
        $useCase->execute($this->user, $this->league->id, $this->roadStage->id, [
            ['category' => 'stage_winner', 'value' => $this->riders[0]->id],
        ]);

        expect(PredictionModel::where('stage_id', $this->roadStage->id)->count())->toBe(1);
    });

    test('rejects rider not in stage_participants', function () {
        createTestStageParticipant($this->roadStage->id, $this->riders[0]->id, $this->team->id);

        $useCase = new StoreStagePredictionUseCase;

        try {
            $useCase->execute($this->user, $this->league->id, $this->roadStage->id, [
                ['category' => 'stage_winner', 'value' => $this->riders[2]->id],
            ]);
            $this->fail('Expected ApplicationException was not thrown');
        } catch (ApplicationException $e) {
            expect($e->getMessage())->toContain('participa en esta etapa');
        }

        expect(PredictionModel::where('stage_id', $this->roadStage->id)->count())->toBe(0);
    });

    test('falls back to competition_participants when no stage_participants', function () {
        $useCase = new StoreStagePredictionUseCase;
        $useCase->execute($this->user, $this->league->id, $this->roadStage->id, [
            ['category' => 'stage_winner', 'value' => $this->riders[0]->id],
        ]);

        expect(PredictionModel::where('stage_id', $this->roadStage->id)->count())->toBe(1);
    });

    test('rejects rider not in competition_participants when no stage_participants', function () {
        $externalRider = createTestRider('External', 'Rider');

        $useCase = new StoreStagePredictionUseCase;

        try {
            $useCase->execute($this->user, $this->league->id, $this->roadStage->id, [
                ['category' => 'stage_winner', 'value' => $externalRider->id],
            ]);
            $this->fail('Expected ApplicationException was not thrown');
        } catch (ApplicationException $e) {
            expect($e->getMessage())->toContain('participa en esta etapa');
        }

        expect(PredictionModel::where('stage_id', $this->roadStage->id)->count())->toBe(0);
    });
});

describe('ShowAdminStageUseCase with stage_participants', function () {

    test('returns stageParticipants when present', function () {
        createTestStageParticipant($this->ttStage->id, $this->riders[0]->id, $this->team->id);
        createTestStageParticipant($this->ttStage->id, $this->riders[1]->id, $this->team->id);

        $useCase = new ShowAdminStageUseCase;
        $result = $useCase->execute($this->edition->id, $this->ttStage->id);

        expect($result['stageParticipants'])->toHaveCount(2);
        expect($result['availableRiders'])->toHaveCount(2);
    });

    test('returns empty stageParticipants when no stage_participants', function () {
        $useCase = new ShowAdminStageUseCase;
        $result = $useCase->execute($this->edition->id, $this->ttStage->id);

        expect($result['stageParticipants'])->toBe([]);
        expect($result['availableRiders'])->toHaveCount(5);
    });
});
