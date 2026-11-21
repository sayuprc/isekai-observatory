<?php

declare(strict_types=1);

namespace Public\Application\Person\UseCase\Group\List\Query;

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
