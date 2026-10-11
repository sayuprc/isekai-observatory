<?php

declare(strict_types=1);

namespace Tools\Arkitect\ComponentMaps;

enum MediaComponent: string implements ComponentMap
{
    use Accessor;

    case Domain = 'Media\Domain\*';
}
