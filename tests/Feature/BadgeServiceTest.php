<?php

use App\Application\Services\BadgeService;
use App\Application\Services\PredictionHitService;
use App\Domain\ValueObjects\CompetitionType;
use App\Domain\ValueObjects\PredictionCategory;
use App\Domain\ValueObjects\PredictionType;
use App\Domain\ValueObjects\StageStatus;
use App\Domain\ValueObjects\StageType;
use App\Infrastructure\Persistence\Models\BadgeEventModel;
use App\Infrastructure\Persistence\Models\CountryModel;
use App\Infrastructure\Persistence\Models\EditionModel;
use App\Infrastructure\Persistence\Models\FinalClassificationModel;
use App\Infrastructure\Persistence\Models\LeagueModel;
use App\Infrastructure\Persistence\Models\PredictionModel;
use App\Infrastructure\Persistence\Models\ScoreEventModel;
use App\Infrastructure\Persistence\Models\StageModel;
use App\Infrastructure\Persistence\Models\StageResultModel;
use App\Infrastructure\Persistence\Models\UserBadgeModel;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

function badgeService(): BadgeService
{
    return new BadgeService(new PredictionHitService);
}

function badgeLeague(string $competitionName, int $year, CompetitionType $type): array
{
    $data = createSeasonLeague($competitionName, $year, $year.'-07-01', null, $competitionName);
    $data['league']->edition->competition->update(['type' => $type]);
    $data['league']->load('edition.competition');

    return $data;
}

function badgeStageWithResult(EditionModel $edition, int $number, string $winnerRiderId, LeagueModel $league, User $user, ?string $predictedRiderId = null): StageModel
{
    $stage = StageModel::create([
        'id' => Str::uuid()->toString(),
        'edition_id' => $edition->id,
        'number' => $number,
        'name' => 'Etapa '.$number,
        'date' => now()->subDay()->format('Y-m-d'),
        'type' => StageType::Hill,
        'distance' => 150.0,
        'origin' => 'A',
        'destination' => 'B',
        'status' => StageStatus::Finished,
    ]);

    StageResultModel::create([
        'id' => Str::uuid()->toString(),
        'stage_id' => $stage->id,
        'rider_id' => $winnerRiderId,
        'position' => 1,
    ]);

    PredictionModel::create([
        'id' => Str::uuid()->toString(),
        'user_id' => $user->id,
        'league_id' => $league->id,
        'type' => PredictionType::PreStage->value,
        'category' => PredictionCategory::StageWinner->value,
        'stage_id' => $stage->id,
        'prediction_value' => ['rider_id' => $predictedRiderId ?? $winnerRiderId],
    ]);

    return $stage;
}

function badgeScore(User $user, LeagueModel $league, int $points): void
{
    ScoreEventModel::create([
        'id' => Str::uuid()->toString(),
        'user_id' => $user->id,
        'league_id' => $league->id,
        'scoring_rule_id' => Str::uuid()->toString(),
        'points' => $points,
        'description' => 'test',
        'context' => 'test',
    ]);
}

test('badge service tiers stages played and stages won', function () {
    $user = User::factory()->create();
    $data = badgeLeague('Carrera Etapas', 2027, CompetitionType::GC);
    joinSeasonLeague($data['league'], $user);

    foreach (range(1, 5) as $n) {
        badgeStageWithResult($data['edition'], $n, 'rider-1', $data['league'], $user, $n === 1 ? 'rider-1' : 'rider-x');
    }

    badgeService()->recalculateForUser($user);

    $played = UserBadgeModel::where('user_id', $user->id)->where('badge_code', 'stages_played')->firstOrFail();
    expect($played->progress)->toBe(5);
    expect($played->level)->toBe(5);

    $won = UserBadgeModel::where('user_id', $user->id)->where('badge_code', 'stages_won')->firstOrFail();
    expect($won->progress)->toBe(1);
    expect($won->level)->toBe(1);
});

