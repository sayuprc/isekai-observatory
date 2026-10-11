<?php

declare(strict_types=1);

namespace Person\Domain\Models;

use Support\Domain\ValueObjects\String\UuidValueObject;

readonly class PersonId extends UuidValueObject
{
}
