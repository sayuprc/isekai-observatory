<?php

declare(strict_types=1);

use Arkitect\ClassSet;
use Arkitect\CLI\Config;
use Tools\Arkitect\PackageRules;

return static function (Config $config): void {
    $classSet = ClassSet::fromDir(
        __DIR__ . '/app',
        __DIR__ . '/packages',
    );

    /** @var PackageRules $packageRules */
    $packageRules = require __DIR__ . '/tools/Arkitect/config.php';

    $config->add($classSet, ...$packageRules->rules());
};
