<?php

declare(strict_types=1);

namespace Media\Application\Admin\UseCase\Update;

readonly class UpdateInputData
{
    /**
     * @param list<array{tagId: string}> $tags
     */
    public function __construct(
        public string $mediaId,
        public string $title,
        public string $url,
        public string $publishedAt,
        public int $type,
        public bool $isDisplay,
        public array $tags = [],
    ) {
    }
}
