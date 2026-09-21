<?php

declare(strict_types=1);

namespace Song\Domain\Models;

use Support\Domain\ValueObjects\String\UuidValueObject;

readonly class SongId extends UuidValueObject
{
}
