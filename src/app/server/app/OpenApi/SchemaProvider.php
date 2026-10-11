<?php

declare(strict_types=1);

namespace App\OpenApi;

use cebe\openapi\spec\OpenApi;
use Psr\Cache\CacheItemPoolInterface;
use Support\App\Environment;
use Symfony\Component\Cache\Adapter\ApcuAdapter;
use Symfony\Component\Cache\Adapter\ChainAdapter;
use Symfony\Component\Cache\Adapter\PhpFilesAdapter;

class SchemaProvider
{
    public function provide(string $yamlPath): OpenApi
    {
        $builder = new CachedValidatorBuilder()->fromYamlFile($yamlPath);

        if ($this->isCacheEnabled()) {
            $builder->setCache($this->createCache());
        }

        return $builder->getSchema();
    }

    public function isCacheEnabled(): bool
    {
        return ! in_array(Environment::getEnv(), [Environment::Local, Environment::Testing], true);
    }

    private function createCache(): CacheItemPoolInterface
    {
        $adapters = [];

        // CLI (ウォームアップコマンド実行時など) では APCu が使えないため Filesystem のみに書く
        if (ApcuAdapter::isSupported()) {
            $adapters[] = new ApcuAdapter();
        }

        $adapters[] = new PhpFilesAdapter(directory: base_path('bootstrap/cache/openapi'));

        return new ChainAdapter($adapters);
    }
}
