<?php

declare(strict_types=1);

namespace Event\Application\Public\Query;

use Support\Pagination\KeysetCursor;

final class EventListPageToken
{
    public static function encode(string $title, string $eventId): string
    {
        return KeysetCursor::encode([
            'title' => $title,
            'eventId' => $eventId,
        ]);
    }

    public static function decode(string $value): DecodedEventListPageToken
    {
        $cursor = KeysetCursor::decode($value);

        return new DecodedEventListPageToken($cursor->string('title'), $cursor->string('eventId'));
    }
}
