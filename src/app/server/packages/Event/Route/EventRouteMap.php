<?php

declare(strict_types=1);

namespace Event\Route;

enum EventRouteMap: string
{
    case Create = 'events.create';

    case Search = 'events.search';

    case Get = 'events.get';

    case Update = 'events.update';

    case Delete = 'events.delete';
}
