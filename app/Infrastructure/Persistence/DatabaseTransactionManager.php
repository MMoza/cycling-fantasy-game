<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Domain\Interfaces\TransactionManagerInterface;
use Illuminate\Support\Facades\DB;

class DatabaseTransactionManager implements TransactionManagerInterface
{
    public function run(callable $callback): mixed
    {
        return DB::transaction($callback);
    }
}
