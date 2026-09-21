<?php

declare(strict_types=1);

namespace Tools\Arkitect\ComponentMaps;

interface ComponentMap
{
    public function getName(): string;

    public function getNamespace(): string;
}
