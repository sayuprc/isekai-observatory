<?php

declare(strict_types=1);

namespace App\Providers\Domain;

use Illuminate\Support\ServiceProvider;
use Media\Domain\Models\MediaRepositoryInterface;
use Media\Infrastructures\MediaRepository;
use Override;
use Song\Application\Admin\UseCase\Search\Query\SongQueryServiceInterface;
use Song\Domain\Models\SongRepositoryInterface;
use Song\Infrastructures\Admin\SongQueryService;
use Song\Infrastructures\SongRepository;

class SongServiceProvider extends ServiceProvider
{
    #[Override]
    public function register(): void
    {
        $this->app->bind(SongRepositoryInterface::class, SongRepository::class);
        $this->app->bind(MediaRepositoryInterface::class, MediaRepository::class);

        $this->app->bind(SongQueryServiceInterface::class, SongQueryService::class);
    }
}
