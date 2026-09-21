<?php

declare(strict_types=1);

namespace Tools\Arkitect\ComponentMaps;

enum AuthComponent: string implements ComponentMap
{
    use Accessor;

    case Domain = 'Auth\Domain\*';

    case UseCase = 'Auth\Application\*\UseCase\*';
}
