<?php

declare(strict_types=1);

namespace Tools\Arkitect\ComponentMaps;

trait Accessor
{
    public function getName(): string
    {
        return __CLASS__ . $this->name;
    }

    public function getNamespace(): string
    {
        return $this->value;
    }
}
