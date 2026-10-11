<?php

declare(strict_types=1);

namespace Person\Application\Admin\UseCase\Update;

readonly class UpdateInputData
{
    public function __construct(
        public string $personId,
        public string $name,
        public int $orderNo,
    ) {
    }
}
