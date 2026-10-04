<?php

declare(strict_types=1);

namespace Person\Route;

enum PersonGroupRouteMap: string
{
    case Create = 'person-groups.create';

    case Search = 'person-groups.search';

    case Get = 'person-groups.get';

    case Update = 'person-groups.update';

    case Delete = 'person-groups.delete';
}
