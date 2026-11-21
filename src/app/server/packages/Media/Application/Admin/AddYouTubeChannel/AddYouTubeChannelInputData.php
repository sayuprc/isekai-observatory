<?php

declare(strict_types=1);

namespace Media\Application\Admin\AddYouTubeChannel;

readonly class AddYouTubeChannelInputData
{
    public function __construct(
        public string $channelId,
        public string $name,
    ) {
    }
}
