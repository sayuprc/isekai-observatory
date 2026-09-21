<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Admin\V1\Song;

use PHPUnit\Framework\Attributes\Test;
use Song\Domain\Models\SongType;
use Song\Infrastructures\SongRepository;
use Song\Route\SongRouteMap;
use Tests\Feature\Api\Admin\WithAuth;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Domain\EntityFactory;

class DeleteSongTest extends DatabaseTestCase
{
    use EntityFactory;
    use WithAuth;

    #[Test]
    public function canDelete(): void
    {
        $uuid = $this->generateUuid();

        $this->app->make(SongRepository::class)->save(
            $this->createSong($uuid, '', '', SongType::Original, true, 1, [], [], [], []),
        );

        $this->withAuth()
            ->delete(route(SongRouteMap::Delete, $uuid))
            ->assertStatus(204);
    }

    #[Test]
    public function emptyParameters(): void
    {
        $this->markTestSkipped('TODO 実装する');
    }
}
