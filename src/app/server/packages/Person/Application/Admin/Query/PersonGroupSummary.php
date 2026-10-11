<?php

declare(strict_types=1);

namespace Person\Application\Admin\Query;

readonly class PersonGroupSummary
{
    /**
     * @param list<PersonGroupMemberSummary> $members
     */
    public function __construct(
        public string $personGroupId,
        public string $name,
        public array $members,
    ) {
    }
}
