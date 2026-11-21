<?php

declare(strict_types=1);

namespace Media\Application\Admin\ImportYouTube;

readonly class ImportedVideo
{
    public function __construct(
        public string $title,
        public string $url,
    ) {
    }
}
