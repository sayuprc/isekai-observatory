<?php

declare(strict_types=1);

namespace Release\Application\Admin\Group\Create;

readonly class CreateInputData
{
    /**
     * @param list<array{tagId: string}> $tags
     */
    public function __construct(
        public string $title,
        public int $type,
        public string $description,
        public string $note,
        public bool $isDisplay,
        public int $orderNo,
        public array $tags,
    ) {
    }
}
