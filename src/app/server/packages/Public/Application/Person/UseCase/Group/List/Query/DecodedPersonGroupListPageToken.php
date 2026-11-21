<?php

declare(strict_types=1);

namespace Public\Application\Person\UseCase\Group\List\Query;

readonly class DecodedPersonGroupListPageToken
{
    public function __construct(
        public string $name,
        public string $personGroupId,
    ) {
    }
}
