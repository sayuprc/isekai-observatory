<?php

declare(strict_types=1);

namespace Media\Application\Admin\UseCase\RemoveYouTubeChannel;

readonly class RemoveYouTubeChannelInputData
{
    public function __construct(public string $channelId)
    {
    }
}
