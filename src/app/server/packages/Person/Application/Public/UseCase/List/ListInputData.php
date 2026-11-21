<?php

declare(strict_types=1);

namespace Person\Application\Public\UseCase\List;

use SensitiveParameter;

readonly class ListInputData
{
    public function __construct(
        #[SensitiveParameter]
        public ?string $pageToken,
        public ?int $pageSize,
    ) {
    }
}
