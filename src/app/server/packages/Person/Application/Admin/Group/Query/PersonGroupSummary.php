<?php

declare(strict_types=1);

namespace Person\Application\Admin\Group\Query;

readonly class PersonGroupSummary
{
    /**
     * @param array<int, PersonGroupMemberSummary> $members
     */
    public function __construct(
        public string $personGroupId,
        public string $name,
        public string $displayName,
        public array $members,
    ) {
    }
}
