<?php

declare(strict_types=1);

namespace App\Providers\Domain;

use Illuminate\Support\ServiceProvider;
use Override;
use SiteStats\Application\Viewer\Query\SiteStatsQueryServiceInterface;
use SiteStats\Infrastructures\Viewer\SiteStatsQueryService;

class SiteStatsServiceProvider extends ServiceProvider
{
    #[Override]
    public function register(): void
    {
        $this->app->bind(SiteStatsQueryServiceInterface::class, SiteStatsQueryService::class);
    }
}
