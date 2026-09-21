<?php

declare(strict_types=1);

namespace Song\Application\Admin\UseCase\Tag\Update;

readonly class UpdateInputData
{
    /**
     * @param positive-int $orderNo
     */
    public function __construct(
        public string $songTagId,
        public string $name,
        public int $orderNo,
    ) {
    }
}
