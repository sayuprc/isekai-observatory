<?php

declare(strict_types=1);

namespace Event\Application\Admin\Assemble;

readonly class AssembledSource
{
    public function __construct(
        public string $displayName,
        public string $url,
        public int $orderNo,
    ) {
    }
}
