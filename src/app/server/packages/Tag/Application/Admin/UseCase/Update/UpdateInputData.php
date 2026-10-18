<?php

declare(strict_types=1);

namespace Tag\Application\Admin\UseCase\Update;

readonly class UpdateInputData
{
    /**
     * @param positive-int $orderNo
     */
    public function __construct(
        public string $tagId,
        public string $name,
        public int $orderNo,
    ) {
    }
}
