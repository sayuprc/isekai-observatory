<?php

declare(strict_types=1);

namespace Support\Contracts;

use Closure;

interface TransactionInterface
{
    /**
     * @template T
     *
     * @param Closure(): T $callback
     *
     * @return T
     */
    public function scope(Closure $callback): mixed;
}
