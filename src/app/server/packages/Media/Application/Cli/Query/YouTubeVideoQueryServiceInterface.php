<?php

declare(strict_types=1);

namespace Media\Application\Cli\Query;

use Media\Domain\Models\YouTubeChannel\YouTubeChannelId;

interface YouTubeVideoQueryServiceInterface
{
    /**
     * チャンネルにアップロードされた動画を新しい順に取得する
     *
     * @return iterable<int, YouTubeUploadedVideo>|null チャンネルが存在しない場合は null
     */
    public function fetchUploadedVideos(YouTubeChannelId $channelId): ?iterable;
}
