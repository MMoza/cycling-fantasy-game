<?php

use App\Domain\ValueObjects\ActivityLogType;
use App\Domain\ValueObjects\EditionStatus;
use App\Domain\ValueObjects\PredictionCategory;
use App\Domain\ValueObjects\PredictionType;
use App\Domain\ValueObjects\StageStatus;
use App\Domain\ValueObjects\StageType;
use App\Infrastructure\Persistence\Models\ActivityLogModel;
use App\Infrastructure\Persistence\Models\LeagueModel;
use App\Infrastructure\Persistence\Models\PredictionModel;
use App\Infrastructure\Persistence\Models\ScoreEventModel;
use App\Infrastructure\Persistence\Models\StageModel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

/**
 * Full fixture for the Leagues/Show contract: 2 members, 3 stages (2 finished +
 * 1 upcoming), score events and one activity log.
 *
 * Totals: owner 70 pts (50 general + 10 + 10), rival 160 pts (100 + 30 + 30).
 *
 * @return array{owner: User, rival: User, league: LeagueModel}
 */
function leagueContractFixture(): array
{
    $owner = User::factory()->create(['name' => 'Owner User']);
    $rival = User::factory()->create(['name' => 'Rival User']);

    $data = createSeasonLeague('Liga Contrato', (int) date('Y'), date('Y').'-07-01', $owner, 'Copa Contrato', EditionStatus::Ongoing);
    $league = $data['league'];
    $edition = $data['edition'];
    joinSeasonLeague($league, $rival);

    StageModel::create([
        'id' => Str::uuid()->toString(),
        'edition_id' => $edition->id,
        'number' => 1,
        'name' => 'Etapa 1',
        'date' => date('Y').'-07-01',
        'type' => StageType::Hill,
        'distance' => 180.5,
        'origin' => 'Alicante',
        'destination' => 'Valencia',
        'status' => StageStatus::Finished,
        'difficulty' => 3,
    ]);

    StageModel::create([
        'id' => Str::uuid()->toString(),
        'edition_id' => $edition->id,
        'number' => 2,
        'name' => 'Etapa 2',
        'date' => date('Y').'-07-02',
        'type' => StageType::Flat,
        'distance' => 160.0,
        'origin' => 'Bilbao',
        'destination' => 'Santander',
        'status' => StageStatus::Finished,
        'difficulty' => 1,
    ]);

    $upcoming = StageModel::create([
        'id' => Str::uuid()->toString(),
        'edition_id' => $edition->id,
        'number' => 3,
        'name' => 'Etapa 3',
        'date' => date('Y').'-07-03',
        'scheduled_start' => date('Y').'-07-03 12:00:00',
        'type' => StageType::Mountain,
        'distance' => 120.0,
        'origin' => 'Granada',
        'destination' => 'Sierra Nevada',
        'status' => StageStatus::Upcoming,
        'difficulty' => 5,
    ]);

    $score = function (User $user, int $points, string $context, ?string $stageId = null) use ($league): void {
        ScoreEventModel::create([
            'id' => Str::uuid()->toString(),
            'user_id' => $user->id,
            'league_id' => $league->id,
            'scoring_rule_id' => Str::uuid()->toString(),
            'points' => $points,
            'description' => $context,
            'context' => $context,
            'stage_id' => $stageId,
        ]);
    };

    $stageIds = StageModel::where('edition_id', $edition->id)->orderBy('number')->pluck('id')->values();

    $score($owner, 50, 'general');
    $score($owner, 10, 'stage', $stageIds[0]);
    $score($owner, 10, 'stage', $stageIds[1]);
    $score($rival, 100, 'general');
    $score($rival, 30, 'stage', $stageIds[0]);
    $score($rival, 30, 'stage', $stageIds[1]);

    ActivityLogModel::create([
        'id' => Str::uuid()->toString(),
        'league_id' => $league->id,
        'type' => ActivityLogType::CompetitionStart,
        'title' => 'Competición iniciada',
        'description' => 'Todo listo',
        'data' => ['foo' => 'bar'],
    ]);

    PredictionModel::create([
        'id' => Str::uuid()->toString(),
        'user_id' => $owner->id,
        'league_id' => $league->id,
        'type' => PredictionType::PreStage,
        'category' => PredictionCategory::StageWinner,
        'stage_id' => $upcoming->id,
        'prediction_value' => ['rider_id' => 'rider-1'],
    ]);

    return ['owner' => $owner, 'rival' => $rival, 'league' => $league];
}

