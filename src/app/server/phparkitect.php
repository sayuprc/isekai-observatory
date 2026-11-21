<?php

declare(strict_types=1);

use Arkitect\ClassSet;
use Arkitect\CLI\Config;
use Arkitect\Expression\ForClasses\DependsOnlyOnTheseNamespaces;
use Arkitect\Expression\ForClasses\NotDependsOnTheseNamespaces;
use Arkitect\Expression\ForClasses\NotResideInTheseNamespaces;
use Arkitect\Expression\ForClasses\ResideInOneOfTheseNamespaces;
use Arkitect\Rules\Rule;
use Tools\Arkitect\Define;

// Architecture DSL はコンポーネントを namespace 1 つでしか定義できず、UseCase 配下の Query を UseCase から除外できないため、同じ意味のルールを直接組み立てる
return static function (Config $config): void {
    $classSet = ClassSet::fromDir(
        __DIR__ . '/app',
        __DIR__ . '/packages',
    );

    /** @var array<Define> $defines */
    $defines = require_once __DIR__ . '/tools/Arkitect/config.php';

    $namespaces = array_map(static fn (Define $define): string => $define->namespace(), $defines);
    $because = 'of component architecture';

    $rules = array_map(
        static function (Define $define) use ($namespaces, $because) {
            $rule = Rule::allClasses()->that(new ResideInOneOfTheseNamespaces($define->namespace()));

            if ($define->excludedNamespaces() !== []) {
                $rule = $rule->andThat(new NotResideInTheseNamespaces(...$define->excludedNamespaces()));
            }

            $expression = $define->hasDependencies()
                ? new DependsOnlyOnTheseNamespaces($define->dependencyNamespaces())
                : new NotDependsOnTheseNamespaces(array_values(array_diff($namespaces, [$define->namespace()])));

            return $rule->should($expression)->because($because);
        },
        $defines,
    );

    $config->add($classSet, ...$rules);
};
