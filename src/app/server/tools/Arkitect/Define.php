<?php

declare(strict_types=1);

namespace Tools\Arkitect;

use Tools\Arkitect\ComponentMaps\ComponentMap;

class Define
{
    /**
     * @param array<ComponentMap> $dependencies
     * @param array<ComponentMap> $excludes     自分の namespace にマッチしても別のコンポーネントとして扱うもの
     */
    public function __construct(
        private readonly ComponentMap $component,
        private readonly array $dependencies = [],
        private readonly array $excludes = [],
    ) {
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
    public function dependencyNamespaces(): array
    {
        return array_map(
            static fn (ComponentMap $component): string => $component->getNamespace(),
            $this->dependencies,
        );
    }

    /**
     * @return array<string>
     */
    public function excludedNamespaces(): array
    {
        return array_map(static fn (ComponentMap $component): string => $component->getNamespace(), $this->excludes);
    }
}
