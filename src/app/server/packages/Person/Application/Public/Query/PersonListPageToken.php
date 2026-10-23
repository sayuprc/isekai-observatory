<?php

declare(strict_types=1);

namespace Person\Application\Public\Query;

use Support\Pagination\KeysetCursor;

final class PersonListPageToken
{
    public static function encode(int $orderNo, string $personId): string
    {
        return KeysetCursor::encode([
            'orderNo' => $orderNo,
            'personId' => $personId,
        ]);
    }

    public static function decode(string $value): DecodedPersonListPageToken
    {
        $cursor = KeysetCursor::decode($value);

        return new DecodedPersonListPageToken($cursor->int('orderNo'), $cursor->string('personId'));
    }
}
