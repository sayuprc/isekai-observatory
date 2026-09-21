<?php

declare(strict_types=1);

namespace Support\Notification\Contracts;

interface NotificationDriverInterface
{
    public function notice(NotificationMessage $message): void;
}
