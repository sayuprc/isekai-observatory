<?php

declare(strict_types=1);

namespace Release\Application\Viewer\Query;

use InvalidArgumentException;
use Support\Pagination\KeysetCursor;

final class ReleaseGroupListCursor
{
    public static function encode(int $orderNo, string $firstReleasedOn, string $releaseGroupId): string
    {
        return KeysetCursor::encode([
            'orderNo' => $orderNo,
            'firstReleasedOn' => $firstReleasedOn,
            'releaseGroupId' => $releaseGroupId,
        ]);
    }

    /**
     * @throws InvalidArgumentException
     */
    public static function decode(string $value): DecodedReleaseGroupListCursor
    {
        $cursor = KeysetCursor::decode($value);

        return new DecodedReleaseGroupListCursor(
            $cursor->int('orderNo'),
            $cursor->string('firstReleasedOn'),
            $cursor->string('releaseGroupId'),
        );
    }
}
