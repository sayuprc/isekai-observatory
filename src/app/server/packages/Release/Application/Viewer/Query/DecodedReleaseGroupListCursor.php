<?php

declare(strict_types=1);

namespace Release\Application\Viewer\Query;

readonly class DecodedReleaseGroupListCursor
{
    public function __construct(
        public int $orderNo,
        public string $firstReleasedOn,
        public string $releaseGroupId,
    ) {
    }
}
