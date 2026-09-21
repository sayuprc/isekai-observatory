<?php

declare(strict_types=1);

namespace Support\Optional;

use LogicException;
use Override;

/**
 * @implements Optional<never>
 */
readonly class None implements Optional
{
    #[Override]
    public function isPresent(): bool
    {
        return false;
    }

    #[Override]
    public function isEmpty(): bool
    {
        return true;
    }

    #[Override]
    public function get(): mixed
    {
        throw new LogicException('None から値は取得できません。');
    }
}
