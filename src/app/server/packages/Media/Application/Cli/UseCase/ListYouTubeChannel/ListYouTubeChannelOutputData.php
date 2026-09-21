<?php

declare(strict_types=1);

namespace Media\Application\Cli\UseCase\ListYouTubeChannel;

use Media\Domain\Models\YouTubeChannel\YouTubeChannel;

readonly class ListYouTubeChannelOutputData
{
    /**
     * @param list<YouTubeChannel> $channels
     */
    public function __construct(public array $channels)
    {
    }
}
