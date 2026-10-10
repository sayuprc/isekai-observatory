<?php

declare(strict_types=1);

namespace Release\Application\Public\Query;

readonly class DecodedReleaseGroupListPageToken
{
    public function __construct(
        public int $orderNo,
        public string $releaseGroupId,
    ) {
    }
}
