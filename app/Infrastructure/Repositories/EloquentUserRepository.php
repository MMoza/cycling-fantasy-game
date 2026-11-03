<?php

declare(strict_types=1);

namespace App\Infrastructure\Repositories;

use App\Domain\Entities\User as UserEntity;
use App\Domain\Interfaces\UserRepositoryInterface;
use App\Domain\ValueObjects\UserPlan;
use App\Models\User as UserModel;

class EloquentUserRepository implements UserRepositoryInterface
{
    public function find(string $id): ?UserEntity
    {
        $model = UserModel::find($id);

        return $model ? $this->toEntity($model) : null;
    }

    public function findByEmail(string $email): ?UserEntity
    {
        $model = UserModel::where('email', $email)->first();

        return $model ? $this->toEntity($model) : null;
    }

    public function all(): array
    {
        return UserModel::orderBy('name')
            ->get()
            ->map(fn (UserModel $model): UserEntity => $this->toEntity($model))
            ->all();
    }

    public function save(UserEntity $user): void
    {
        UserModel::updateOrCreate(
            ['id' => $user->id],
            [
                'name' => $user->name,
                'email' => $user->email,
                'is_admin' => $user->isAdmin,
                'plan' => $user->plan,
                'avatar' => $user->avatar,
                'last_visited_league_id' => $user->lastVisitedLeagueId,
            ],
        );
    }

    private function toEntity(UserModel $model): UserEntity
    {
        /** @var UserPlan $plan */
        $plan = $model->plan;

        return new UserEntity(
            id: $model->id,
            name: $model->name,
            email: $model->email,
            isAdmin: (bool) $model->is_admin,
            plan: $plan,
            avatar: $model->avatar,
            lastVisitedLeagueId: $model->last_visited_league_id,
        );
    }
}
