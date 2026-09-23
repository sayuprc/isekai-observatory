<?php

declare(strict_types=1);

namespace App\Providers\Domain;

use Event\Application\Viewer\Query\EventQueryServiceInterface as ViewerEventQueryServiceInterface;
use Event\Domain\Models\EventRepositoryInterface;
use Event\Infrastructures\EventRepository;
use Event\Infrastructures\Viewer\EventQueryService as ViewerEventQueryService;
use Illuminate\Support\ServiceProvider;
use Override;

class EventServiceProvider extends ServiceProvider
{
    #[Override]
    public function register(): void
    {
        $this->app->bind(EventRepositoryInterface::class, EventRepository::class);
        $this->app->bind(ViewerEventQueryServiceInterface::class, ViewerEventQueryService::class);
    }
}
