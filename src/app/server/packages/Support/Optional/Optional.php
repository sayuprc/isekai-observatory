<?php

declare(strict_types=1);

namespace Support\Optional;

/**
 * @template T
 */
interface Optional
{
    public function isPresent(): bool;

    public function isEmpty(): bool;

    /**
     * @return T
     */
    public function get(): mixed;
}
