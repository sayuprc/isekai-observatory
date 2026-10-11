<?php

declare(strict_types=1);

namespace AdminUser\Domain\Models;

use Support\Domain\ValueObjects\Date\ImmutableDateTimeValueObject;

readonly class CreatedAt extends ImmutableDateTimeValueObject
{
}
