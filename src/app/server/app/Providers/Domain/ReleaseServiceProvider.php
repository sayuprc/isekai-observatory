<?php

declare(strict_types=1);

namespace App\Providers\Domain;

use Illuminate\Support\ServiceProvider;
use Override;
use Release\Application\Admin\Get\Query\ReleaseDetailQueryServiceInterface;
use Release\Application\Admin\Group\Get\Query\ReleaseGroupDetailQueryServiceInterface;
use Release\Application\Admin\Group\Search\Query\ReleaseGroupSearchQueryServiceInterface;
use Release\Application\Admin\Search\Query\ReleaseSearchQueryServiceInterface;
use Release\Application\Public\List\Query\ReleaseGroupQueryServiceInterface as PublicReleaseGroupQueryServiceInterface;
use Release\Application\Viewer\List\Query\ReleaseGroupQueryServiceInterface as ViewerReleaseGroupQueryServiceInterface;
use Release\Domain\Models\ReleaseGroupRepositoryInterface;
use Release\Domain\Models\ReleaseRepositoryInterface;
use Release\Infrastructures\Admin\ReleaseDetailQueryService;
use Release\Infrastructures\Admin\ReleaseGroupDetailQueryService;
use Release\Infrastructures\Admin\ReleaseGroupSearchQueryService;
use Release\Infrastructures\Admin\ReleaseSearchQueryService;
use Release\Infrastructures\Public\ReleaseGroupQueryService as PublicReleaseGroupQueryService;
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
        $this->app->bind(ReleaseSearchQueryServiceInterface::class, ReleaseSearchQueryService::class);
        $this->app->bind(ViewerReleaseGroupQueryServiceInterface::class, ViewerReleaseGroupQueryService::class);
        $this->app->bind(PublicReleaseGroupQueryServiceInterface::class, PublicReleaseGroupQueryService::class);
    }
}
