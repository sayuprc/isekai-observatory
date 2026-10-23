<?php

declare(strict_types=1);

namespace Tools\Arkitect\ComponentMaps;

enum TagComponent: string implements ComponentMap
{
    use Accessor;

    case Domain = 'Tag\Domain\*';

    case Query = 'Tag\Application\*\Query\*';

    case UseCase = 'Tag\Application\*\UseCase\*';
}
