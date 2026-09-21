<?php

declare(strict_types=1);

namespace Tools\Arkitect;

use Tools\Arkitect\ComponentMaps\ComponentMap;

class Define
{
    /**
     * @param array<ComponentMap> $dependencies
     */
    public function __construct(
        private readonly ComponentMap $component,
        private readonly array $dependencies = [],
    ) {
    }

    public function componentName(): string
    {
        return $this->component->getName();
    }

    public function namespace(): string
    {
        return $this->component->getNamespace();
    }

    public function hasDependencies(): bool
    {
        return 0 < count($this->dependencies);
    }

    /**
     * @return array<string>
     */
    public function dependencies(): array
    {
        return array_map(static fn (ComponentMap $component): string => $component->getName(), $this->dependencies);
    }
}
