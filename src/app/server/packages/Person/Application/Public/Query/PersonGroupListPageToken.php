<?php

declare(strict_types=1);

namespace Person\Application\Public\Query;

use Support\Pagination\KeysetCursor;

final class PersonGroupListPageToken
{
    public static function encode(string $name, string $personGroupId): string
    {
        return KeysetCursor::encode([
            'name' => $name,
            'personGroupId' => $personGroupId,
        ]);
    }

    public static function decode(string $value): DecodedPersonGroupListPageToken
    {
        $cursor = KeysetCursor::decode($value);

        return new DecodedPersonGroupListPageToken($cursor->string('name'), $cursor->string('personGroupId'));
    }
}
