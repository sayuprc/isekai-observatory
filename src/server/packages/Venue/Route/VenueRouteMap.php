<?php

declare(strict_types=1);

namespace Venue\Route;

enum VenueRouteMap: string
{
    case Create = 'venues.create';

    case Search = 'venues.search';

    case Get = 'venues.get';

    case Update = 'venues.update';

    case Delete = 'venues.delete';
}
