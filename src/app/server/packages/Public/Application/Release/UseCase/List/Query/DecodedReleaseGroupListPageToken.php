<?php

declare(strict_types=1);

namespace Public\Application\Release\UseCase\List\Query;

readonly class DecodedReleaseGroupListPageToken
{
    public function __construct(
        public int $orderNo,
        public string $releaseGroupId,
    ) {
    }
}
