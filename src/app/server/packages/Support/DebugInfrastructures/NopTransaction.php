<?php

declare(strict_types=1);

namespace Support\DebugInfrastructures;

use Closure;
use Override;
use Support\Contracts\TransactionInterface;

readonly class NopTransaction implements TransactionInterface
{
    #[Override]
    public function scope(Closure $callback): mixed
    {
        return $callback();
    }
}
