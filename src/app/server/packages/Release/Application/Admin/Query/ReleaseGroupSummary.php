<?php

declare(strict_types=1);

namespace Release\Application\Admin\Query;

readonly class ReleaseGroupSummary
{
    public function __construct(
        public string $releaseGroupId,
        public string $title,
        public int $typeValue,
        public string $description,
        public bool $isDisplay,
        public int $orderNo,
        public ?string $firstReleasedOn,
    ) {
    }
}
