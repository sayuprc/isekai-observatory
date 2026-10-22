<?php

declare(strict_types=1);

namespace App\Providers\Domain;

use Illuminate\Support\ServiceProvider;
use Override;
use Person\Application\Admin\Query\PersonGroupQueryServiceInterface;
use Person\Application\Admin\Query\PersonGroupUsageCountQueryServiceInterface;
use Person\Application\Admin\Query\PersonUsageCountQueryServiceInterface;
use Person\Application\Public\Query\PersonGroupQueryServiceInterface as PublicPersonGroupQueryServiceInterface;
use Person\Application\Public\Query\PersonQueryServiceInterface as PublicPersonQueryServiceInterface;
use Person\Domain\Models\PersonGroupRepositoryInterface;
use Person\Domain\Models\PersonRepositoryInterface;
use Person\Infrastructures\Admin\PersonGroupQueryService;
use Person\Infrastructures\Admin\PersonGroupUsageCountQueryService;
use Person\Infrastructures\Admin\PersonUsageCountQueryService;
use Person\Infrastructures\PersonGroupRepository;
use Person\Infrastructures\PersonRepository;
use Person\Infrastructures\Public\PersonGroupQueryService as PublicPersonGroupQueryService;
use Person\Infrastructures\Public\PersonQueryService as PublicPersonQueryService;

class PersonServiceProvider extends ServiceProvider
{
    #[Override]
    public function register(): void
    {
        $this->app->bind(PersonUsageCountQueryServiceInterface::class, PersonUsageCountQueryService::class);
        $this->app->bind(PersonGroupUsageCountQueryServiceInterface::class, PersonGroupUsageCountQueryService::class);
        $this->app->bind(PersonRepositoryInterface::class, PersonRepository::class);
        $this->app->bind(PublicPersonQueryServiceInterface::class, PublicPersonQueryService::class);
        $this->app->bind(PublicPersonGroupQueryServiceInterface::class, PublicPersonGroupQueryService::class);
        $this->app->bind(PersonGroupRepositoryInterface::class, PersonGroupRepository::class);
        $this->app->bind(PersonGroupQueryServiceInterface::class, PersonGroupQueryService::class);
    }
}
