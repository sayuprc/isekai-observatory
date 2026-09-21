<?php

declare(strict_types=1);

namespace Tools\Arkitect\ComponentMaps;

enum SupportComponent: string implements ComponentMap
{
    use Accessor;

    case Domain = 'Support\Domain\*';

    case Contracts = 'Support\Contracts\*';

    case Optional = 'Support\Optional\*';

    case Collection = 'Support\Collection\*';

    case UseCase = 'Support\UseCase\*';
}
