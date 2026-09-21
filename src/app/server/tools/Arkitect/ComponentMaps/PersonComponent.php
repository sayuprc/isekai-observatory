<?php

declare(strict_types=1);

namespace Tools\Arkitect\ComponentMaps;

enum PersonComponent: string implements ComponentMap
{
    use Accessor;

    case Domain = 'Person\Domain\*';

    case UseCase = 'Person\Application\*\UseCase\*';
}
