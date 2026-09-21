<?php

declare(strict_types=1);

use Arkitect\ClassSet;
use Arkitect\CLI\Config;
use Arkitect\RuleBuilders\Architecture\Architecture;
use Arkitect\RuleBuilders\Architecture\Component;
use Tools\Arkitect\Define;

return static function (Config $config): void {
    $classSet = ClassSet::fromDir(
        __DIR__ . '/app',
        __DIR__ . '/packages',
    );

    $components = array_reduce(
        require_once __DIR__ . '/tools/Arkitect/config.php',
        static function (Component $component, Define $define): Component {
            $name = $define->componentName();

            $component = $component->component($name)
                ->definedBy($define->namespace())
                ->where($name);

            return $define->hasDependencies()
                ? $component->shouldOnlyDependOnComponents(...$define->dependencies())
                : $component->shouldNotDependOnAnyComponent();
        },
        Architecture::withComponents(),
    );

    $config->add($classSet, ...$components->rules());
};
