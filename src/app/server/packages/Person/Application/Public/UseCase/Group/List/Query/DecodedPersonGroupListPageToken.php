<?php

declare(strict_types=1);

namespace Person\Application\Public\UseCase\Group\List\Query;

readonly class DecodedPersonGroupListPageToken
{
    public function __construct(
        public string $name,
        public string $personGroupId,
    ) {
    }
}
