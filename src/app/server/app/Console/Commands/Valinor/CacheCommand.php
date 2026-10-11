<?php

declare(strict_types=1);

namespace App\Console\Commands\Valinor;

use CuyZ\Valinor\MapperBuilder;
use Illuminate\Console\Command;
use Override;
use Support\Infrastructures\Valinor\MapperBuilderFactory;

class CacheCommand extends Command
{
    #[Override]
    protected $signature = 'valinor:cache';

    #[Override]
    protected $description = 'Valinor mapper のキャッシュを生成する';

    public function handle(MapperBuilderFactory $factory, MapperBuilder $builder): int
    {
        if (! $factory->isCacheEnabled()) {
            $this->warn('この環境では Valinor のキャッシュは無効です');

            return Command::SUCCESS;
        }

        /** @var list<class-string> $signatures */
        $signatures = config()->array('valinor.warmup');

        $builder->warmupCacheFor(...$signatures);

        foreach ($signatures as $signature) {
            $this->info(sprintf('%s の mapper キャッシュを生成しました', $signature));
        }

        return Command::SUCCESS;
    }
}
