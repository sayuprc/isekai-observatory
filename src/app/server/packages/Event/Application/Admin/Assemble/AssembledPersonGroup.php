<?php

declare(strict_types=1);

namespace Event\Application\Admin\Assemble;

readonly class AssembledPersonGroup
{
    public function __construct(
        public string $personGroupId,
        public string $name,
    ) {
    }
}
