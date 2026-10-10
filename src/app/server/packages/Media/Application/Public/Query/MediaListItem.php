<?php

declare(strict_types=1);

namespace Media\Application\Public\Query;

use DateTimeImmutable;
use Media\Domain\Models\MediaType;

readonly class MediaListItem
{
    public function __construct(
        public string $mediaId,
        public string $title,
        public string $url,
        public DateTimeImmutable $publishedAt,
        public MediaType $type,
    ) {
    }
}