test('index locks the my_leagues and search_query contract', function () {
    $owner = User::factory()->create();
    createSeasonLeague('Liga Contrato', (int) date('Y'), date('Y').'-07-01', $owner);

    $response = $this->actingAs($owner)->get(route('leagues.index'));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('Leagues/Index')
        ->where('search_query', '')
        ->has('my_leagues', 1)
        ->has('my_leagues.0', fn (Assert $league) => $league
            ->hasAll(['id', 'name', 'edition', 'scoring_system', 'member_count', 'owner_id', 'invite_code', 'is_official', 'is_public'])
            ->whereType('id', 'string')
            ->whereType('name', 'string')
            ->has('edition', fn (Assert $edition) => $edition
                ->hasAll(['name', 'year'])
                ->whereType('name', 'string')
                ->whereType('year', 'integer'))
            ->has('scoring_system', fn (Assert $scoring) => $scoring
                ->hasAll(['name'])
                ->whereType('name', 'string'))
            ->whereType('member_count', 'integer')
            ->whereType('owner_id', 'string')
            ->whereType('invite_code', 'string')
            ->whereType('is_official', 'boolean')
            ->whereType('is_public', 'boolean'))
        ->has('public_leagues', 0));
});

test('index locks the public leagues paginator contract', function () {
    $searcher = User::factory()->create();
    $other = User::factory()->create();

    $alpha = createSeasonLeague('Liga Alpha', (int) date('Y'), date('Y').'-07-01', $searcher);
    createSeasonLeague('Liga Beta', (int) date('Y'), date('Y').'-08-01', $other);

    $response = $this->actingAs($searcher)->get(route('leagues.index', ['q' => 'Liga']));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('Leagues/Index')
        ->where('search_query', 'Liga')
        ->has('public_leagues.data', 2)
        ->has('public_leagues.data.0', fn (Assert $league) => $league
            ->hasAll(['id', 'name', 'edition', 'scoring_system', 'member_count', 'owner_name', 'is_joined', 'is_official'])
            ->whereType('id', 'string')
            ->whereType('name', 'string')
            ->has('edition', fn (Assert $edition) => $edition
                ->hasAll(['name', 'year'])
                ->whereType('year', 'integer'))
            ->has('scoring_system', fn (Assert $scoring) => $scoring->hasAll(['name']))
            ->whereType('member_count', 'integer')
            ->whereType('owner_name', 'string')
            ->whereType('is_joined', 'boolean')
            ->whereType('is_official', 'boolean'))
        ->where('public_leagues.data.0.id', $alpha['league']->id)
        ->where('public_leagues.data.0.is_joined', true)
        ->where('public_leagues.data.1.is_joined', false));
});

test('create locks the league form data contract', function () {
    $user = User::factory()->create();
    createSeasonLeague('Liga Contrato', (int) date('Y'), date('Y').'-07-01');

    $response = $this->actingAs($user)->get(route('leagues.create'));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('Leagues/Create')
        ->has('editions', 1)
        ->has('editions.0', fn (Assert $edition) => $edition
            ->hasAll(['id', 'name', 'year', 'competition'])
            ->whereType('id', 'string')
            ->whereType('year', 'integer')
            ->has('competition', fn (Assert $competition) => $competition
                ->hasAll(['name'])
                ->whereType('name', 'string')))
        ->has('scoringSystems')
        ->has('scoringSystems.0', fn (Assert $scoring) => $scoring
            ->hasAll(['id', 'name', 'description', 'type'])
            ->whereType('id', 'string')
            ->whereType('name', 'string')
            ->whereType('description', 'string')
            ->whereType('type', 'string')));
});

