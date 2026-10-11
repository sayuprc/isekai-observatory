<?php

declare(strict_types=1);

namespace Tools\Arkitect\ComponentMaps;

enum LibraryComponent: string implements ComponentMap
{
    use Accessor;

    case ResultType = 'ResultType\*';

    case DateType = 'DateType\*';
}
