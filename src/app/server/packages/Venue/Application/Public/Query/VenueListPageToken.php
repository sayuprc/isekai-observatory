<?php

declare(strict_types=1);

namespace Venue\Application\Public\Query;

use Support\Pagination\KeysetCursor;

final class VenueListPageToken
{
    public static function encode(string $name, string $venueId): string
    {
        return KeysetCursor::encode([
            'name' => $name,
            'venueId' => $venueId,
        ]);
    }

    public static function decode(string $value): DecodedVenueListPageToken
    {
        $cursor = KeysetCursor::decode($value);

        return new DecodedVenueListPageToken($cursor->string('name'), $cursor->string('venueId'));
    }
}
