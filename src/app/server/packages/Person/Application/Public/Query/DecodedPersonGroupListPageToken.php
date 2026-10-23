<?php

declare(strict_types=1);

namespace Person\Application\Public\Query;

readonly class DecodedPersonGroupListPageToken
{
    public function __construct(
        public string $name,
        public string $personGroupId,
    ) {
    }
}
