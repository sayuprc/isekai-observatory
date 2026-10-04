<?php

declare(strict_types=1);

namespace Person\Application\Admin\UseCase\Group\Create;

readonly class CreateInputData
{
    /**
     * @param list<array{personId: string, orderNo: int}> $members
     */
    public function __construct(
        public string $name,
        public array $members,
    ) {
    }
}
