<?php

declare(strict_types=1);

namespace Tools\Arkitect\ComponentMaps;

enum VenueComponent: string implements ComponentMap
{
    use Accessor;

    case Domain = 'Venue\Domain\*';

    case Query = 'Venue\Application\*\Query\*';

    case UseCase = 'Venue\Application\*\UseCase\*';
}
