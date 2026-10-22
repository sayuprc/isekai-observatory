<?php

declare(strict_types=1);

namespace App\Providers\Domain;

use Event\Application\Admin\Query\EventSearchQueryServiceInterface;
use Event\Application\Public\Query\EventQueryServiceInterface as PublicEventQueryServiceInterface;
use Event\Application\Viewer\Query\EventQueryServiceInterface as ViewerEventQueryServiceInterface;
use Event\Domain\Models\EventRepositoryInterface;
use Event\Infrastructures\Admin\EventSearchQueryService;
use Event\Infrastructures\EventRepository;
use Event\Infrastructures\PersonGroupUsageChecker;
use Event\Infrastructures\Public\EventQueryService as PublicEventQueryService;
use Event\Infrastructures\Viewer\EventQueryService as ViewerEventQueryService;
use Illuminate\Support\ServiceProvider;
use Override;
use Person\Domain\Services\PersonGroupUsageCheckerInterface;

class EventServiceProvider extends ServiceProvider
{
    #[Override]
    public function register(): void
    {
        $this->app->bind(EventRepositoryInterface::class, EventRepository::class);
        $this->app->bind(EventSearchQueryServiceInterface::class, EventSearchQueryService::class);
        $this->app->bind(ViewerEventQueryServiceInterface::class, ViewerEventQueryService::class);
        $this->app->bind(PublicEventQueryServiceInterface::class, PublicEventQueryService::class);
        $this->app->bind(PersonGroupUsageCheckerInterface::class, PersonGroupUsageChecker::class);
    }
}
