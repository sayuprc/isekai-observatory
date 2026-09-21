<?php

declare(strict_types=1);

namespace Venue\Domain\Models;

enum VenueKind: int
{
    case Physical = 1;

    case Online = 2;

    public function getName(): string
    {
        return match ($this) {
            self::Physical => '現地',
            self::Online => 'オンライン',
        };
    }
}
