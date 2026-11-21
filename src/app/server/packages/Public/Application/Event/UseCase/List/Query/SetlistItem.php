<?php

declare(strict_types=1);

namespace Public\Application\Event\UseCase\List\Query;

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
