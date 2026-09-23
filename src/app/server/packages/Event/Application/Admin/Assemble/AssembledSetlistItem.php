<?php

declare(strict_types=1);

namespace Event\Application\Admin\Assemble;

readonly class AssembledSetlistItem
{
    /**
     * @param array<int, AssembledPerformance> $performances
     */
    public function __construct(
        public string $setlistItemId,
        public int $orderNo,
        public ?string $label,
        public array $performances,
    ) {
    }
}
