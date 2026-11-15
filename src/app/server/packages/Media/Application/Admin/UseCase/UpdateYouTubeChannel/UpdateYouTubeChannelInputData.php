<?php

declare(strict_types=1);

namespace Media\Application\Admin\UseCase\UpdateYouTubeChannel;

readonly class UpdateYouTubeChannelInputData
{
    public function __construct(
        public string $channelId,
        public string $name,
    ) {
    }
}
