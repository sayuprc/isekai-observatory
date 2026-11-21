<?php

declare(strict_types=1);

namespace App\Providers\Domain;

use Illuminate\Support\ServiceProvider;
use Override;
use Venue\Application\Admin\UseCase\Search\Query\VenueUsageCountQueryServiceInterface;
use Venue\Application\Public\UseCase\List\Query\VenueQueryServiceInterface as PublicVenueQueryServiceInterface;
use Venue\Domain\Models\VenueRepositoryInterface;
use Venue\Infrastructures\Admin\VenueUsageCountQueryService;
use Venue\Infrastructures\Public\VenueQueryService as PublicVenueQueryService;
use Venue\Infrastructures\VenueRepository;

class VenueServiceProvider extends ServiceProvider
{
    #[Override]
    public function register(): void
    {
        $this->app->bind(VenueUsageCountQueryServiceInterface::class, VenueUsageCountQueryService::class);
        $this->app->bind(VenueRepositoryInterface::class, VenueRepository::class);
        $this->app->bind(PublicVenueQueryServiceInterface::class, PublicVenueQueryService::class);
    }
}
