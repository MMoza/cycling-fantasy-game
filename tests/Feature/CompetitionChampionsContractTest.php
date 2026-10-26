<?php

use App\Domain\ValueObjects\EditionStatus;
use App\Infrastructure\Persistence\Models\EditionModel;
use App\Infrastructure\Persistence\Models\LeagueModel;
use App\Infrastructure\Persistence\Models\ScoreEventModel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

/**
 * @return array{winner: User, league: LeagueModel, edition: EditionModel}
 */
function championsFixture(bool $finished = true, bool $withScores = true): array
{
    $winner = User::factory()->create(['name' => 'Campeón User']);
    $rival = User::factory()->create(['name' => 'Segundo User']);

    $data = createSeasonLeague('Liga Palmarés', 2027, '2027-07-01', null, 'Tour de Francia', $finished ? EditionStatus::Finished : EditionStatus::Ongoing);
    $league = $data['league'];
    joinSeasonLeague($league, $winner);
    joinSeasonLeague($league, $rival);

    if ($withScores) {
        foreach ([[$winner, 100], [$rival, 50]] as [$user, $points]) {
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
    }

    return ['winner' => $winner, 'league' => $league, 'edition' => $data['edition']];
}

test('competition show exposes the champions contract', function () {
    $fixture = championsFixture();

    $this->actingAs(User::factory()->create())
        ->get(route('competitions.show', $fixture['edition']->id))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Competitions/Show')
            ->has('competition.champions', 1)
            ->where('competition.champions.0.leagueName', 'Liga Palmarés')
            ->where('competition.champions.0.userId', $fixture['winner']->id)
            ->where('competition.champions.0.userName', 'Campeón User')
            ->where('competition.champions.0.points', 100)
            ->has('competition.champions.0.avatar')
        );
});

test('competition show hides champions while the edition is ongoing', function () {
    $fixture = championsFixture(finished: false);

    $this->actingAs(User::factory()->create())
        ->get(route('competitions.show', $fixture['edition']->id))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Competitions/Show')
            ->where('competition.champions', [])
        );
});

test('competition show hides champions when the league has no scores', function () {
    $fixture = championsFixture(withScores: false);

    $this->actingAs(User::factory()->create())
        ->get(route('competitions.show', $fixture['edition']->id))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Competitions/Show')
            ->where('competition.champions', [])
        );
});

test('competition show only crowns official leagues', function () {
    $fixture = championsFixture();
    $fixture['league']->update(['is_official' => false]);

    $this->actingAs(User::factory()->create())
        ->get(route('competitions.show', $fixture['edition']->id))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Competitions/Show')
            ->where('competition.champions', [])
        );
});
