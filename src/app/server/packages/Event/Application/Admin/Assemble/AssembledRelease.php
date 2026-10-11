<?php

declare(strict_types=1);

namespace Event\Application\Admin\Assemble;

readonly class AssembledRelease
{
    /**
     * @param list<int> $formatValues
     */
    public function __construct(
        public string $releaseId,
        public string $releaseGroupId,
        public string $releaseGroupTitle,
        public string $name,
        public string $releasedOn,
        public bool $isDisplay,
        public array $formatValues,
    ) {
    }
}
