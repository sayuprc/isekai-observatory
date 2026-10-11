<?php

declare(strict_types=1);

namespace Event\Application\Viewer\Query;

use Support\Pagination\KeysetCursor;

final class EventListCursor
{
    public static function encode(?string $startOn, string $eventId): string
    {
        return KeysetCursor::encode([
            'startOn' => $startOn,
            'eventId' => $eventId,
        ]);
    }

    public static function decode(string $value): DecodedEventListCursor
    {
        $cursor = KeysetCursor::decode($value);

        return new DecodedEventListCursor($cursor->nullableString('startOn'), $cursor->string('eventId'));
    }
}
