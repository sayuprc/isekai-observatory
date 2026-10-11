<?php

declare(strict_types=1);

namespace App\Providers\Domain;

use Illuminate\Support\ServiceProvider;
use Override;
use Release\Application\Admin\Query\ReleaseDetailQueryServiceInterface;
use Release\Application\Admin\Query\ReleaseGroupDetailQueryServiceInterface;
use Release\Application\Admin\Query\ReleaseGroupSearchQueryServiceInterface;
use Release\Application\Viewer\Query\ReleaseGroupQueryServiceInterface as ViewerReleaseGroupQueryServiceInterface;
use Release\Domain\Models\ReleaseGroupRepositoryInterface;
use Release\Domain\Models\ReleaseRepositoryInterface;
use Release\Infrastructures\Admin\ReleaseDetailQueryService;
use Release\Infrastructures\Admin\ReleaseGroupDetailQueryService;
use Release\Infrastructures\Admin\ReleaseGroupSearchQueryService;
use Release\Infrastructures\ReleaseGroupRepository;
use Release\Infrastructures\ReleaseRepository;
use Release\Infrastructures\Viewer\ReleaseGroupQueryService as ViewerReleaseGroupQueryService;

class ReleaseServiceProvider extends ServiceProvider
{
    #[Override]
    public function register(): void
    {
        $this->app->bind(ReleaseRepositoryInterface::class, ReleaseRepository::class);
        $this->app->bind(ReleaseGroupRepositoryInterface::class, ReleaseGroupRepository::class);
        $this->app->bind(ReleaseDetailQueryServiceInterface::class, ReleaseDetailQueryService::class);
        $this->app->bind(ReleaseGroupDetailQueryServiceInterface::class, ReleaseGroupDetailQueryService::class);
        $this->app->bind(ReleaseGroupSearchQueryServiceInterface::class, ReleaseGroupSearchQueryService::class);
        $this->app->bind(ViewerReleaseGroupQueryServiceInterface::class, ViewerReleaseGroupQueryService::class);
    }
}
