<?php

declare(strict_types=1);

namespace Support\UseCase\AuditLog;

enum AuditAction: string
{
    case Create = 'create';

    case Update = 'update';

    case Delete = 'delete';

    case Register = 'register';

    case Login = 'login';

    case Refresh = 'refresh';

    case RecoveryCodeIssue = 'recovery_code_issue';

    case RecoveryCodeUse = 'recovery_code_use';
}
