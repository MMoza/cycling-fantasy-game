<?php

declare(strict_types=1);

use App\Domain\Entities\User as UserEntity;
use App\Domain\Interfaces\UserRepositoryInterface;
use App\Domain\ValueObjects\UserPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->repository = app(UserRepositoryInterface::class);
});

test('busca un usuario por id y lo mapea a la entidad de dominio', function () {
    $model = User::factory()->create([
        'name' => 'Ana Admin',
        'is_admin' => true,
        'plan' => 'premium',
    ]);

    $user = $this->repository->find($model->id);

    expect($user)->toBeInstanceOf(UserEntity::class)
        ->and($user->id)->toBe($model->id)
        ->and($user->name)->toBe('Ana Admin')
        ->and($user->email)->toBe($model->email)
        ->and($user->isAdmin)->toBeTrue()
        ->and($user->plan)->toBe(UserPlan::Premium)
        ->and($user->lastVisitedLeagueId)->toBeNull();
});

test('devuelve null cuando el usuario no existe', function () {
    expect($this->repository->find('00000000-0000-0000-0000-000000000000'))->toBeNull();
});

test('busca un usuario por email', function () {
    $model = User::factory()->create(['email' => 'ana@example.com']);

    $user = $this->repository->findByEmail('ana@example.com');

    expect($user?->id)->toBe($model->id);
    expect($this->repository->findByEmail('nobody@example.com'))->toBeNull();
});

test('lista los usuarios ordenados por nombre', function () {
    User::factory()->create(['name' => 'Zoe']);
    User::factory()->create(['name' => 'Ana']);
    User::factory()->create(['name' => 'Mario']);

    $users = $this->repository->all();

    expect($users)->toHaveCount(3)
        ->and(array_map(fn (UserEntity $user): string => $user->name, $users))
        ->toBe(['Ana', 'Mario', 'Zoe']);
});

test('guarda los cambios de un usuario sobre la persistencia', function () {
    $model = User::factory()->create(['name' => 'Ana']);
    $user = $this->repository->find($model->id);

    $this->repository->save(new UserEntity(
        id: $user->id,
        name: 'Ana Actualizada',
        email: $user->email,
        isAdmin: true,
        plan: UserPlan::Premium,
        avatar: $user->avatar,
        lastVisitedLeagueId: $user->lastVisitedLeagueId,
    ));

    $model->refresh();

    expect($model->name)->toBe('Ana Actualizada')
        ->and($model->is_admin)->toBeTrue()
        ->and($model->plan)->toBe(UserPlan::Premium);
});
