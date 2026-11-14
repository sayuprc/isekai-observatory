<?php

declare(strict_types=1);

namespace Tests\Support\Domain;

use AdminUser\Domain\Models\AdminUser;
use AdminUser\Domain\Models\AdminUserRepositoryInterface;
use Auth\Domain\Models\Token\RefreshToken\RefreshToken;
use Auth\Domain\Models\Token\RefreshToken\RefreshTokenRepositoryInterface;
use Event\Domain\Models\Event;
use Event\Domain\Models\EventRepositoryInterface;
use Media\Domain\Models\Media;
use Media\Domain\Models\MediaRepositoryInterface;
use Media\Domain\Models\YouTubeChannel\YouTubeChannel;
use Media\Domain\Models\YouTubeChannel\YouTubeChannelRepositoryInterface;
use Person\Domain\Models\Person;
use Person\Domain\Models\PersonGroup;
use Person\Domain\Models\PersonGroupRepositoryInterface;
use Person\Domain\Models\PersonRepositoryInterface;
use Release\Domain\Models\Release;
use Release\Domain\Models\ReleaseGroup;
use Release\Domain\Models\ReleaseGroupRepositoryInterface;
use Release\Domain\Models\ReleaseRepositoryInterface;
use Song\Domain\Models\Song;
use Song\Domain\Models\SongRepositoryInterface;
use Tag\Domain\Models\Tag;
use Tag\Domain\Models\TagRepositoryInterface;
use Venue\Domain\Models\Venue;
use Venue\Domain\Models\VenueRepositoryInterface;

trait EntityStore
{
    protected function storeVenues(Venue ...$items): void
    {
        $repository = $this->makeRepository(VenueRepositoryInterface::class);
        array_map($repository->save(...), $items);
    }

    protected function storeEvents(Event ...$items): void
    {
        $repository = $this->makeRepository(EventRepositoryInterface::class);
        array_map($repository->save(...), $items);
    }

    protected function storePersons(Person ...$items): void
    {
        $repository = $this->makeRepository(PersonRepositoryInterface::class);
        array_map($repository->save(...), $items);
    }

    protected function storePersonGroups(PersonGroup ...$items): void
    {
        $repository = $this->makeRepository(PersonGroupRepositoryInterface::class);
        array_map($repository->save(...), $items);
    }

    protected function storeSongs(Song ...$items): void
    {
        $repository = $this->makeRepository(SongRepositoryInterface::class);
        array_map($repository->save(...), $items);
    }

    protected function storeMedia(Media ...$items): void
    {
        $repository = $this->makeRepository(MediaRepositoryInterface::class);
        array_map($repository->save(...), $items);
    }

    protected function storeYouTubeChannels(YouTubeChannel ...$items): void
    {
        $repository = $this->makeRepository(YouTubeChannelRepositoryInterface::class);
        array_map($repository->save(...), $items);
    }

    protected function storeTags(Tag ...$items): void
    {
        $repository = $this->makeRepository(TagRepositoryInterface::class);
        array_map($repository->save(...), $items);
    }

    protected function storeReleaseGroups(ReleaseGroup ...$items): void
    {
        $repository = $this->makeRepository(ReleaseGroupRepositoryInterface::class);
        array_map($repository->save(...), $items);
    }

    protected function storeReleases(Release ...$items): void
    {
        $repository = $this->makeRepository(ReleaseRepositoryInterface::class);
        array_map($repository->save(...), $items);
    }

    protected function storeAdminUsers(AdminUser ...$items): void
    {
        $repository = $this->makeRepository(AdminUserRepositoryInterface::class);
        array_map($repository->register(...), $items);
    }

    protected function storeRefreshTokens(RefreshToken ...$items): void
    {
        $repository = $this->makeRepository(RefreshTokenRepositoryInterface::class);
        array_map($repository->save(...), $items);
    }

    /**
     * @template T
     *
     * @param class-string<T> $class
     *
     * @return T
     */
    private function makeRepository(string $class): mixed
    {
        return $this->app->make($class);
    }
}