test('show locks the full league dashboard contract', function () {
    ['owner' => $owner, 'rival' => $rival, 'league' => $league] = leagueContractFixture();

    $response = $this->actingAs($owner)->get(route('leagues.show', $league->id));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('Leagues/Show')
        ->where('league_id', $league->id)
        ->has('season_leagues', 1)
        ->whereType('season_leagues.0.year', 'integer')
        ->has('league', fn (Assert $league) => $league
            ->hasAll([
                'id', 'name', 'invite_code', 'competition', 'owner_id', 'scoring_system',
                'is_official', 'is_public', 'max_players', 'member_count', 'is_owner',
                'edition_status', 'progress',
            ])
            ->whereType('id', 'string')
            ->whereType('name', 'string')
            ->whereType('invite_code', 'string')
            ->whereType('owner_id', 'string')
            ->whereType('is_official', 'boolean')
            ->whereType('is_public', 'boolean')
            ->whereType('max_players', 'integer')
            ->whereType('member_count', 'integer')
            ->whereType('is_owner', 'boolean')
            ->whereType('edition_status', 'string')
            ->has('competition', fn (Assert $competition) => $competition
                ->hasAll(['name', 'year', 'type', 'coverImageUrl', 'logoImageUrl'])
                ->whereType('name', 'string')
                ->whereType('year', 'integer')
                ->whereType('type', 'string'))
            ->has('scoring_system', fn (Assert $scoring) => $scoring
                ->hasAll(['name', 'type', 'description', 'rules'])
                ->whereType('name', 'string')
                ->whereType('type', 'string')
                ->whereType('description', 'string')
                ->whereType('rules', 'array')
                ->where('rules', function ($rules) {
                    foreach ($rules as $rule) {
                        foreach (['type', 'label', 'context', 'points', 'difficulty', 'position'] as $key) {
                            if (! array_key_exists($key, $rule)) {
                                return false;
                            }
                        }
                    }

                    return true;
                }))
            ->has('progress', fn (Assert $progress) => $progress
                ->hasAll(['current_stage', 'total_stages'])
                ->whereType('current_stage', 'integer')
                ->whereType('total_stages', 'integer')))
        ->where('league.member_count', 2)
        ->where('league.is_owner', true)
        ->where('league.edition_status', 'ongoing')
        ->where('league.progress.current_stage', 3)
        ->where('league.progress.total_stages', 3)
        ->has('next_stage', fn (Assert $stage) => $stage
            ->hasAll([
                'id', 'number', 'name', 'date', 'type', 'type_value', 'distance', 'distance_value',
                'origin', 'destination', 'status', 'scheduled_start', 'difficulty', 'has_predictions',
            ])
            ->whereType('number', 'integer')
            ->whereType('name', 'string')
            ->whereType('date', 'string')
            ->whereType('type', 'string')
            ->whereType('type_value', 'string')
            ->whereType('origin', 'string')
            ->whereType('destination', 'string')
            ->whereType('status', 'string')
            ->whereType('has_predictions', 'boolean'))
        ->where('next_stage.number', 3)
        ->where('next_stage.type_value', 'mountain')
        ->where('next_stage.origin', 'Granada')
        ->where('next_stage.destination', 'Sierra Nevada')
        ->where('next_stage.has_predictions', true)
        ->has('user_position', fn (Assert $position) => $position
            ->hasAll(['rank', 'points', 'behind_leader'])
            ->whereType('rank', 'string')
            ->whereType('points', 'string')
            ->whereType('behind_leader', 'string'))
        ->where('user_position.rank', '2')
        ->where('user_position.points', '70')
        ->where('user_position.behind_leader', '90 pts')
        ->has('stages', 3)
        ->has('stages.0', fn (Assert $stage) => $stage
            ->hasAll(['id', 'number', 'name', 'date', 'type', 'distance', 'status'])
            ->whereType('number', 'integer')
            ->whereType('name', 'string')
            ->whereType('date', 'string')
            ->whereType('type', 'string')
            ->whereType('status', 'string'))
        ->where('stages.0.name', 'Alicante → Valencia')
        ->where('stages.0.distance', '180.5 km')
        ->has('leaderboard', 2)
        ->has('leaderboard.0', fn (Assert $entry) => $entry
            ->hasAll([
                'user_id', 'user_name', 'avatar', 'points', 'is_current_user', 'is_online',
                'rank', 'previous_rank', 'rank_change', 'behind_leader', 'winner_streak',
            ])
            ->whereType('user_id', 'string')
            ->whereType('user_name', 'string')
            ->whereType('points', 'integer')
            ->whereType('is_current_user', 'boolean')
            ->whereType('is_online', 'boolean')
            ->whereType('rank', 'integer')
            ->whereType('behind_leader', 'integer')
            ->whereType('winner_streak', 'integer'))
        ->where('leaderboard.0.user_name', 'Rival User')
        ->where('leaderboard.0.points', 160)
        ->where('leaderboard.0.rank', 1)
        ->where('leaderboard.0.previous_rank', 1)
        ->where('leaderboard.0.rank_change', 0)
        ->where('leaderboard.0.behind_leader', 0)
        ->where('leaderboard.0.winner_streak', 0)
        ->where('leaderboard.1.user_name', 'Owner User')
        ->where('leaderboard.1.points', 70)
        ->where('leaderboard.1.rank', 2)
        ->where('leaderboard.1.behind_leader', 90)
        ->where('leaderboard.1.is_current_user', true)
        ->has('activity_logs', 1)
        ->has('activity_logs.0', fn (Assert $log) => $log
            ->hasAll(['id', 'type', 'title', 'description', 'data', 'created_at'])
            ->whereType('id', 'string')
            ->whereType('type', 'string')
            ->whereType('title', 'string')
            ->whereType('created_at', 'string'))
        ->where('activity_logs.0.type', 'competition_start')
        ->where('activity_logs.0.title', 'Competición iniciada'));
});

test('show locks the empty dashboard contract when the edition has no stages', function () {
    $owner = User::factory()->create();
    $league = createSeasonLeague('Liga Vacía', (int) date('Y'), date('Y').'-07-01', $owner)['league'];

    $response = $this->actingAs($owner)->get(route('leagues.show', $league->id));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('Leagues/Show')
        ->where('next_stage', null)
        ->where('user_position.rank', '1')
        ->where('user_position.points', '0')
        ->where('user_position.behind_leader', '0 pts')
        ->has('stages', 0)
        ->has('leaderboard', 1)
        ->where('leaderboard.0.points', 0)
        ->where('leaderboard.0.rank', 1)
        ->has('activity_logs', 0));
});
