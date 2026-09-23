<?php

declare(strict_types=1);

namespace Event\Application\Admin\Assemble;

readonly class AssembledCoVocalist
{
    public function __construct(
        public string $personId,
        public string $name,
        public ?string $creditName,
        public int $orderNo,
    ) {
    }
}
