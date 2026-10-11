<?php

declare(strict_types=1);

namespace Event\Route;

enum ViewerEventRouteMap: string
{
    case List = 'viewer.events.list';
}
