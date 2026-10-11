<?php

declare(strict_types=1);

namespace Media\Application\Viewer\Query;

readonly class DecodedMediaListCursor
{
    public function __construct(
        public string $publishedAt,
        public string $mediaId,
    ) {
    }
}
