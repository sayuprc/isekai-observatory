<?php

declare(strict_types=1);

namespace Release\Application\Public\Query;

use Support\Pagination\KeysetCursor;

final class ReleaseGroupListPageToken
{
    public static function encode(int $orderNo, string $releaseGroupId): string
    {
        return KeysetCursor::encode([
            'orderNo' => $orderNo,
            'releaseGroupId' => $releaseGroupId,
        ]);
    }

    public static function decode(string $value): DecodedReleaseGroupListPageToken
    {
        $cursor = KeysetCursor::decode($value);

        return new DecodedReleaseGroupListPageToken($cursor->int('orderNo'), $cursor->string('releaseGroupId'));
    }
}
