<?php

declare(strict_types=1);

namespace Song\Route;

enum SongRouteMap: string
{
    case Search = 'songs.search';

    case Get = 'songs.show';

    case Create = 'songs.create';

    case Update = 'songs.update';

    case Delete = 'songs.delete';
}
