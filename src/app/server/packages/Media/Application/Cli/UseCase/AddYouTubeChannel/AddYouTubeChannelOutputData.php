<?php

declare(strict_types=1);

namespace Media\Application\Cli\UseCase\AddYouTubeChannel;

use Media\Domain\Models\YouTubeChannel\YouTubeChannel;

readonly class AddYouTubeChannelOutputData
{
    public function __construct(public YouTubeChannel $channel)
    {
    }
}
