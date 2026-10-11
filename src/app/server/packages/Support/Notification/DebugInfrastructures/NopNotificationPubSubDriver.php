<?php

declare(strict_types=1);

namespace Support\Notification\DebugInfrastructures;

use Override;
use Support\Notification\Contracts\NotificationDriverInterface;
use Support\Notification\Contracts\NotificationMessage;

final readonly class NopNotificationPubSubDriver implements NotificationDriverInterface
{
    #[Override]
    public function notice(NotificationMessage $message): void
    {
        // ローカル用なので何もしない
    }
}
