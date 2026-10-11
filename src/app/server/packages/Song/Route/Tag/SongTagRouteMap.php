<?php

declare(strict_types=1);

namespace Song\Route\Tag;

enum SongTagRouteMap: string
{
    case List = 'song-tags';

    case Search = 'song-tags.search';

    case Get = 'song-tags.show';

    case Create = 'song-tags.create';

    case Update = 'song-tags.update';

    case Delete = 'song-tags.delete';
}
