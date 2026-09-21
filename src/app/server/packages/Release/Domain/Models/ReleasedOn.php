<?php

declare(strict_types=1);

namespace Release\Domain\Models;

use Support\Domain\ValueObjects\Date\ImmutableDateValueObject;

readonly class ReleasedOn extends ImmutableDateValueObject
{
}
