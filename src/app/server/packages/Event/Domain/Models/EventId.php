<?php

declare(strict_types=1);

namespace Event\Domain\Models;

use Support\Domain\ValueObjects\String\UuidValueObject;

readonly class EventId extends UuidValueObject
{
}