test('badge service tracks participation per race type', function () {
    $user = User::factory()->create();

    $monument = badgeLeague('Monumento Badge', 2027, CompetitionType::Monument);
    joinSeasonLeague($monument['league'], $user);
    badgeStageWithResult($monument['edition'], 1, 'rider-1', $monument['league'], $user, 'rider-x');

    $classic = badgeLeague('Clásica Badge', 2027, CompetitionType::Classic);
    joinSeasonLeague($classic['league'], $user);
    badgeStageWithResult($classic['edition'], 1, 'rider-1', $classic['league'], $user, 'rider-x');

    badgeService()->recalculateForUser($user);

    expect(UserBadgeModel::where('badge_code', 'monument_participation')->firstOrFail()->progress)->toBe(1);
    expect(UserBadgeModel::where('badge_code', 'classic_participation')->firstOrFail()->progress)->toBe(1);
    expect(UserBadgeModel::where('badge_code', 'grand_tour_participation')->exists())->toBeFalse();
});

test('badge service awards grand tour winners and trilogy milestones', function () {
    CountryModel::firstOrCreate(['id' => 'FR'], ['name' => 'Francia']);

    $user = User::factory()->create();
    $winner = createTestRider('Test', 'Winner');

    foreach ([['Tour de France', 'tour_winner'], ['Giro d\'Italia', 'giro_winner'], ['Vuelta a España', 'vuelta_winner']] as [$name, $badge]) {
        if ($badge === 'vuelta_winner') {
            // antes del tercer GT: trilogía en 2/3
            badgeService()->recalculateForUser($user);
            $trilogy = UserBadgeModel::where('badge_code', 'trilogy')->firstOrFail();
            expect($trilogy->progress)->toBe(2);
            expect($trilogy->level)->toBe(0);
        }

        $data = badgeLeague($name, 2027, CompetitionType::GC);
        joinSeasonLeague($data['league'], $user);

        FinalClassificationModel::create([
            'id' => Str::uuid()->toString(),
            'edition_id' => $data['edition']->id,
            'category' => 'gc_top_5',
            'position' => 1,
            'rider_id' => $winner->id,
        ]);

        PredictionModel::create([
            'id' => Str::uuid()->toString(),
            'user_id' => $user->id,
            'league_id' => $data['league']->id,
            'type' => PredictionType::PreRace->value,
            'category' => PredictionCategory::GcTop5->value,
            'stage_id' => null,
            'prediction_value' => ['rider_ids' => [$winner->id]],
        ]);
    }

    badgeService()->recalculateForUser($user);

    expect(UserBadgeModel::where('badge_code', 'tour_winner')->firstOrFail()->level)->toBe(1);
    expect(UserBadgeModel::where('badge_code', 'giro_winner')->firstOrFail()->level)->toBe(1);
    expect(UserBadgeModel::where('badge_code', 'vuelta_winner')->firstOrFail()->level)->toBe(1);

    $trilogy = UserBadgeModel::where('badge_code', 'trilogy')->firstOrFail();
    expect($trilogy->progress)->toBe(3);
    expect($trilogy->level)->toBe(1);
    expect(BadgeEventModel::where('badge_code', 'trilogy')->where('event', 'progress')->count())->toBeGreaterThanOrEqual(2);
    expect(BadgeEventModel::where('badge_code', 'trilogy')->where('event', 'level')->count())->toBe(1);
});

test('badge service awards monument wins, collection and world champion once', function () {
    $user = User::factory()->create();

    foreach ([['Monumento A', CompetitionType::Monument], ['Monumento B', CompetitionType::Monument]] as [$name, $type]) {
        $data = badgeLeague($name, 2027, $type);
        joinSeasonLeague($data['league'], $user);
        badgeStageWithResult($data['edition'], 1, 'rider-1', $data['league'], $user);
    }

    $worlds = badgeLeague('World Championships', 2027, CompetitionType::Championship);
    joinSeasonLeague($worlds['league'], $user);
    badgeStageWithResult($worlds['edition'], 1, 'rider-1', $worlds['league'], $user);

    badgeService()->recalculateForUser($user);

    expect(UserBadgeModel::where('badge_code', 'monument_winner')->firstOrFail()->progress)->toBe(2);
    expect(UserBadgeModel::where('badge_code', 'monument_collection')->firstOrFail()->progress)->toBe(2);
    expect(UserBadgeModel::where('badge_code', 'monument_collection')->firstOrFail()->level)->toBe(0);

    $world = UserBadgeModel::where('badge_code', 'world_champion')->firstOrFail();
    expect($world->level)->toBe(1);
    expect($world->progress)->toBe(1);
});

