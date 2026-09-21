<?php

declare(strict_types=1);

namespace Tools\Arkitect\ComponentMaps;

enum SongComponent: string implements ComponentMap
{
    use Accessor;

    case Domain = 'Song\Domain\*';

    case Query = 'Song\Application\*\Query\*';

    case Assemble = 'Song\Application\*\Assemble\*';

    case UseCase = 'Song\Application\*\UseCase\*';
}
