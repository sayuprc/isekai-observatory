<?php

declare(strict_types=1);

namespace Viewer\Application\Media\UseCase\List\Query;

readonly class DecodedMediaListCursor
{
    public function __construct(
        public string $publishedAt,
        public string $mediaId,
    ) {
    }
}
