<?php

declare(strict_types=1);

namespace Tools\Arkitect\ComponentMaps;

enum AdminUserComponent: string implements ComponentMap
{
    use Accessor;

    case Domain = 'AdminUser\Domain\*';

    case UseCase = 'AdminUser\Application\*\UseCase\*';
}
