<?php

declare(strict_types=1);

namespace App\Providers\Domain;

use Event\Application\Admin\UseCase\Search\Query\EventSearchQueryServiceInterface;
use Event\Application\Public\UseCase\List\Query\EventQueryServiceInterface as PublicEventQueryServiceInterface;
use Event\Domain\Models\EventRepositoryInterface;
use Event\Infrastructures\Admin\EventSearchQueryService;
use Event\Infrastructures\EventRepository;
use Event\Infrastructures\Public\EventQueryService as PublicEventQueryService;
use Illuminate\Support\ServiceProvider;
use Override;

class EventServiceProvider extends ServiceProvider
{
    #[Override]
    public function register(): void
    {
        $this->app->bind(EventRepositoryInterface::class, EventRepository::class);
        $this->app->bind(EventSearchQueryServiceInterface::class, EventSearchQueryService::class);
        $this->app->bind(PublicEventQueryServiceInterface::class, PublicEventQueryService::class);
    }
}
