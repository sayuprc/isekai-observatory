<?php

declare(strict_types=1);

namespace Release\Application\Admin\UseCase\Group\Create;

readonly class CreateInputData
{
    public function __construct(
        public string $title,
        public int $type,
        public string $description,
        public string $note,
        public bool $isDisplay,
        public int $orderNo,
    ) {
    }
}
