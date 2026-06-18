<?php

declare(strict_types=1);

use App\Domain\Entities\Edition;
use App\Infrastructure\Persistence\Models\CompetitionModel;
use App\Infrastructure\Repositories\EloquentEditionRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->repository = new EloquentEditionRepository;
    $this->competition = CompetitionModel::factory()->create();
});

test('saves and finds edition', function () {
    $edition = Edition::create(
        competitionId: $this->competition->id,
        year: 2026,
        startDate: '2026-07-01',
        endDate: '2026-07-23',
    );

    $this->repository->save($edition);

    $found = $this->repository->find($edition->id);

    expect($found)->not->toBeNull();
    expect($found->year)->toBe(2026);
    expect($found->competitionId)->toBe($this->competition->id);
});

test('finds editions by competition', function () {
    Edition::factory()->count(3)->create(['competition_id' => $this->competition->id]);

    $editions = $this->repository->findByCompetition($this->competition->id);

    expect($editions)->toHaveCount(3);
});

test('finds editions by year', function () {
    Edition::factory()->create(['year' => 2026]);
    Edition::factory()->create(['year' => 2027]);
    Edition::factory()->create(['year' => 2026]);

    $editions = $this->repository->findByYear(2026);

    expect($editions)->toHaveCount(2);
});

test('deletes edition', function () {
    $edition = Edition::factory()->create();

    $this->repository->delete($edition->id);

    $found = $this->repository->find($edition->id);

    expect($found)->toBeNull();
});
