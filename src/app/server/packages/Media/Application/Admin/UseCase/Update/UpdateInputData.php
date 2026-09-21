<?php

declare(strict_types=1);

namespace Media\Application\Admin\UseCase\Update;

readonly class UpdateInputData
{
    public function __construct(
        public string $mediaId,
        public string $title,
        public string $url,
        public string $publishedAt,
        public int $typeValue,
        public bool $isDisplay,
    ) {
    }
}
