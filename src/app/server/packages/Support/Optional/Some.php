<?php

declare(strict_types=1);

namespace Support\Optional;

use Override;

/**
 * @template T
 *
 * @implements Optional<T>
 */
readonly class Some implements Optional
{
    /**
     * @param T $value
     */
    public function __construct(private mixed $value)
    {
    }

    #[Override]
    public function isPresent(): bool
    {
        return true;
    }

    #[Override]
    public function isEmpty(): bool
    {
        return false;
    }

    #[Override]
    public function get(): mixed
    {
        return $this->value;
    }
}
