<?php

declare(strict_types=1);

namespace Media\Application\Cli\UseCase\UpdateYouTubeChannel;

use Media\Domain\Models\YouTubeChannel\YouTubeChannel;

readonly class UpdateYouTubeChannelOutputData
{
    public function __construct(public YouTubeChannel $channel)
    {
    }
}
