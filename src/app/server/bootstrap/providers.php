<?php

declare(strict_types=1);

return [
    App\Providers\AppServiceProvider::class,
    App\Providers\DatabaseServiceProvider::class,
    App\Providers\Domain\AuthServiceProvider::class,
    App\Providers\Domain\MediaServiceProvider::class,
    App\Providers\Domain\PersonServiceProvider::class,
    App\Providers\Domain\VenueServiceProvider::class,
    App\Providers\Domain\EventServiceProvider::class,
    App\Providers\Domain\ReleaseServiceProvider::class,
    App\Providers\Domain\SiteStatsServiceProvider::class,
    App\Providers\Domain\SongServiceProvider::class,
    App\Providers\Domain\SupportServiceProvider::class,
    App\Providers\Domain\AdminUserServiceProvider::class,
];