test('badge service counts season championships cumulatively and awards tester', function () {
    $user = User::factory()->create();
    $rival = User::factory()->create();

    foreach ([2026, 2027] as $year) {
        $data = badgeLeague('Carrera Temporada '.$year, $year, CompetitionType::GC);
        joinSeasonLeague($data['league'], $user);
        joinSeasonLeague($data['league'], $rival);
        badgeScore($user, $data['league'], 100);
        badgeScore($rival, $data['league'], 50);

        // predicción para que el usuario cuente como participante (badge TESTER)
        badgeStageWithResult($data['edition'], 1, 'rider-1', $data['league'], $user, 'rider-x');
    }

    badgeService()->recalculateForUser($user);

    expect(UserBadgeModel::where('badge_code', 'season_champion')->firstOrFail()->level)->toBe(2);
    expect(UserBadgeModel::where('badge_code', 'tester')->firstOrFail()->level)->toBe(1);
});

test('badge service is idempotent and levels never decrease', function () {
    $user = User::factory()->create();
    $data = badgeLeague('Carrera Idempotente', 2027, CompetitionType::GC);
    joinSeasonLeague($data['league'], $user);
    badgeStageWithResult($data['edition'], 1, 'rider-1', $data['league'], $user);

    badgeService()->recalculateForUser($user);
    $events = BadgeEventModel::where('user_id', $user->id)->count();
    $badges = UserBadgeModel::where('user_id', $user->id)->count();

    badgeService()->recalculateForUser($user);
    badgeService()->recalculateForUser($user);

    expect(BadgeEventModel::where('user_id', $user->id)->count())->toBe($events);
    expect(UserBadgeModel::where('user_id', $user->id)->count())->toBe($badges);
});

test('badge service dry-run reports without persisting', function () {
    $user = User::factory()->create();
    $data = badgeLeague('Carrera Dry', 2027, CompetitionType::GC);
    joinSeasonLeague($data['league'], $user);
    badgeStageWithResult($data['edition'], 1, 'rider-1', $data['league'], $user);

    $report = badgeService()->recalculateForUser($user, dryRun: true);

    expect($report['earned'])->not->toBeEmpty();
    expect(UserBadgeModel::count())->toBe(0);
    expect(BadgeEventModel::count())->toBe(0);
});

test('badge detail exposes levels with dates and milestone history', function () {
    $user = User::factory()->create();
    $data = badgeLeague('Carrera Detalle', 2027, CompetitionType::GC);
    joinSeasonLeague($data['league'], $user);
    badgeStageWithResult($data['edition'], 1, 'rider-1', $data['league'], $user);

    badgeService()->recalculateForUser($user);

    $detail = badgeService()->badgeDetail($user, 'stages_played');

    expect($detail->user['id'])->toBe($user->id);
    expect($detail->progress)->toBe(1);
    expect($detail->levelsDetail)->toHaveCount(5);
    expect($detail->levelsDetail[0]['reached'])->toBeTrue();
    expect($detail->levelsDetail[0]['achieved_at'])->not->toBeNull();
    expect($detail->levelsDetail[1]['reached'])->toBeFalse();
    expect($detail->history)->not->toBeEmpty();

    expect(fn () => badgeService()->badgeDetail($user, 'badge-inventado'))
        ->toThrow(ModelNotFoundException::class);
});

test('badge status exposes earned badges and pending progress', function () {
    $user = User::factory()->create();
    $data = badgeLeague('Carrera Estado', 2027, CompetitionType::GC);
    joinSeasonLeague($data['league'], $user);
    badgeStageWithResult($data['edition'], 1, 'rider-1', $data['league'], $user);

    badgeService()->recalculateForUser($user);
    $status = badgeService()->badgeStatusForUser($user);

    $earnedCodes = array_column($status->earned, 'code');
    expect($earnedCodes)->toContain('stages_played');
    expect($earnedCodes)->toContain('tester');

    $pendingCodes = array_column($status->pending, 'code');
    expect($pendingCodes)->toContain('trilogy');
    // los badges especiales (TESTER) nunca aparecen como pendientes
    expect($pendingCodes)->not->toContain('tester');
    expect($status->pending[array_search('trilogy', $pendingCodes)]['target'])->toBe(3);

    // los badges por niveles exponen sus tiers para el relleno/hueco de la UI
    $stagesPlayed = $status->earned[array_search('stages_played', $earnedCodes)];
    expect($stagesPlayed['levels'])->toBe([1, 5, 25, 100, 500]);
    expect($stagesPlayed['level_index'])->toBe(1);
});
