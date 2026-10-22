<?php

declare(strict_types=1);

namespace Person\Application\Public\Query;

readonly class PersonGroupListItem
{
    /**
     * @param list<string> $memberIds
     */
    public function __construct(
        public string $personGroupId,
        public string $name,
        public array $memberIds,
    ) {
    }
}
