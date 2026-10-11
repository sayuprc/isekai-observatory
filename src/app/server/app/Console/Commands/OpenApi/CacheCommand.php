<?php

declare(strict_types=1);

namespace App\Console\Commands\OpenApi;

use App\OpenApi\SchemaProvider;
use Illuminate\Console\Command;
use Override;

class CacheCommand extends Command
{
    #[Override]
    protected $signature = 'openapi:cache';

    #[Override]
    protected $description = 'OpenAPI schema のキャッシュを生成する';

    public function handle(SchemaProvider $schemaProvider): int
    {
        if (! $schemaProvider->isCacheEnabled()) {
            $this->warn('この環境では OpenAPI schema のキャッシュは無効です');

            return Command::SUCCESS;
        }

        /** @var array<string, string> */
        $paths = config()->array('openapi.path');

        foreach ($paths as $name => $path) {
            $schemaProvider->provide($path);

            $this->info(sprintf('%s の schema キャッシュを生成しました', $name));
        }

        return Command::SUCCESS;
    }
}
