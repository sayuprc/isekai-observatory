<?php

declare(strict_types=1);

namespace Person\Application\Public\List\Query;

readonly class DecodedPersonListPageToken
{
    public function __construct(
        public int $orderNo,
        public string $personId,
    ) {
    }
}
