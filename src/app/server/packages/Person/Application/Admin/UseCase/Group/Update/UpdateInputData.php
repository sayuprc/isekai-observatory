<?php

declare(strict_types=1);

namespace Person\Application\Admin\UseCase\Group\Update;

readonly class UpdateInputData
{
    /**
     * @param list<array{personId: string, orderNo: int}> $members
     */
    public function __construct(
        public string $personGroupId,
        public string $name,
        public array $members,
    ) {
    }
}
