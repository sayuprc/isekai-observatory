<?php

declare(strict_types=1);

namespace Auth\Domain\Models;

enum PasskeyCeremonyType: string
{
    case Login = 'login';

    case Register = 'register';

    case Recovery = 'recovery';
}
