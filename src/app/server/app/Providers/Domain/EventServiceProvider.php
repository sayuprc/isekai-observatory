<?php

declare(strict_types=1);

namespace App\Providers\Domain;

use Event\Application\Admin\Query\EventSearchQueryServiceInterface;
use Event\Domain\Models\EventRepositoryInterface;
use Event\Infrastructures\Admin\EventSearchQueryService;
use Event\Infrastructures\EventRepository;
use Illuminate\Support\ServiceProvider;
use Override;

class EventServiceProvider extends ServiceProvider
{
    #[Override]
    public function register(): void
    {
        $this->app->bind(EventRepositoryInterface::class, EventRepository::class);
        $this->app->bind(EventSearchQueryServiceInterface::class, EventSearchQueryService::class);
    }
}
