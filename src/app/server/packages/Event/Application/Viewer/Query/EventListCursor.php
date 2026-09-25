<?php

declare(strict_types=1);

namespace Event\Application\Viewer\Query;

use InvalidArgumentException;

final class EventListCursor
{
    public static function encode(?string $startOn, string $eventId): string
    {
        return base64_encode(
            (string)json_encode(
                [
                    'startOn' => $startOn,
                    'eventId' => $eventId,
                ],
                JSON_THROW_ON_ERROR,
            ),
        );
    }

    public static function decode(string $value): DecodedEventListCursor
    {
        $decoded = base64_decode($value, true);

        if ($decoded === false) {
            throw new InvalidArgumentException('Invalid cursor.');
        }

        $data = json_decode($decoded, true, flags: JSON_THROW_ON_ERROR);

        if (
            ! is_array($data)
            || ! array_key_exists('startOn', $data)
            || ! isset($data['eventId'])
            || ! (is_null($data['startOn']) || is_string($data['startOn']))
            || ! is_string($data['eventId'])
        ) {
            throw new InvalidArgumentException('Invalid cursor.');
        }

        return new DecodedEventListCursor($data['startOn'], $data['eventId']);
    }
}
