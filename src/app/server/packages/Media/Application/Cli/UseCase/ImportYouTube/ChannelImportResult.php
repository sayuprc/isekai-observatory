<?php

declare(strict_types=1);

namespace Media\Application\Cli\UseCase\ImportYouTube;

use Media\Domain\Models\YouTubeChannel\YouTubeChannel;

readonly class ChannelImportResult
{
    /**
     * @param list<ImportedVideo> $importedVideos
     */
    private function __construct(
        public YouTubeChannel $channel,
        public int $importedCount,
        public bool $channelFound,
        public array $importedVideos,
    ) {
    }

    /**
     * @param list<ImportedVideo> $importedVideos
     */
    public static function imported(YouTubeChannel $channel, array $importedVideos): self
    {
        return new self($channel, count($importedVideos), true, $importedVideos);
    }

    public static function channelNotFound(YouTubeChannel $channel): self
    {
        return new self($channel, 0, false, []);
    }
}
