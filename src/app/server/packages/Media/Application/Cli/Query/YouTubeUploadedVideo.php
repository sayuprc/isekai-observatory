<?php

declare(strict_types=1);

namespace Media\Application\Cli\Query;

readonly class YouTubeUploadedVideo
{
    public string $url;

    public function __construct(
        public string $videoId,
        public string $title,
        public string $publishedAt,
    ) {
        $this->url = sprintf('https://www.youtube.com/watch?v=%s', rawurlencode($videoId));
    }
}
