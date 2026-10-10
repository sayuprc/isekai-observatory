<?php

declare(strict_types=1);

namespace Event\Application\Public\Query;

readonly class SetlistItem
{
    /**
     * @param list<string> $performanceIds
     */
    public function __construct(
        public ?string $label,
        public array $performanceIds,
    ) {
    }
}
