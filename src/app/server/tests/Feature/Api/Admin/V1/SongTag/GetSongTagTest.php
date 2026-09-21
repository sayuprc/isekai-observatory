<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Admin\V1\SongTag;

use PHPUnit\Framework\Attributes\Test;
use Song\Infrastructures\Tag\SongTagRepository;
use Song\Route\Tag\SongTagRouteMap;
use Tests\Feature\Api\Admin\WithAuth;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Domain\EntityFactory;

class GetSongTagTest extends DatabaseTestCase
{
    use EntityFactory;
    use WithAuth;

    #[Test]
    public function found(): void
    {
        $uuid = $this->generateUuid();

        $this->app->make(SongTagRepository::class)->save($this->createSongTag($uuid, 'テストタグA', 1));

        $this->withAuth()
            ->get(route(SongTagRouteMap::Get, $uuid))
            ->assertStatus(200)
            ->assertExactJson([
                'tag' => [
                    'songTagId' => $uuid,
                    'name' => 'テストタグA',
                    'orderNo' => 1,
                ],
            ]);
    }

    #[Test]
    public function notFound(): void
    {
        $uuid = $this->generateUuid();

        $this->withAuth()
            ->get(route(SongTagRouteMap::Get, $uuid))
            ->assertStatus(404);
    }
}
