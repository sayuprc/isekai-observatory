<?php

declare(strict_types=1);

namespace Release\Application\Viewer\List;

readonly class ListInputData
{
    public function __construct(
        public ?string $cursor,
        public ?int $limit,
    ) {
    }
}
