<?php

declare(strict_types=1);

namespace Tag\Route;

enum TagRouteMap: string
{
    case List = 'tags';

    case Search = 'tags.search';

    case Get = 'tags.show';

    case Create = 'tags.create';

    case Update = 'tags.update';

    case Delete = 'tags.delete';
}
