<?php

declare(strict_types=1);

namespace Media\Application\Admin\UseCase\Search;

use Media\Domain\Models\Media;

readonly class SearchOutputData
{
    /**
     * @param list<Media> $media
     */
    public function __construct(
        public array $media,
        public int $maxPage,
    ) {
    }
}
