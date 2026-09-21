<?php

declare(strict_types=1);

namespace Release\Route;

enum ReleaseGroupRouteMap: string
{
    case Create = 'releaseGroup.create';

    case Get = 'releaseGroup.get';

    case Search = 'releaseGroup.search';

    case Update = 'releaseGroup.update';

    case Delete = 'releaseGroup.delete';
}
