<?php

declare(strict_types=1);

namespace Support\Notification\Infrastructures;

final readonly class NotificationConfig
{
    public function __construct(
        public string $project,
        public string $topic,
    ) {
    }
}
