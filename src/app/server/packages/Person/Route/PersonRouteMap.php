<?php

declare(strict_types=1);

namespace Person\Route;

enum PersonRouteMap: string
{
    case Create = 'persons.create';

    case List = 'persons.list';

    case Search = 'persons.search';

    case Get = 'persons.get';

    case Update = 'persons.update';

    case Delete = 'persons.delete';
}
