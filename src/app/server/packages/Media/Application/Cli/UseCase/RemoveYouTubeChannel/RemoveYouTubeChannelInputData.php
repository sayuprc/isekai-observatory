<?php

declare(strict_types=1);

namespace Media\Application\Cli\UseCase\RemoveYouTubeChannel;

readonly class RemoveYouTubeChannelInputData
{
    public function __construct(public string $channelId)
    {
    }
}
