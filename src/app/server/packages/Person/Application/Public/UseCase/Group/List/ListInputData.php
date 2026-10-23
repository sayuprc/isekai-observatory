<?php

declare(strict_types=1);

namespace Person\Application\Public\UseCase\Group\List;

readonly class ListInputData
{
    public function __construct(
        public ?string $pageToken,
        public ?int $pageSize,
    ) {
    }
}
