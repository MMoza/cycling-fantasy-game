<?php

declare(strict_types=1);

namespace App\Providers;

use App\Domain\Interfaces\CompetitionRepositoryInterface;
use App\Domain\Interfaces\EditionRepositoryInterface;
use App\Domain\Interfaces\TransactionManagerInterface;
use App\Domain\Interfaces\UserRepositoryInterface;
use App\Infrastructure\Persistence\DatabaseTransactionManager;
use App\Infrastructure\Repositories\EloquentCompetitionRepository;
use App\Infrastructure\Repositories\EloquentEditionRepository;
use App\Infrastructure\Repositories\EloquentUserRepository;
use Illuminate\Support\ServiceProvider;

/**
 * Bindings de persistencia: cada interfaz de Domain queda asociada a su
 * implementación Eloquent en Infrastructure.
 */
class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(TransactionManagerInterface::class, DatabaseTransactionManager::class);
        $this->app->bind(UserRepositoryInterface::class, EloquentUserRepository::class);
        $this->app->bind(CompetitionRepositoryInterface::class, EloquentCompetitionRepository::class);
        $this->app->bind(EditionRepositoryInterface::class, EloquentEditionRepository::class);
    }
}
