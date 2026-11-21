<?php

declare(strict_types=1);

namespace Public\Application\Media\UseCase\List\Query;

readonly class DecodedMediaListPageToken
{
    public function __construct(
        public string $title,
        public string $mediaId,
    ) {
    }
}
