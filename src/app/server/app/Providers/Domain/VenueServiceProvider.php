<?php

declare(strict_types=1);

namespace App\Providers\Domain;

use Illuminate\Support\ServiceProvider;
use Override;
use Venue\Domain\Models\VenueRepositoryInterface;
use Venue\Infrastructures\VenueRepository;

class VenueServiceProvider extends ServiceProvider
{
    #[Override]
    public function register(): void
    {
        $this->app->bind(VenueRepositoryInterface::class, VenueRepository::class);
    }
}
