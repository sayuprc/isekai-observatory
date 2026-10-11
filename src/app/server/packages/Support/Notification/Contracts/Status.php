<?php

declare(strict_types=1);

namespace Support\Notification\Contracts;

enum Status: string
{
    case Started = 'started';

    case Succeeded = 'succeeded';

    case Failed = 'failed';
}
