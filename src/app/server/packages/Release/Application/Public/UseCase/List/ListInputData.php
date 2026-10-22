<?php

declare(strict_types=1);

namespace Release\Application\Public\UseCase\List;

readonly class ListInputData
{
    public function __construct(
        public ?string $pageToken,
        public ?int $pageSize,
    ) {
    }
}
