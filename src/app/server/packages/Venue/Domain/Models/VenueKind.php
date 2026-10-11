<?php

declare(strict_types=1);

namespace Venue\Domain\Models;

enum VenueKind: int
{
    case Physical = 1;

    case Online = 2;
}
