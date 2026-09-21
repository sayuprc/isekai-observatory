<?php

declare(strict_types=1);

namespace Auth\Route;

enum AuthRouteMap: string
{
    case LoginStart = 'login.start';

    case LoginFinish = 'login.finish';

    case Refresh = 'refresh';

    case RegisterStart = 'register.start';

    case RegisterFinish = 'register.finish';

    case RecoveryStart = 'recovery.start';

    case RecoveryFinish = 'recovery.finish';

    case GenerateRecoveryCodes = 'recovery-codes.generate';
}
