<?php

declare(strict_types=1);

namespace Event\Application\Public\Query;

readonly class CoVocalistItem
{
    public function __construct(
        public string $personId,
        public ?string $creditName,
        public ?string $personGroupId,
    ) {
    }
}
