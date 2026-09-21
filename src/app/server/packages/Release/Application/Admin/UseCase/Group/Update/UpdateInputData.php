<?php

declare(strict_types=1);

namespace Release\Application\Admin\UseCase\Group\Update;

readonly class UpdateInputData
{
    public function __construct(
        public string $releaseGroupId,
        public string $title,
        public int $typeValue,
        public string $description,
        public bool $isDisplay,
        public int $orderNo,
    ) {
    }
}
