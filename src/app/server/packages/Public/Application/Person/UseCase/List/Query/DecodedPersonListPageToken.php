<?php

declare(strict_types=1);

namespace Public\Application\Person\UseCase\List\Query;

readonly class DecodedPersonListPageToken
{
    public function __construct(
        public int $orderNo,
        public string $personId,
    ) {
    }
}
