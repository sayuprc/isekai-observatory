<?php

declare(strict_types=1);

namespace Support\Route;

enum AuditLogRouteMap: string
{
    case Search = 'audit-logs.search';

    case Get = 'audit-logs.show';
}
