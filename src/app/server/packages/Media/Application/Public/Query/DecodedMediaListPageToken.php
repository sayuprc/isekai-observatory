<?php

declare(strict_types=1);

namespace Media\Application\Public\Query;

readonly class DecodedMediaListPageToken
{
    public function __construct(
        public string $title,
        public string $mediaId,
    ) {
    }
}
