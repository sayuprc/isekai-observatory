<?php

declare(strict_types=1);

namespace Media\Domain\Models;

use Support\Domain\ValueObjects\Date\ImmutableDateTimeValueObject;

readonly class MediaPublishedAt extends ImmutableDateTimeValueObject
{
}
