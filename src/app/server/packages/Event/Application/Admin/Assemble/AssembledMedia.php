<?php

declare(strict_types=1);

namespace Event\Application\Admin\Assemble;

use DateTimeImmutable;

readonly class AssembledMedia
{
    public function __construct(
        public string $mediaId,
        public string $title,
        public string $url,
        public DateTimeImmutable $publishedAt,
        public string $typeName,
        public int $typeValue,
        public bool $isDisplay,
    ) {
    }
}
