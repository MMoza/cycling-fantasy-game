<?php

declare(strict_types=1);

use App\Domain\Entities\Competition;
use App\Domain\ValueObjects\CompetitionType;
use App\Infrastructure\Repositories\EloquentCompetitionRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->repository = new EloquentCompetitionRepository;
});

test('saves and finds competition', function () {
    $competition = Competition::create(
        name: 'Tour de Francia',
        type: CompetitionType::GC,
        country: 'Francia',
    );

    $this->repository->save($competition);

    $found = $this->repository->find($competition->id);

    expect($found)->not->toBeNull();
    expect($found->name)->toBe('Tour de Francia');
    expect($found->type)->toBe(CompetitionType::GC);
});

test('returns null for non-existent competition', function () {
    $found = $this->repository->find('non-existent-id');

    expect($found)->toBeNull();
});

test('finds all competitions', function () {
    Competition::factory()->count(3)->create();

    $competitions = $this->repository->findAll();

    expect($competitions)->toHaveCount(3);
});

test('finds competitions by type', function () {
    Competition::factory()->create(['type' => CompetitionType::GC]);
    Competition::factory()->create(['type' => CompetitionType::Classic]);
    Competition::factory()->create(['type' => CompetitionType::GC]);

    $grandTours = $this->repository->findByType(CompetitionType::GC->value);

    expect($grandTours)->toHaveCount(2);
});

test('deletes competition', function () {
    $competition = Competition::factory()->create();

    $this->repository->delete($competition->id);

    $found = $this->repository->find($competition->id);

    expect($found)->toBeNull();
});
