<?php

declare(strict_types=1);

namespace App\Providers\Domain;

use Illuminate\Support\ServiceProvider;
use Override;
use Public\Application\Event\UseCase\List\Query\EventQueryServiceInterface;
use Public\Application\Media\UseCase\List\Query\MediaQueryServiceInterface;
use Public\Application\Person\UseCase\Group\List\Query\PersonGroupQueryServiceInterface;
use Public\Application\Person\UseCase\List\Query\PersonQueryServiceInterface;
use Public\Application\Release\UseCase\List\Query\ReleaseGroupQueryServiceInterface;
use Public\Application\Song\UseCase\List\Query\SongQueryServiceInterface;
use Public\Application\Tag\UseCase\List\Query\TagQueryServiceInterface;
use Public\Application\Venue\UseCase\List\Query\VenueQueryServiceInterface;
use Public\Infrastructures\EventQueryService;
use Public\Infrastructures\MediaQueryService;
use Public\Infrastructures\PersonGroupQueryService;
use Public\Infrastructures\PersonQueryService;
use Public\Infrastructures\ReleaseGroupQueryService;
use Public\Infrastructures\SongQueryService;
use Public\Infrastructures\TagQueryService;
use Public\Infrastructures\VenueQueryService;

class PublicServiceProvider extends ServiceProvider
{
    #[Override]
    public function register(): void
    {
        $this->app->bind(EventQueryServiceInterface::class, EventQueryService::class);
        $this->app->bind(MediaQueryServiceInterface::class, MediaQueryService::class);
        $this->app->bind(PersonGroupQueryServiceInterface::class, PersonGroupQueryService::class);
        $this->app->bind(PersonQueryServiceInterface::class, PersonQueryService::class);
        $this->app->bind(ReleaseGroupQueryServiceInterface::class, ReleaseGroupQueryService::class);
        $this->app->bind(SongQueryServiceInterface::class, SongQueryService::class);
        $this->app->bind(TagQueryServiceInterface::class, TagQueryService::class);
        $this->app->bind(VenueQueryServiceInterface::class, VenueQueryService::class);
    }
}
