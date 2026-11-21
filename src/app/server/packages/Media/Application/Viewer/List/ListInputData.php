<?php

declare(strict_types=1);

namespace Media\Application\Viewer\List;

readonly class ListInputData
{
    public function __construct(
        public ?string $cursor,
        public ?int $limit,
    ) {
    }
}
