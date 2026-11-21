<?php

declare(strict_types=1);

namespace App\Providers\Domain;

use Illuminate\Support\ServiceProvider;
use Override;
use Viewer\Application\Event\UseCase\List\Query\EventQueryServiceInterface;
use Viewer\Application\Media\UseCase\List\Query\MediaQueryServiceInterface;
use Viewer\Application\Release\UseCase\List\Query\ReleaseGroupQueryServiceInterface;
use Viewer\Application\Song\UseCase\List\Query\SongQueryServiceInterface;
use Viewer\Infrastructures\EventQueryService;
use Viewer\Infrastructures\MediaQueryService;
use Viewer\Infrastructures\ReleaseGroupQueryService;
use Viewer\Infrastructures\SongQueryService;

class ViewerServiceProvider extends ServiceProvider
{
    #[Override]
    public function register(): void
    {
        $this->app->bind(EventQueryServiceInterface::class, EventQueryService::class);
        $this->app->bind(MediaQueryServiceInterface::class, MediaQueryService::class);
        $this->app->bind(ReleaseGroupQueryServiceInterface::class, ReleaseGroupQueryService::class);
        $this->app->bind(SongQueryServiceInterface::class, SongQueryService::class);
    }
}
