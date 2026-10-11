<?php

declare(strict_types=1);

namespace Support\Infrastructures;

use Closure;
use Illuminate\Support\Facades\DB;
use Override;
use Support\Contracts\TransactionInterface;

readonly class DbTransaction implements TransactionInterface
{
    #[Override]
    public function scope(Closure $callback): mixed
    {
        return DB::transaction($callback);
    }
}
