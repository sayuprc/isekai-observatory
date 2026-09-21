<?php

declare(strict_types=1);

namespace Support\Notification;

use Support\Notification\Contracts\Action;
use Support\Notification\Contracts\Embed\NotificationEmbed;
use Support\Notification\Contracts\NotificationDriverInterface;
use Support\Notification\Contracts\NotificationMessage;
use Support\Notification\Contracts\Status;

readonly class NotificationService
{
    public function __construct(private NotificationDriverInterface $driver)
    {
    }

    /**
     * @param array<NotificationEmbed> $embeds
     */
    public function notice(Action $action, Status $status, ?string $content = null, array $embeds = []): void
    {
        $this->driver->notice(new NotificationMessage($action, $status, $content, $embeds));
    }
}
