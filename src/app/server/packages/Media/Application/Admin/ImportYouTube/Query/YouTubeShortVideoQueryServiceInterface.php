<?php

declare(strict_types=1);

namespace Media\Application\Admin\ImportYouTube\Query;

interface YouTubeShortVideoQueryServiceInterface
{
    /**
     * @param array<int, YouTubeUploadedVideo> $videos
     *
     * @return array<string, bool> videoId ごとの short 判定
     */
    public function detectShorts(array $videos): array;
}
