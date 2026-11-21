<?php

declare(strict_types=1);

namespace Person\Application\Admin\Group\Query;

readonly class PersonGroupMemberSummary
{
    public function __construct(
        public string $personId,
        public string $name,
        public int $orderNo,
    ) {
    }
}
