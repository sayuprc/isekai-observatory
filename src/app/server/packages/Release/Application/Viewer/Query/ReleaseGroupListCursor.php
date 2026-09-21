<?php

declare(strict_types=1);

namespace Release\Application\Viewer\Query;

use InvalidArgumentException;

final class ReleaseGroupListCursor
{
    public static function encode(int $orderNo, string $firstReleasedOn, string $releaseGroupId): string
    {
        return base64_encode(
            (string)json_encode(
                [
                    'orderNo' => $orderNo,
                    'firstReleasedOn' => $firstReleasedOn,
                    'releaseGroupId' => $releaseGroupId,
                ],
                JSON_THROW_ON_ERROR,
            ),
        );
    }

    public static function decode(string $value): DecodedReleaseGroupListCursor
    {
        $decoded = base64_decode($value, true);

        if ($decoded === false) {
            throw new InvalidArgumentException('Invalid cursor.');
        }

        $data = json_decode($decoded, true, flags: JSON_THROW_ON_ERROR);

        if (! is_array($data) || ! isset($data['orderNo'], $data['firstReleasedOn'], $data['releaseGroupId']) || ! is_int($data['orderNo']) || ! is_string($data['firstReleasedOn']) || ! is_string($data['releaseGroupId'])) {
            throw new InvalidArgumentException('Invalid cursor.');
        }

        return new DecodedReleaseGroupListCursor($data['orderNo'], $data['firstReleasedOn'], $data['releaseGroupId']);
    }
}
