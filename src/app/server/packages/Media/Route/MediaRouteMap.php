<?php

declare(strict_types=1);

namespace Media\Route;

enum MediaRouteMap: string
{
    case Create = 'media.create';

    case Get = 'media.get';

    case Search = 'media.search';

    case Update = 'media.update';

    case Delete = 'media.delete';
}
