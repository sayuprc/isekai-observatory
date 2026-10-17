<?php

declare(strict_types=1);

namespace Song\Application\Admin\Assemble;

readonly class AssembledTag
{
    public function __construct(
        public string $tagId,
        public string $name,
    ) {
    }
}
