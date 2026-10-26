<?php

use App\Domain\ValueObjects\CompetitionType;
use App\Domain\ValueObjects\EditionStatus;
use App\Infrastructure\Persistence\Models\BadgeEventModel;
use App\Infrastructure\Persistence\Models\UserBadgeModel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('season award-badges command awards badges to the given user', function () {
    $user = User::factory()->create();
    $data = badgeLeague('Carrera Comando', 2027, CompetitionType::GC);
    joinSeasonLeague($data['league'], $user);
    badgeStageWithResult($data['edition'], 1, 'rider-1', $data['league'], $user);

    $this->artisan('season:award-badges', ['--user' => $user->id])->assertExitCode(0);

    expect(UserBadgeModel::where('user_id', $user->id)->where('badge_code', 'stages_played')->exists())->toBeTrue();
    expect(UserBadgeModel::where('user_id', $user->id)->where('badge_code', 'tester')->exists())->toBeTrue();
});

test('season award-badges command dry-run persists nothing', function () {
    $user = User::factory()->create();
    $data = badgeLeague('Carrera Dry Comando', 2027, CompetitionType::GC);
    joinSeasonLeague($data['league'], $user);
    badgeStageWithResult($data['edition'], 1, 'rider-1', $data['league'], $user);

    $this->artisan('season:award-badges', ['--user' => $user->id, '--dry-run' => true])->assertExitCode(0);

    expect(UserBadgeModel::count())->toBe(0);
});

test('season award-badges command filters by season year', function () {
    $user2026 = User::factory()->create();
    $data2026 = badgeLeague('Carrera 2026', 2026, CompetitionType::GC);
    joinSeasonLeague($data2026['league'], $user2026);
    badgeStageWithResult($data2026['edition'], 1, 'rider-1', $data2026['league'], $user2026);

    $user2027 = User::factory()->create();
    $data2027 = badgeLeague('Carrera 2027', 2027, CompetitionType::GC);
    joinSeasonLeague($data2027['league'], $user2027);
    badgeStageWithResult($data2027['edition'], 1, 'rider-1', $data2027['league'], $user2027);

    $this->artisan('season:award-badges', ['year' => 2026])->assertExitCode(0);

    expect(UserBadgeModel::where('user_id', $user2026->id)->exists())->toBeTrue();
    expect(UserBadgeModel::where('user_id', $user2027->id)->exists())->toBeFalse();
});

test('season award-badges rebuild rewrites milestones with real achievement dates', function () {
    $user = User::factory()->create();
    $data = badgeLeague('Carrera Rebuild', 2027, CompetitionType::GC, EditionStatus::Finished);
    joinSeasonLeague($data['league'], $user);
    badgeStageWithResult($data['edition'], 1, 'rider-1', $data['league'], $user, 'rider-x', '2027-01-10');

    $this->artisan('season:award-badges', ['--user' => $user->id])->assertExitCode(0);

    // simula hitos ya guardados con la fecha del cálculo (p.ej. un backfill antiguo)
    BadgeEventModel::where('user_id', $user->id)->update(['created_at' => now()]);

    $this->artisan('season:award-badges', ['--user' => $user->id, '--rebuild' => true])->assertExitCode(0);

    $events = BadgeEventModel::where('user_id', $user->id)
        ->where('badge_code', 'stages_played')
        ->where('event', 'level')
        ->get();

    expect($events)->toHaveCount(1);
    expect($events->first()->created_at->toDateString())->toBe('2027-01-10');
});

test('season award-badges command fails for unknown users', function () {
    $this->artisan('season:award-badges', ['--user' => 'no-existe'])->assertExitCode(1);
});
