<?php

declare(strict_types=1);

namespace Tag\Application\Public\Query;

use Support\Pagination\KeysetCursor;

final class TagListPageToken
{
    public static function encode(int $orderNo, string $tagId): string
    {
        return KeysetCursor::encode([
            'orderNo' => $orderNo,
            'tagId' => $tagId,
        ]);
    }

    public static function decode(string $value): DecodedTagListPageToken
    {
        $cursor = KeysetCursor::decode($value);

        return new DecodedTagListPageToken($cursor->int('orderNo'), $cursor->string('tagId'));
    }
}
