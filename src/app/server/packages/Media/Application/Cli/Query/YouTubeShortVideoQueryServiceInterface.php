<?php

declare(strict_types=1);

namespace Media\Application\Cli\Query;

interface YouTubeShortVideoQueryServiceInterface
{
    /**
     * @param list<YouTubeUploadedVideo> $videos
     *
     * @return array<string, bool> videoId ごとの short 判定
     */
    public function detectShorts(array $videos): array;
}
