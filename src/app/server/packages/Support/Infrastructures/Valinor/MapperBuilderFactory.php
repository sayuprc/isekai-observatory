<?php

declare(strict_types=1);

namespace Support\Infrastructures\Valinor;

use CuyZ\Valinor\Cache\FileSystemCache;
use CuyZ\Valinor\MapperBuilder;
use Support\App\Environment;

final class MapperBuilderFactory
{
    public function create(): MapperBuilder
    {
        $builder = new MapperBuilder()
            ->configureWith(new ApplicationMapperConfigurator());

        if (! $this->isCacheEnabled()) {
            return $builder;
        }

        return $builder->withCache(new FileSystemCache(config()->string('valinor.cache_path')));
    }

    public function isCacheEnabled(): bool
    {
        return ! in_array(Environment::getEnv(), [Environment::Local, Environment::Testing], true);
    }
}
