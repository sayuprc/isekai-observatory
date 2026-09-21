<?php

declare(strict_types=1);

namespace Release\Application\Admin\Query;

readonly class ReleaseGroupReferencedRelease
{
    /**
     * @param list<int> $formatValues
     */
    public function __construct(
        public string $releaseId,
        public string $name,
        public string $releasedOn,
        public string $color,
        public bool $isDisplay,
        public int $orderNo,
        public array $formatValues,
    ) {
    }
}
