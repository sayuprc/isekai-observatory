<?php

declare(strict_types=1);

namespace Viewer\Application\Release\UseCase\List;

readonly class ListInputData
{
    public function __construct(
        public ?string $cursor,
        public ?int $limit,
    ) {
    }
}
